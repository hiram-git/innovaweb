<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CobroController extends Controller
{
    public function __construct(private readonly CadenaControlService $cadena) {}

    public function index(Request $request): JsonResponse
    {
        $q       = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 25), 100);
        $params  = ['limit' => $perPage, 'tt' => 'FAC'];
        $where   = ["m.INTEGRADO = 0", "m.TIPOFACTURA = 'CREDITO'", "m.MONTOSAL > 0"];

        if ($q) {
            $where[] = "(m.NUMREF LIKE :q OR m.CODIGO LIKE :q2 OR m.NOMBRE LIKE :q3)";
            $params['q'] = "%{$q}%"; $params['q2'] = "%{$q}%"; $params['q3'] = "%{$q}%";
        }

        $cobros = DB::select(
            "SELECT TOP (:limit)
                m.CONTROL, m.NUMREF, m.CODIGO, m.NOMBRE, m.FECEMIS,
                m.MONTOTOT, m.MONTOSAL, m.FECVENCS, m.DIASVEN
             FROM TRANSACCMAESTRO m
             WHERE " . implode(' AND ', $where) . "
             ORDER BY m.FECVENCS ASC", $params
        );
        return response()->json(['data' => $cobros]);
    }

    public function show(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $factura = DB::selectOne("SELECT * FROM TRANSACCMAESTRO WHERE CONTROL = ? AND INTEGRADO = 0", [$control]);
        if (! $factura) return response()->json(['message' => 'Factura no encontrada.'], 404);

        $pagosRealizados = DB::select(
            "SELECT p.*, b.NOMBRE AS DESCRIP_PAGO FROM TRANSACCMAESTRO p
             LEFT JOIN BASEINSTRUMENTOS b ON b.CODTAR = p.CODTAR
             WHERE p.NUMREF = ? AND p.TIPTRAN = 'PAGxFAC' AND p.INTEGRADO = 0",
            [$factura->NUMREF]
        );
        return response()->json(['data' => ['factura' => $factura, 'cobros' => $pagosRealizados]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'control_factura' => ['required', 'string'],
            'formas_pago'     => ['required', 'array', 'min:1'],
            'formas_pago.*.codtar' => ['required', 'string'],
            'formas_pago.*.monto'  => ['required', 'numeric', 'min:0.01'],
        ]);

        $controlFac = base64_decode($data['control_factura']);
        $factura = DB::selectOne(
            "SELECT CONTROL, NUMREF, MONTOTOT, MONTOSAL, CODIGO FROM TRANSACCMAESTRO WHERE CONTROL = ? AND INTEGRADO = 0",
            [$controlFac]
        );
        if (! $factura) return response()->json(['message' => 'Factura no encontrada.'], 404);
        if ((float) $factura->MONTOSAL <= 0) return response()->json(['message' => 'Esta factura ya está saldada.'], 422);

        $totalPagado = array_sum(array_column($data['formas_pago'], 'monto'));
        if ($totalPagado > (float) $factura->MONTOSAL + 0.01) {
            return response()->json(['message' => 'El monto del cobro supera el saldo pendiente.'], 422);
        }

        DB::beginTransaction();
        try {
            $empresa = DB::selectOne("SELECT NROINIREC FROM BASEEMPRESA WHERE CONTROL = 1");
            $nroPago = str_pad((string) ((int) $empresa->NROINIREC), 10, '0', STR_PAD_LEFT);
            [$dias,$hora,$ale] = $this->cadena->componentes();
            $controlCobro = "{$dias}{$hora}{$ale}RC";

            // Acumular montos por instrumento (como hace el legacy)
            $montoTar = $montoChe = $montoInts1 = $montoEfe = 0;
            foreach ($data['formas_pago'] as $fp) {
                $inst = DB::selectOne("SELECT FUNCION FROM BASEINSTRUMENTOS WHERE CODTAR = ?", [$fp['codtar']]);
                match ((int) ($inst?->FUNCION ?? 99)) {
                    0 => $montoTar   += $fp['monto'],
                    1 => $montoChe   += $fp['monto'],
                    2, 3 => $montoInts1 += $fp['monto'],
                    6 => $montoEfe   += $fp['monto'],
                    default => null,
                };
            }

            DB::statement(
                "INSERT INTO TRANSACCMAESTRO
                    (CONTROL,TIPREG,TIPTRAN,NUMDOC,CODIGO,NOMBRE,FECEMIS,NUMREF,
                     MONTOTOT,MONTOTAR,MONTOCHE,MONTOINTS1,MONTOEFE,INTEGRADO)
                 VALUES (:ctrl,'1','PAGxFAC',:numref,:cod,:nom,GETDATE(),:nroPago,
                         :total,:tar,:che,:ints1,:efe,0)",
                [
                    'ctrl' => $controlCobro, 'numref' => $factura->NUMREF,
                    'cod' => $factura->CODIGO, 'nom' => '',
                    'nroPago' => $nroPago, 'total' => round($totalPagado, 2),
                    'tar' => round($montoTar,2), 'che' => round($montoChe,2),
                    'ints1' => round($montoInts1,2), 'efe' => round($montoEfe,2),
                ]
            );

            foreach ($data['formas_pago'] as $fp) {
                [$d2,$h2,$a2] = $this->cadena->componentes();
                DB::statement(
                    "INSERT INTO TRANSACCPAGOS (FECHORA,CONTROL,CODTAR,MONTOPAG,INTEGRADO)
                     VALUES (:fh,:ctrl,:cod,:monto,0)",
                    ['fh' => "{$d2}{$h2}{$a2}RP", 'ctrl' => $controlCobro,
                     'cod' => $fp['codtar'], 'monto' => round((float)$fp['monto'],2)]
                );
            }

            // Actualizar saldo de la factura
            $nuevoSaldo = max(0, (float) $factura->MONTOSAL - $totalPagado);
            DB::statement(
                "UPDATE TRANSACCMAESTRO SET MONTOSAL = :sal WHERE CONTROL = :ctrl",
                ['sal' => round($nuevoSaldo, 2), 'ctrl' => $controlFac]
            );

            DB::statement("UPDATE BASEEMPRESA SET NROINIREC = NROINIREC + 1 WHERE CONTROL = 1");

            DB::commit();
            return response()->json([
                'message'      => 'Cobro registrado exitosamente.',
                'control_cobro'=> base64_encode($controlCobro),
                'saldo_restante'=> round($nuevoSaldo, 2),
            ], 201);
        } catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Los cobros no se pueden modificar. Anule y vuelva a registrar.'], 422);
    }

    public function destroy(string $id): JsonResponse
    {
        $control = base64_decode($id);
        DB::beginTransaction();
        try {
            $cobro = DB::selectOne("SELECT NUMREF, MONTOTOT FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PAGxFAC'", [$control]);
            if (! $cobro) return response()->json(['message' => 'Cobro no encontrado.'], 404);

            // Restaurar saldo de la factura original
            DB::statement(
                "UPDATE TRANSACCMAESTRO SET MONTOSAL = MONTOSAL + :monto WHERE NUMREF = :ref AND TIPTRAN = 'FAC'",
                ['monto' => $cobro->MONTOTOT, 'ref' => $cobro->NUMREF]
            );
            DB::statement("UPDATE TRANSACCMAESTRO SET INTEGRADO = 1 WHERE CONTROL = ?", [$control]);
            DB::commit();
            return response()->json(['message' => 'Cobro anulado y saldo restaurado.']);
        } catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }
}
