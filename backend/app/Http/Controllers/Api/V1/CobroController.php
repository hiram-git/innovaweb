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
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * $perPage;
        $params  = [];
        $where   = ["m.INTEGRADO = 0", "m.TIPOFACTURA = 'CREDITO'", "m.MONTOSAL > 0"];

        if ($q) {
            $where[]      = "(m.NUMREF LIKE :q OR m.CODIGO LIKE :q2 OR m.NOMBRE LIKE :q3)";
            $params['q']  = "%{$q}%";
            $params['q2'] = "%{$q}%";
            $params['q3'] = "%{$q}%";
        }

        $whereStr = implode(' AND ', $where);
        $total    = (int) (DB::selectOne("SELECT COUNT(*) AS total FROM TRANSACCMAESTRO m WHERE {$whereStr}", $params)->total ?? 0);

        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $cobros = DB::select(
            "SELECT m.CONTROL AS CONTROLMAESTRO, m.NUMREF AS NROFAC,
                m.CODIGO AS CODCLIENTE, m.NOMBRE AS NOMCLIENTE,
                m.FECEMIS AS FECHA, m.MONTOTOT, m.MONTOSAL, m.FECVENCS, m.DIASVEN
             FROM TRANSACCMAESTRO m
             WHERE {$whereStr}
             ORDER BY m.FECVENCS ASC
             OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY", $params
        );

        return response()->json([
            'data' => $cobros,
            'meta' => ['total' => $total, 'per_page' => $perPage, 'current_page' => $page,
                       'last_page' => (int) ceil($total / max(1, $perPage))],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $factura = DB::selectOne(
            "SELECT CONTROL AS CONTROLMAESTRO, NUMREF AS NROFAC, NOMBRE AS NOMCLIENTE,
                MONTOTOT, MONTOSAL FROM TRANSACCMAESTRO WHERE CONTROL = ? AND INTEGRADO = 0",
            [$control]
        );
        if (! $factura) return response()->json(['message' => 'Factura no encontrada.'], 404);

        $cobros = DB::select(
            "SELECT p.CONTROL AS CONTROLCOBRO, p.FECEMIS AS FECHA, p.MONTOTOT,
                b.DESCRINSTRUMENTO
             FROM TRANSACCMAESTRO p
             LEFT JOIN BASEINSTRUMENTOS b ON b.CODINSTRUMENTO = p.CODTAR
             WHERE p.NUMREF = ? AND p.TIPTRAN = 'PAGxFAC' AND p.INTEGRADO = 0",
            [$factura->NROFAC]
        );
        return response()->json(['data' => ['factura' => $factura, 'cobros' => $cobros]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            // Acepta controlmaestro en base64 o texto plano
            'controlmaestro' => ['required', 'string'],
            'instrumento'    => ['required', 'string', 'max:20'],
            'monto'          => ['required', 'numeric', 'min:0.01'],
            'referencia'     => ['nullable', 'string', 'max:100'],
        ]);

        // Intentar decodificar como base64; si falla, usar como plain
        $decoded = base64_decode($data['controlmaestro'], strict: true);
        $controlFac = ($decoded !== false && strlen($decoded) > 5) ? $decoded : $data['controlmaestro'];

        $factura = DB::selectOne(
            "SELECT CONTROL, NUMREF, MONTOTOT, MONTOSAL, CODIGO FROM TRANSACCMAESTRO
             WHERE CONTROL = ? AND INTEGRADO = 0 AND MONTOSAL > 0",
            [$controlFac]
        );
        if (! $factura) return response()->json(['message' => 'Factura no encontrada o ya saldada.'], 404);
        if ((float) $data['monto'] > (float) $factura->MONTOSAL + 0.01) {
            return response()->json(['message' => 'El monto supera el saldo pendiente de $' . number_format($factura->MONTOSAL, 2) . '.'], 422);
        }

        $instrumento = DB::selectOne(
            "SELECT CODINSTRUMENTO, FUNCION FROM BASEINSTRUMENTOS WHERE CODINSTRUMENTO = ?",
            [$data['instrumento']]
        );
        if (! $instrumento) {
            return response()->json(['message' => 'Instrumento de pago inválido.'], 422);
        }

        DB::beginTransaction();
        try {
            $empresa = DB::selectOne("SELECT NROINIREC FROM BASEEMPRESA WHERE CONTROL = 1");
            $nroPago = str_pad((string) ((int) ($empresa->NROINIREC ?? 1)), 10, '0', STR_PAD_LEFT);
            [$dias, $hora, $ale] = $this->cadena->componentes();
            $controlCobro = "{$dias}{$hora}{$ale}RC";
            $monto = round((float) $data['monto'], 2);

            [$montoTar, $montoChe, $montoInts1, $montoEfe] = [0, 0, 0, 0];
            match ((int) $instrumento->FUNCION) {
                0 => $montoTar   = $monto,
                1 => $montoChe   = $monto,
                2, 3 => $montoInts1 = $monto,
                6 => $montoEfe   = $monto,
                default => $montoEfe = $monto,
            };

            DB::statement(
                "INSERT INTO TRANSACCMAESTRO
                    (CONTROL,TIPREG,TIPTRAN,NUMDOC,CODIGO,NOMBRE,FECEMIS,NUMREF,
                     MONTOTOT,MONTOTAR,MONTOCHE,MONTOINTS1,MONTOEFE,INTEGRADO)
                 VALUES (:ctrl,'1','PAGxFAC',:numref,:cod,'',GETDATE(),:nroPago,
                         :total,:tar,:che,:ints1,:efe,0)",
                [
                    'ctrl' => $controlCobro, 'numref' => $factura->NUMREF,
                    'cod' => $factura->CODIGO, 'nroPago' => $nroPago,
                    'total' => $monto, 'tar' => $montoTar, 'che' => $montoChe,
                    'ints1' => $montoInts1, 'efe' => $montoEfe,
                ]
            );

            [$d2, $h2, $a2] = $this->cadena->componentes();
            DB::statement(
                "INSERT INTO TRANSACCPAGOS (FECHORA,CONTROL,CODTAR,MONTOPAG,REFERENCIA,INTEGRADO)
                 VALUES (:fh,:ctrl,:cod,:monto,:ref,0)",
                [
                    'fh' => "{$d2}{$h2}{$a2}RP", 'ctrl' => $controlCobro,
                    'cod' => $data['instrumento'], 'monto' => $monto,
                    'ref' => $data['referencia'] ?? '',
                ]
            );

            $nuevoSaldo = max(0.0, round((float) $factura->MONTOSAL - $monto, 2));
            DB::statement(
                "UPDATE TRANSACCMAESTRO SET MONTOSAL = :sal WHERE CONTROL = :ctrl",
                ['sal' => $nuevoSaldo, 'ctrl' => $controlFac]
            );
            DB::statement("UPDATE BASEEMPRESA SET NROINIREC = NROINIREC + 1 WHERE CONTROL = 1");

            DB::commit();
            return response()->json([
                'message'       => 'Cobro registrado exitosamente.',
                'controlCobro'  => base64_encode($controlCobro),
                'saldoRestante' => $nuevoSaldo,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
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
            $cobro = DB::selectOne(
                "SELECT NUMREF, MONTOTOT FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PAGxFAC'",
                [$control]
            );
            if (! $cobro) return response()->json(['message' => 'Cobro no encontrado.'], 404);

            DB::statement(
                "UPDATE TRANSACCMAESTRO SET MONTOSAL = MONTOSAL + :monto WHERE NUMREF = :ref AND TIPTRAN = 'FAC'",
                ['monto' => $cobro->MONTOTOT, 'ref' => $cobro->NUMREF]
            );
            DB::statement("UPDATE TRANSACCMAESTRO SET INTEGRADO = 1 WHERE CONTROL = ?", [$control]);
            DB::commit();
            return response()->json(['message' => 'Cobro anulado y saldo restaurado.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
