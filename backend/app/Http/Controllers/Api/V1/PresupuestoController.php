<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    public function __construct(private readonly CadenaControlService $cadena) {}

    public function index(Request $request): JsonResponse
    {
        $q       = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 25), 100);
        $params  = ['limit' => $perPage, 'tiptran' => 'PRE'];
        $where   = ["m.TIPTRAN = :tiptran"];

        if ($q) {
            $where[] = "(m.NUMREF LIKE :q OR m.CODIGO LIKE :q2 OR m.NOMBRE LIKE :q3)";
            $params['q'] = "%{$q}%"; $params['q2'] = "%{$q}%"; $params['q3'] = "%{$q}%";
        }

        $presupuestos = DB::select(
            "SELECT TOP (:limit)
                m.CONTROL, m.NUMREF, m.CODIGO, m.NOMBRE,
                m.FECEMIS, m.MONTOBRU, m.MONTOIMP, m.MONTODES, m.MONTOTOT
             FROM TRANSACCMAESTRO m
             WHERE " . implode(' AND ', $where) . "
             ORDER BY m.FECEMIS DESC", $params
        );
        return response()->json(['data' => $presupuestos]);
    }

    public function show(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $maestro = DB::selectOne(
            "SELECT * FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PRE'", [$control]
        );
        if (! $maestro) return response()->json(['message' => 'Presupuesto no encontrado.'], 404);

        $detalles = DB::select(
            "SELECT * FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0 ORDER BY FECHORA", [$control]
        );
        return response()->json(['data' => ['maestro' => $maestro, 'detalles' => $detalles]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codcliente'       => ['required', 'string', 'max:20'],
            'descuentoGlobal'  => ['nullable', 'numeric', 'min:0'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.codpro'   => ['required', 'string', 'max:20'],
            'items.*.descrip'  => ['required', 'string', 'max:150'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio'   => ['required', 'numeric', 'min:0'],
            'items.*.descuento'=> ['nullable', 'numeric', 'min:0'],
            'items.*.imppor'   => ['required', 'integer', 'in:0,7,10,15'],
            'observacion'      => ['nullable', 'string', 'max:500'],
        ]);

        $codCliente = strtoupper(trim($data['codcliente']));
        $cliente    = DB::selectOne(
            "SELECT NOMBRE, TIPOCLI FROM BASECLIENTESPROVEEDORES WHERE CODIGO = ? AND TIPREG = '1'",
            [$codCliente]
        );
        if (! $cliente) {
            return response()->json(['message' => "Cliente '{$codCliente}' no encontrado en el ERP."], 422);
        }

        DB::beginTransaction();
        try {
            $empresa = DB::selectOne("SELECT NROINIPRE FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref  = str_pad((string) ((int) ($empresa->NROINIPRE ?? 1)), 10, '0', STR_PAD_LEFT);
            [$dias,$hora,$ale] = $this->cadena->componentes();
            $control = "{$dias}{$hora}{$ale}PR";

            $montoBru = 0; $montoImp = 0;
            $montoDes = (float) ($data['descuentoGlobal'] ?? 0);
            $itemsCalc = [];

            foreach ($data['items'] as $item) {
                $sub  = (float) $item['cantidad'] * ((float) $item['precio'] - (float) ($item['descuento'] ?? 0));
                $itb  = round($sub * (int) $item['imppor'] / 100, 2);
                $montoBru += $sub; $montoImp += $itb;
                $itemsCalc[] = array_merge($item, ['_sub' => $sub, '_itb' => $itb]);
            }

            $montoTot = $montoBru + $montoImp - $montoDes;

            DB::statement(
                "INSERT INTO TRANSACCMAESTRO
                    (CONTROL,TIPREG,TIPTRAN,CODIGO,NOMBRE,FECEMIS,NUMREF,
                     MONTOBRU,MONTOIMP,MONTODES,MONTOTOT,TIPOCLI,CODVEN)
                 VALUES (:ctrl,'1','PRE',:cod,:nom,GETDATE(),:numref,
                         :bru,:imp,:des,:tot,:tcli,:cven)",
                [
                    'ctrl' => $control, 'cod' => $codCliente,
                    'nom' => $cliente->NOMBRE, 'numref' => $numref,
                    'bru' => round($montoBru,2), 'imp' => round($montoImp,2),
                    'des' => round($montoDes,2), 'tot' => round($montoTot,2),
                    'tcli' => $cliente->TIPOCLI ?? '01', 'cven' => $request->user()->erp_coduser ?? '',
                ]
            );

            foreach ($itemsCalc as $item) {
                [$d2,$h2,$a2] = $this->cadena->componentes();
                DB::statement(
                    "INSERT INTO TRANSACCDETALLES
                        (FECHORA,CONTROL,CODPRO,DESCRIP1,CANTIDAD,PRECOSUNI,
                         MONTODESCUENTO,COSTOADU1,IMPPOR,MONTOIMP,COMPONENTE,INTEGRADO)
                     VALUES (:fh,:ctrl,:cod,:des,:cant,:prec,:dsc,:tot,:imp,:itb,0,0)",
                    [
                        'fh' => "{$d2}{$h2}{$a2}PD", 'ctrl' => $control,
                        'cod' => strtoupper($item['codpro']), 'des' => $item['descrip'],
                        'cant' => $item['cantidad'], 'prec' => $item['precio'],
                        'dsc' => $item['descuento'] ?? 0,
                        'tot' => round($item['_sub'] + $item['_itb'], 2),
                        'imp' => $item['imppor'], 'itb' => round($item['_itb'], 2),
                    ]
                );
            }

            if (!empty($data['observacion'])) {
                DB::statement("INSERT INTO TRANSACCOBSERVACIONES (CONTROL,OBS1) VALUES (:c,:o)", ['c' => $control, 'o' => $data['observacion']]);
            }

            DB::statement("UPDATE BASEEMPRESA SET NROINIPRE = NROINIPRE + 1 WHERE CONTROL = 1");
            DB::commit();

            return response()->json([
                'message' => 'Presupuesto creado.',
                'control' => base64_encode($control),
                'numref'  => $numref,
            ], 201);
        } catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $control = base64_decode($id);
        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:500'],
        ]);
        if (isset($data['observacion'])) {
            DB::statement(
                "MERGE INTO TRANSACCOBSERVACIONES AS t USING (SELECT :c AS CONTROL) AS s ON (t.CONTROL=s.CONTROL)
                 WHEN MATCHED THEN UPDATE SET OBS1=:o WHEN NOT MATCHED THEN INSERT (CONTROL,OBS1) VALUES (:c2,:o2);",
                ['c' => $control, 'o' => $data['observacion'], 'c2' => $control, 'o2' => $data['observacion']]
            );
        }
        return response()->json(['message' => 'Presupuesto actualizado.']);
    }

    public function destroy(string $id): JsonResponse
    {
        DB::statement("DELETE FROM TRANSACCMAESTRO WHERE CONTROL=? AND TIPTRAN='PRE'", [base64_decode($id)]);
        return response()->json(['message' => 'Presupuesto eliminado.']);
    }

    /**
     * Convertir presupuesto a factura (reutiliza los ítems del PRE)
     * POST /api/v1/presupuestos/{id}/convertir-factura
     */
    public function convertirAFactura(Request $request, string $id): JsonResponse
    {
        $controlPre = base64_decode($id);
        $data = $request->validate([
            'tipoFactura'              => ['required', 'in:CONTADO,CREDITO'],
            'diasVencimiento'          => ['nullable', 'integer', 'min:0'],
            'formasPago'               => ['required', 'array', 'min:1'],
            'formasPago.*.instrumento' => ['required', 'string'],
            'formasPago.*.monto'       => ['required', 'numeric', 'min:0.01'],
        ]);

        $pres = DB::selectOne(
            "SELECT * FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PRE'", [$controlPre]
        );
        if (! $pres) return response()->json(['message' => 'Presupuesto no encontrado.'], 404);

        $detalles = DB::select(
            "SELECT * FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0", [$controlPre]
        );
        if (empty($detalles)) return response()->json(['message' => 'El presupuesto no tiene ítems.'], 422);

        DB::beginTransaction();
        try {
            $empresa = DB::selectOne("SELECT NROINIFAC FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref  = str_pad((string) ((int) $empresa->NROINIFAC), 10, '0', STR_PAD_LEFT);
            [$dias,$hora,$ale] = $this->cadena->componentes();
            $controlFac = "{$dias}{$hora}{$ale}01";

            $diasVenc  = (int) ($data['diasVencimiento'] ?? 0);
            $montoTot  = (float) $pres->MONTOTOT;
            $totalPag  = array_sum(array_column($data['formasPago'], 'monto'));
            $cambio    = max(0, $totalPag - $montoTot);
            $montoSal  = $data['tipoFactura'] === 'CREDITO' ? $montoTot : 0;
            $fechaVenc = now('America/Panama')->addDays($diasVenc)->format('Y-m-d');

            DB::statement(
                "INSERT INTO TRANSACCMAESTRO
                    (CONTROL,TIPREG,TIPTRAN,TIPOFACTURA,CODIGO,NOMBRE,FECEMIS,NUMREF,
                     MONTOBRU,MONTOIMP,MONTODES,MONTOTOT,MONTOSAL,CAMBIO,
                     DIASVEN,FECVENCS,TIPOCLI,CODVEN)
                 VALUES (:ctrl,'1','FAC',:tipofac,:cod,:nom,GETDATE(),:numref,
                         :bru,:imp,:des,:tot,:sal,:cambio,:dias,:fvenc,:tcli,:cven)",
                [
                    'ctrl' => $controlFac, 'tipofac' => $data['tipoFactura'],
                    'cod' => $pres->CODIGO, 'nom' => $pres->NOMBRE,
                    'numref' => $numref, 'bru' => $pres->MONTOBRU, 'imp' => $pres->MONTOIMP,
                    'des' => $pres->MONTODES, 'tot' => $montoTot, 'sal' => round($montoSal,2),
                    'cambio' => round($cambio,2), 'dias' => $diasVenc, 'fvenc' => $fechaVenc,
                    'tcli' => $pres->TIPOCLI ?? '', 'cven' => $pres->CODVEN ?? '',
                ]
            );

            // Copiar detalles del presupuesto a la factura
            foreach ($detalles as $det) {
                [$d2,$h2,$a2] = $this->cadena->componentes();
                DB::statement(
                    "INSERT INTO TRANSACCDETALLES
                        (FECHORA,CONTROL,CODPRO,DESCRIP1,CANTIDAD,PRECOSUNI,
                         MONTODESCUENTO,COSTOADU1,IMPPOR,MONTOIMP,COMPONENTE,INTEGRADO)
                     VALUES (:fh,:ctrl,:cod,:des,:cant,:prec,:dsc,:tot,:imp,:itb,0,0)",
                    [
                        'fh' => "{$d2}{$h2}{$a2}02", 'ctrl' => $controlFac,
                        'cod' => $det->CODPRO, 'des' => $det->DESCRIP1,
                        'cant' => $det->CANTIDAD, 'prec' => $det->PRECOSUNI,
                        'dsc' => $det->MONTODESCUENTO, 'tot' => $det->COSTOADU1,
                        'imp' => $det->IMPPOR, 'itb' => $det->MONTOIMP,
                    ]
                );
                DB::statement(
                    "UPDATE INVENTARIO SET EXISTENCIA=EXISTENCIA-:c WHERE CODPRO=:p AND TIPINV NOT IN('S','SRV')",
                    ['c' => $det->CANTIDAD, 'p' => $det->CODPRO]
                );
            }

            foreach ($data['formasPago'] as $fp) {
                [$d3,$h3,$a3] = $this->cadena->componentes();
                DB::statement(
                    "INSERT INTO TRANSACCPAGOS (FECHORA,CONTROL,CODTAR,MONTOPAG,INTEGRADO) VALUES (:fh,:ctrl,:cod,:m,0)",
                    ['fh' => "{$d3}{$h3}{$a3}03", 'ctrl' => $controlFac, 'cod' => $fp['instrumento'], 'm' => round((float)$fp['monto'],2)]
                );
            }

            DB::statement("UPDATE BASEEMPRESA SET NROINIFAC=NROINIFAC+1 WHERE CONTROL=1");
            DB::statement("DELETE FROM TRANSACCMAESTRO WHERE CONTROL=? AND TIPTRAN='PRE'", [$controlPre]);
            DB::commit();

            return response()->json([
                'message'          => 'Presupuesto convertido a factura exitosamente.',
                'control_factura'  => base64_encode($controlFac),
                'numref'           => $numref,
            ], 201);
        } catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }
}
