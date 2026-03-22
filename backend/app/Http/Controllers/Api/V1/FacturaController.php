<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function __construct(private readonly CadenaControlService $cadena) {}

    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        $estado = $request->query('estado', '');
        $desde = $request->query('desde', '');
        $hasta = $request->query('hasta', '');
        $perPage = min((int) $request->query('per_page', 25), 100);

        $params = ['tiptran' => 'FAC', 'limit' => $perPage];
        $where = ["m.TIPTRAN = :tiptran", "m.INTEGRADO = 0"];

        if ($q) {
            $where[] = "(m.NUMREF LIKE :q OR m.CODIGO LIKE :q2 OR m.NOMBRE LIKE :q3)";
            $params['q'] = "%{$q}%"; $params['q2'] = "%{$q}%"; $params['q3'] = "%{$q}%";
        }
        if ($estado) { $where[] = "m.TIPOFACTURA = :estado"; $params['estado'] = $estado; }
        if ($desde)  { $where[] = "m.FECEMIS >= :desde"; $params['desde'] = $desde; }
        if ($hasta)  { $where[] = "m.FECEMIS <= :hasta"; $params['hasta'] = $hasta . ' 23:59:59'; }

        $whereStr = implode(' AND ', $where);
        $facturas = DB::select(
            "SELECT TOP (:limit) m.CONTROL, m.NUMREF, m.CODIGO, m.NOMBRE,
                m.FECEMIS, m.TIPTRAN, m.TIPOFACTURA, m.MONTOBRU, m.MONTOIMP,
                m.MONTODES, m.MONTOTOT, m.MONTOSAL, m.DIASVEN, m.FECVENCS,
                m.COM_FISCAL, m.URLCONSULTAFEL,
                d.CUFE, d.QR, d.RESULTADO AS FE_RESULTADO
             FROM TRANSACCMAESTRO m
             LEFT JOIN Documentos d ON d.CONTROL = m.CONTROL
             WHERE {$whereStr} ORDER BY m.FECEMIS DESC", $params
        );
        return response()->json(['data' => $facturas, 'meta' => ['per_page' => $perPage]]);
    }

    public function show(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $maestro = DB::selectOne(
            "SELECT m.*, d.CUFE, d.QR, d.RESULTADO AS FE_RESULTADO, d.PDF
             FROM TRANSACCMAESTRO m
             LEFT JOIN Documentos d ON d.CONTROL = m.CONTROL
             WHERE m.CONTROL = ? AND m.INTEGRADO = 0", [$control]
        );
        if (! $maestro) return response()->json(['message' => 'Factura no encontrada.'], 404);

        $detalles = DB::select(
            "SELECT * FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0 ORDER BY FECHORA DESC", [$control]
        );
        $pagos = DB::select(
            "SELECT p.*, b.NOMBRE AS DESCRIP_PAGO, b.FUNCION FROM TRANSACCPAGOS p
             LEFT JOIN BASEINSTRUMENTOS b ON b.CODTAR = p.CODTAR WHERE p.CONTROL = ?", [$control]
        );
        return response()->json(['data' => ['maestro' => $maestro, 'detalles' => $detalles, 'pagos' => $pagos]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo_cliente'   => ['required', 'string', 'max:20'],
            'nombre_cliente'   => ['required', 'string', 'max:150'],
            'direccion'        => ['nullable', 'string', 'max:200'],
            'tipo_cliente'     => ['required', 'string'],
            'tipo_factura'     => ['required', 'in:CONTADO,CREDITO'],
            'dias_vencimiento' => ['nullable', 'integer', 'min:0'],
            'descuento_global' => ['nullable', 'numeric', 'min:0'],
            'vendedor_cod'     => ['nullable', 'string', 'max:20'],
            'formas_pago'      => ['required', 'array', 'min:1'],
            'formas_pago.*.codtar' => ['required', 'string'],
            'formas_pago.*.monto'  => ['required', 'numeric', 'min:0'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.codpro'   => ['required', 'string', 'max:20'],
            'items.*.descrip'  => ['required', 'string', 'max:150'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio'   => ['required', 'numeric', 'min:0'],
            'items.*.descuento'=> ['nullable', 'numeric', 'min:0'],
            'items.*.imppor'   => ['required', 'integer', 'in:0,7,10,15'],
            'observacion'      => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $empresa = DB::selectOne("SELECT NROINIFAC FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref  = str_pad((string) ((int) $empresa->NROINIFAC), 10, '0', STR_PAD_LEFT);
            [$dias, $hora, $ale] = $this->cadena->componentes();
            $controlMaestro = "{$dias}{$hora}{$ale}01";

            $montoBru = 0; $montoImp = 0;
            $montoDes = (float) ($data['descuento_global'] ?? 0);
            $itemsCalc = [];

            foreach ($data['items'] as $item) {
                $precio = (float) $item['precio'];
                $cant   = (float) $item['cantidad'];
                $desc   = (float) ($item['descuento'] ?? 0);
                $imp    = (int) $item['imppor'];
                $sub    = $cant * ($precio - $desc);
                $itbms  = round($sub * $imp / 100, 2);
                $montoBru += $sub; $montoImp += $itbms;
                $itemsCalc[] = array_merge($item, ['_sub' => $sub, '_itbms' => $itbms]);
            }

            $montoTot  = $montoBru + $montoImp - $montoDes;
            $diasVenc  = (int) ($data['dias_vencimiento'] ?? 0);
            $totalPag  = array_sum(array_column($data['formas_pago'], 'monto'));
            $cambio    = max(0, $totalPag - $montoTot);
            $montoSal  = $data['tipo_factura'] === 'CREDITO' ? $montoTot : 0;
            $fechaVenc = now('America/Panama')->addDays($diasVenc)->format('Y-m-d');

            DB::statement(
                "INSERT INTO TRANSACCMAESTRO
                    (CONTROL,TIPREG,TIPTRAN,TIPOFACTURA,CODIGO,NOMBRE,DIRECC1,
                     FECEMIS,NUMREF,MONTOBRU,MONTOIMP,MONTODES,MONTOTOT,
                     MONTOSAL,CAMBIO,DIASVEN,FECVENCS,TIPOCLI,CODVEN,INTEGRADO)
                 VALUES
                    (:ctrl,'1','FAC',:tipofac,:codigo,:nombre,:direcc,
                     GETDATE(),:numref,:montobru,:montoimp,:montodes,:montotot,
                     :montosal,:cambio,:diasven,:fecvencs,:tipocli,:codven,0)",
                [
                    'ctrl' => $controlMaestro, 'tipofac' => $data['tipo_factura'],
                    'codigo' => strtoupper($data['codigo_cliente']), 'nombre' => $data['nombre_cliente'],
                    'direcc' => $data['direccion'] ?? '', 'numref' => $numref,
                    'montobru' => round($montoBru,2), 'montoimp' => round($montoImp,2),
                    'montodes' => round($montoDes,2), 'montotot' => round($montoTot,2),
                    'montosal' => round($montoSal,2), 'cambio' => round($cambio,2),
                    'diasven' => $diasVenc, 'fecvencs' => $fechaVenc,
                    'tipocli' => $data['tipo_cliente'], 'codven' => $data['vendedor_cod'] ?? '',
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
                        'fh' => "{$d2}{$h2}{$a2}02", 'ctrl' => $controlMaestro,
                        'cod' => strtoupper($item['codpro']), 'des' => $item['descrip'],
                        'cant' => $item['cantidad'], 'prec' => $item['precio'],
                        'dsc' => $item['descuento'] ?? 0,
                        'tot' => round($item['_sub'] + $item['_itbms'], 2),
                        'imp' => $item['imppor'], 'itb' => round($item['_itbms'], 2),
                    ]
                );
                DB::statement(
                    "UPDATE INVENTARIO SET EXISTENCIA = EXISTENCIA - :cant
                     WHERE CODPRO = :cod AND TIPINV NOT IN ('S','SRV')",
                    ['cant' => $item['cantidad'], 'cod' => strtoupper($item['codpro'])]
                );
            }

            foreach ($data['formas_pago'] as $fp) {
                [$d3,$h3,$a3] = $this->cadena->componentes();
                DB::statement(
                    "INSERT INTO TRANSACCPAGOS (FECHORA,CONTROL,CODTAR,MONTOPAG,INTEGRADO)
                     VALUES (:fh,:ctrl,:cod,:monto,0)",
                    ['fh' => "{$d3}{$h3}{$a3}03", 'ctrl' => $controlMaestro,
                     'cod' => $fp['codtar'], 'monto' => round((float)$fp['monto'],2)]
                );
            }

            DB::statement("UPDATE BASEEMPRESA SET NROINIFAC = NROINIFAC + 1 WHERE CONTROL = 1");

            if (!empty($data['observacion'])) {
                DB::statement(
                    "INSERT INTO TRANSACCOBSERVACIONES (CONTROL,OBS1) VALUES (:ctrl,:obs)",
                    ['ctrl' => $controlMaestro, 'obs' => $data['observacion']]
                );
            }

            DB::commit();
            return response()->json([
                'message' => 'Factura creada exitosamente.',
                'control' => base64_encode($controlMaestro),
                'numref'  => $numref,
                'montotot'=> round($montoTot, 2),
            ], 201);
        } catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $control = base64_decode($id);
        $doc = DB::selectOne("SELECT CUFE FROM Documentos WHERE CONTROL = ? AND CUFE IS NOT NULL AND CUFE != ''", [$control]);
        if ($doc) return response()->json(['message' => 'No se puede modificar una factura con CUFE emitido. Use Nota de Crédito.'], 422);

        $data = $request->validate([
            'observacion'      => ['nullable', 'string', 'max:500'],
            'dias_vencimiento' => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($data['observacion'])) {
            DB::statement(
                "MERGE INTO TRANSACCOBSERVACIONES AS t USING (SELECT :c AS CONTROL) AS s ON (t.CONTROL=s.CONTROL)
                 WHEN MATCHED THEN UPDATE SET OBS1=:o WHEN NOT MATCHED THEN INSERT (CONTROL,OBS1) VALUES (:c2,:o2);",
                ['c' => $control, 'o' => $data['observacion'], 'c2' => $control, 'o2' => $data['observacion']]
            );
        }
        if (isset($data['dias_vencimiento'])) {
            DB::statement("UPDATE TRANSACCMAESTRO SET DIASVEN=:d,FECVENCS=:f WHERE CONTROL=:c", [
                'd' => $data['dias_vencimiento'],
                'f' => now('America/Panama')->addDays($data['dias_vencimiento'])->format('Y-m-d'),
                'c' => $control,
            ]);
        }
        return response()->json(['message' => 'Factura actualizada.']);
    }

    public function destroy(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $doc = DB::selectOne("SELECT CUFE FROM Documentos WHERE CONTROL=? AND CUFE IS NOT NULL AND CUFE!=''", [$control]);
        if ($doc) return response()->json(['message' => 'Emita una Nota de Crédito para anular facturas con CUFE.'], 422);

        DB::beginTransaction();
        try {
            $detalles = DB::select("SELECT CODPRO,CANTIDAD FROM TRANSACCDETALLES WHERE CONTROL=? AND COMPONENTE=0", [$control]);
            foreach ($detalles as $d) {
                DB::statement("UPDATE INVENTARIO SET EXISTENCIA=EXISTENCIA+:c WHERE CODPRO=:p AND TIPINV NOT IN('S','SRV')",
                    ['c' => $d->CANTIDAD, 'p' => $d->CODPRO]);
            }
            DB::statement("UPDATE TRANSACCMAESTRO SET INTEGRADO=1 WHERE CONTROL=?", [$control]);
            DB::commit();
            return response()->json(['message' => 'Factura anulada.']);
        } catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }

    public function pdf(string $id): JsonResponse
    {
        $control  = base64_decode($id);
        $doc = DB::selectOne("SELECT PDF,NUMDOCFISCAL FROM Documentos WHERE CONTROL=?", [$control]);
        if ($doc?->PDF) return response()->json(['tipo' => 'dgi', 'pdf' => $doc->PDF, 'numdocfiscal' => $doc->NUMDOCFISCAL]);
        return response()->json(['tipo' => 'interno', 'message' => 'Sin PDF DGI. Enviar via módulo FE.', 'control' => $id]);
    }

    public function ticket(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $maestro = DB::selectOne("SELECT * FROM TRANSACCMAESTRO WHERE CONTROL=?", [$control]);
        if (!$maestro) return response()->json(['message' => 'Factura no encontrada.'], 404);
        return response()->json(['data' => [
            'maestro'  => $maestro,
            'detalles' => DB::select("SELECT * FROM TRANSACCDETALLES WHERE CONTROL=? AND COMPONENTE=0", [$control]),
            'qr'       => DB::selectOne("SELECT QR FROM Documentos WHERE CONTROL=?", [$control])?->QR,
        ]]);
    }
}
