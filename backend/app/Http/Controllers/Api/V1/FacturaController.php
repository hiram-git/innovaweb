<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
use App\Services\TicketPdfService;
use App\Traits\ErpInsert;
use App\Traits\ReciboData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    use ErpInsert, ReciboData;

    public function __construct(
        private readonly CadenaControlService $cadena,
        private readonly TicketPdfService     $ticketPdf,
    ) {}

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $q        = $request->query('q', '');
        $tipo     = $request->query('tipo', '');
        $conSaldo = $request->boolean('con_saldo');
        $perPage  = min((int) $request->query('per_page', 25), 100);
        $page     = max(1, (int) $request->query('page', 1));
        $offset   = ($page - 1) * $perPage;

        $where  = ["m.TIPTRAN = 'FAC'"];
        $params = [];

        if ($q) {
            $where[]        = "(m.NUMREF LIKE :q OR m.CODIGO LIKE :q2 OR m.NOMBRE LIKE :q3)";
            $params['q']    = "%{$q}%";
            $params['q2']   = "%{$q}%";
            $params['q3']   = "%{$q}%";
        }
        if ($tipo)     { $where[] = "m.TIPOFACTURA = :tipo";  $params['tipo']  = $tipo; }
        if ($conSaldo) { $where[] = "m.MONTOSAL > 0"; }

        $whereStr = implode(' AND ', $where);

        $total = (int) (DB::selectOne(
            "SELECT COUNT(*) AS total FROM TRANSACCMAESTRO m WHERE {$whereStr}", $params
        )->total ?? 0);

        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $facturas = DB::select(
            "SELECT m.CONTROL AS CONTROLMAESTRO, m.NUMREF AS NROFAC, m.CODIGO AS CODCLIENTE,
                m.NOMBRE AS NOMCLIENTE, m.FECEMISS AS FECHA, m.TIPOFACTURA AS TIPTRAN,
                m.MONTOBRU, m.MONTOIMP, m.MONTODES, m.MONTOTOT, m.MONTOSAL,
                m.URLCONSULTAFEL, m.FECHA_CER, m.PROTO_AUTORIZACION,
                d.RESULTADO AS FE_ESTADO, d.MENSAJE AS FE_MENSAJE
             FROM TRANSACCMAESTRO m
             LEFT JOIN Documentos d ON d.CONTROL = m.CONTROL
             WHERE {$whereStr}
             ORDER BY m.FECEMISS DESC
             OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY", $params
        );

        return response()->json([
            'data' => $facturas,
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / max(1, $perPage)),
            ],
        ]);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $maestro = DB::selectOne(
            "SELECT m.CONTROL AS CONTROLMAESTRO, m.NUMREF AS NROFAC, m.CODIGO AS CODCLIENTE,
                m.NOMBRE AS NOMCLIENTE, m.FECEMIS AS FECHA, m.TIPOFACTURA,
                m.MONTOBRU, m.MONTOIMP, m.MONTODES, m.MONTOTOT, m.MONTOSAL,
                d.CUFE, d.QR, d.RESULTADO AS FE_ESTADO, d.PDF
             FROM TRANSACCMAESTRO m
             LEFT JOIN Documentos d ON d.CONTROL = m.CONTROL
             WHERE m.CONTROL = ?", [$control]
        );
        if (! $maestro) return response()->json(['message' => 'Factura no encontrada.'], 404);

        $detalles = DB::select(
            "SELECT CODPRO, DESCRIP1, CANTIDAD, PRECOSUNI AS PRECIO,
                MONTODESCUENTO AS DESCUENTO, IMPPOR, MONTOIMP
             FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0", [$control]
        );
        $pagos = DB::select(
            "SELECT p.CODTAR AS CODINSTRUMENTO, b.DESCRINSTRUMENTO, b.FUNCION, p.MONTOPAG AS MONTO
             FROM TRANSACCPAGOS p
             LEFT JOIN BASEINSTRUMENTOS b ON b.CODINSTRUMENTO = p.CODTAR
             WHERE p.CONTROL = ?", [$control]
        );

        return response()->json(['data' => ['maestro' => $maestro, 'detalles' => $detalles, 'pagos' => $pagos]]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codcliente'          => ['required', 'string', 'max:20'],
            'tipoFactura'         => ['required', 'in:CONTADO,CREDITO'],
            'diasVencimiento'     => ['nullable', 'integer', 'min:0'],
            'descuentoGlobal'     => ['nullable', 'numeric', 'min:0'],
            'observacion'         => ['nullable', 'string', 'max:500'],
            'formasPago'          => ['nullable', 'array'],
            'formasPago.*.instrumento' => ['nullable', 'string'],
            'formasPago.*.monto'       => ['nullable', 'numeric', 'min:0'],
            'formasPago.*.referencia'  => ['nullable', 'string', 'max:100'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.codpro'      => ['required', 'string', 'max:20'],
            'items.*.descrip'     => ['required', 'string', 'max:150'],
            'items.*.cantidad'    => ['required', 'numeric', 'min:0.01'],
            'items.*.precio'      => ['required', 'numeric', 'min:0'],
            'items.*.descuento'   => ['nullable', 'numeric', 'min:0'],
            'items.*.imppor'      => ['required', 'integer', 'in:0,7,10,15'],
        ]);

        $codCliente   = strtoupper(trim($data['codcliente']));
        $tipoFactura  = $data['tipoFactura'];
        $diasVenc     = (int) ($data['diasVencimiento'] ?? 0);
        $montoDes     = (float) ($data['descuentoGlobal'] ?? 0);
        $observacion  = $data['observacion'] ?? '';
        $formasPago   = $data['formasPago'] ?? [];

        if ($tipoFactura === 'CONTADO' && empty($formasPago)) {
            return response()->json(['message' => 'Facturas de contado requieren al menos una forma de pago.'], 422);
        }

        // Lookup del cliente en el ERP
        $cliente = DB::selectOne(
            "SELECT NOMBRE, TIPOCLI, DIRECC1 FROM BASECLIENTESPROVEEDORES WHERE CODIGO = ? AND TIPREG = '1'",
            [$codCliente]
        );
        if (! $cliente) {
            return response()->json(['message' => "Cliente '{$codCliente}' no encontrado en el ERP."], 422);
        }

        $coduser  = $request->user()->erp_coduser ?? '';
        $erpUser  = $this->getErpUserData($coduser);
        $codven   = $erpUser['codven'];
        $codAlm   = $erpUser['codalmacen'];

        DB::beginTransaction();
        try {
            $empresa = DB::selectOne("SELECT NROINIFAC FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref  = str_pad((string) ((int) ($empresa->NROINIFAC ?? 1)), 10, '0', STR_PAD_LEFT);

            [$dias, $hora, $ale] = $this->cadena->componentes();
            $controlMaestro = "{$dias}{$hora}{$ale}01";
            $fecemis  = $this->cadena->fechaClarion();
            $fecemiss = now('America/Panama')->format('Ymd');
            $fecVenc  = (int) now('America/Panama')->addDays($diasVenc)->format('Ymd');

            $montoBru  = 0.0;
            $montoImp  = 0.0;
            $itemsCalc = [];

            foreach ($data['items'] as $item) {
                $sub   = (float) $item['cantidad'] * ((float) $item['precio'] - (float) ($item['descuento'] ?? 0));
                $itbms = round($sub * (int) $item['imppor'] / 100, 2);
                $montoBru += $sub;
                $montoImp += $itbms;
                $itemsCalc[] = array_merge($item, ['_sub' => $sub, '_itbms' => $itbms]);
            }

            $montoTot  = round($montoBru + $montoImp - $montoDes, 2);
            $totalPag  = array_sum(array_column($formasPago, 'monto'));
            $cambio    = max(0.0, round($totalPag - $montoTot, 2));
            $montoSal  = $tipoFactura === 'CREDITO' ? $montoTot : 0.0;

            // TRANSACCMAESTRO — esquema completo del ERP
            DB::statement(
                "INSERT INTO TRANSACCMAESTRO (
                    CONTROL,TIPREG,CODIGO,TIPTRAN,TIPOFACTURA,NUMREF,DESCRIP1,
                    FECEMIS,FECEMISS,DIASVEN,FECVENC,FECVENCS,
                    MONTOBRU,MONTODES,PORDES,MONTOSUB,MONTOIMP,PORIMP,
                    MONTOPAG,MONTOTOT,MONTOSAL,MONTOEFE,MONTOCHE,MONTOTAR,
                    NOMBRE,MARCA,CONTADOR,TOTCONTADOR,CONTROLDOC,MONTOPAGF,
                    RIF,NIT,MONTOCOS,TIPODOC,CAMBIO,CODVEN,TIPOCLI,
                    COMISV,COMISC,MONTORET,PORRET,MONTOPA,COMISVEN,COMISCOB,
                    DIRECCION,CODALENT,ACTBANCO,MONTODESCUENTO,MARCARE,HORA,
                    CODUSER,TOTALEXENTAS,BASEIMPONIBLE,OTRAPLAZA,BASEIMPONIBLEIVA,
                    FACTORCAMBIO,SIGNOMONEDA,PARCONTROL
                ) VALUES (
                    :ctrl,1,:codigo,'FAC',:tipofac,:numref,:descrip1,
                    :fecemis,:fecemiss,:diasven,:fecvenc,:fecvencs,
                    :montobru,:montodes,0,:montosub,:montoimp,0,
                    0,:montotot,:montosal,:montoefe,0,0,
                    :nombre,0,1,0,:controldoc,0,
                    :rif,'',0,0,:cambio,:codven,:tipocli,
                    0,0,0,0,0,0,0,
                    :direcc,:codalmacen,0,:montodescuento,0,:hora,
                    :coduser,0,:baseimponible,0,:baseimponibleiva,
                    1,'Balboa',1
                )",
                [
                    'ctrl'             => $controlMaestro,
                    'codigo'           => $codCliente,
                    'tipofac'          => $tipoFactura,
                    'numref'           => $numref,
                    'descrip1'         => "Factura {$numref}",
                    'fecemis'          => $fecemis,
                    'fecemiss'         => $fecemiss,
                    'diasven'          => $diasVenc,
                    'fecvenc'          => $fecVenc,
                    'fecvencs'         => (string) $fecVenc,
                    'montobru'         => round($montoBru, 2),
                    'montodes'         => round($montoDes, 2),
                    'montosub'         => round($montoBru, 2),
                    'montoimp'         => round($montoImp, 2),
                    'montotot'         => $montoTot,
                    'montosal'         => round($montoSal, 2),
                    'montoefe'         => $montoTot,
                    'nombre'           => $cliente->NOMBRE,
                    'controldoc'       => $controlMaestro,
                    'rif'              => $codCliente,
                    'cambio'           => $cambio,
                    'codven'           => $codven,
                    'tipocli'          => $cliente->TIPOCLI ?? '01',
                    'direcc'           => $cliente->DIRECC1 ?? '',
                    'codalmacen'       => $codAlm,
                    'montodescuento'   => round($montoDes, 2),
                    'hora'             => $hora,
                    'coduser'          => $coduser,
                    'baseimponible'    => round($montoBru, 2),
                    'baseimponibleiva' => round($montoBru, 2),
                ]
            );

            foreach ($itemsCalc as $item) {
                [$d2, $h2, $a2] = $this->cadena->componentes();
                $fechora = "{$d2}{$h2}{$a2}02";
                $codpro  = strtoupper(trim($item['codpro']));
                $dsc     = (float) ($item['descuento'] ?? 0);
                $pordes  = ($item['precio'] > 0 && $item['cantidad'] > 0)
                    ? round($dsc / ($item['precio'] * $item['cantidad']) * 100, 4)
                    : 0.0;

                $this->insertDetalle(
                    $controlMaestro, $fechora, $codpro, $item['descrip'],
                    (float) $item['cantidad'], (float) $item['precio'], $dsc,
                    (float) $item['imppor'], $item['_itbms'], $item['_sub'],
                    'FAC', $fecemis, $fecemiss,
                    $codCliente, $codAlm, $codven, $pordes
                );

                DB::statement(
                    "UPDATE INVENTARIO SET EXISTENCIA = EXISTENCIA - :cant
                     WHERE CODPRO = :cod AND TIPINV NOT IN ('S','SRV')",
                    ['cant' => $item['cantidad'], 'cod' => $codpro]
                );
            }

            foreach ($formasPago as $fp) {
                [$d3, $h3, $a3] = $this->cadena->componentes();
                $ctrlPago = "{$d3}{$h3}{$a3}03";
                $codtar   = $fp['instrumento'] ?? '';
                $this->insertPago(
                    $controlMaestro, $ctrlPago, $codtar,
                    (float) ($fp['monto'] ?? 0), $fecemis,
                    $this->getFuncionInstrumento($codtar)
                );
            }

            DB::statement("UPDATE BASEEMPRESA SET NROINIFAC = NROINIFAC + 1 WHERE CONTROL = 1");

            if ($observacion !== '') {
                DB::statement(
                    "INSERT INTO TRANSACCOBSERVACIONES (CONTROL,OBS1) VALUES (:ctrl,:obs)",
                    ['ctrl' => $controlMaestro, 'obs' => $observacion]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Factura creada exitosamente.',
                'data' => [
                    'CONTROLMAESTRO' => base64_encode($controlMaestro),
                    'NROFAC'         => $numref,
                    'NOMCLIENTE'     => $cliente->NOMBRE,
                    'MONTOTOT'       => $montoTot,
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, string $id): JsonResponse
    {
        $control = base64_decode($id);
        $doc = DB::selectOne(
            "SELECT CUFE FROM Documentos WHERE CONTROL = ? AND CUFE IS NOT NULL AND CUFE != ''", [$control]
        );
        if ($doc) {
            return response()->json(['message' => 'No se puede modificar una factura con CUFE emitido. Use Nota de Crédito.'], 422);
        }

        $data = $request->validate([
            'observacion'     => ['nullable', 'string', 'max:500'],
            'diasVencimiento' => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($data['observacion'])) {
            DB::statement(
                "MERGE INTO TRANSACCOBSERVACIONES AS t USING (SELECT :c AS CONTROL) AS s ON (t.CONTROL=s.CONTROL)
                 WHEN MATCHED THEN UPDATE SET OBS1=:o WHEN NOT MATCHED THEN INSERT (CONTROL,OBS1) VALUES (:c2,:o2);",
                ['c' => $control, 'o' => $data['observacion'], 'c2' => $control, 'o2' => $data['observacion']]
            );
        }
        if (isset($data['diasVencimiento'])) {
            DB::statement("UPDATE TRANSACCMAESTRO SET DIASVEN=:d, FECVENCS=:f WHERE CONTROL=:c", [
                'd' => $data['diasVencimiento'],
                'f' => (int) now('America/Panama')->addDays($data['diasVencimiento'])->format('Ymd'),
                'c' => $control,
            ]);
        }

        return response()->json(['message' => 'Factura actualizada.']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $factura = DB::selectOne(
            "SELECT m.CONTROL, d.CUFE FROM TRANSACCMAESTRO m
             LEFT JOIN Documentos d ON d.CONTROL = m.CONTROL AND d.CUFE != ''
             WHERE m.CONTROL = ?", [$control]
        );
        if (! $factura) return response()->json(['message' => 'Factura no encontrada.'], 404);
        if (! empty($factura->CUFE)) {
            return response()->json(['message' => 'Emita una Nota de Crédito (DGI) para anular facturas con CUFE.'], 422);
        }

        DB::beginTransaction();
        try {
            foreach (DB::select("SELECT CODPRO, CANTIDAD FROM TRANSACCDETALLES WHERE CONTROL=? AND COMPONENTE=0", [$control]) as $d) {
                DB::statement(
                    "UPDATE INVENTARIO SET EXISTENCIA = EXISTENCIA + :c WHERE CODPRO = :p AND TIPINV NOT IN('S','SRV')",
                    ['c' => $d->CANTIDAD, 'p' => $d->CODPRO]
                );
            }
            DB::statement("DELETE FROM TRANSACCMAESTRO WHERE CONTROL = ?", [$control]);
            DB::commit();
            return response()->json(['message' => 'Factura anulada.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function recibo(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $data = $this->buildRecibo($control);
        if (! $data || $data['maestro']?->TIPTRAN !== 'FAC') {
            return response()->json(['message' => 'Factura no encontrada.'], 404);
        }
        return response()->json(['data' => $data]);
    }

    public function ticketPdf(string $id): Response
    {
        $control = base64_decode($id);
        $data = $this->buildRecibo($control);
        if (! $data || $data['maestro']?->TIPTRAN !== 'FAC') {
            abort(404, 'Factura no encontrada.');
        }
        $pdf = $this->ticketPdf->generar($data);
        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="FAC_' . trim($data['maestro']->NUMREF ?? $id) . '.pdf"',
        ]);
    }

    public function pdf(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $doc = DB::selectOne("SELECT PDF, NUMDOCFISCAL FROM Documentos WHERE CONTROL=?", [$control]);
        if ($doc?->PDF) return response()->json(['tipo' => 'dgi', 'pdf' => $doc->PDF, 'numdocfiscal' => $doc->NUMDOCFISCAL]);
        return response()->json(['tipo' => 'interno', 'message' => 'Sin PDF DGI. Enviar via módulo FE.', 'control' => $id]);
    }

    public function ticket(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $maestro = DB::selectOne("SELECT * FROM TRANSACCMAESTRO WHERE CONTROL=?", [$control]);
        if (! $maestro) return response()->json(['message' => 'Factura no encontrada.'], 404);
        return response()->json(['data' => [
            'maestro'  => $maestro,
            'detalles' => DB::select("SELECT * FROM TRANSACCDETALLES WHERE CONTROL=? AND COMPONENTE=0", [$control]),
            'qr'       => DB::selectOne("SELECT QR FROM Documentos WHERE CONTROL=?", [$control])?->QR,
        ]]);
    }
}
