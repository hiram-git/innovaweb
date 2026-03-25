<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
use App\Traits\ErpInsert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    use ErpInsert;

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

        $coduser  = $request->user()->erp_coduser ?? '';
        $erpUser  = $this->getErpUserData($coduser);
        $codven   = $erpUser['codven'];
        $codAlm   = $erpUser['codalmacen'];

        $codCliente = strtoupper(trim($data['codcliente']));
        $cliente    = DB::selectOne(
            "SELECT NOMBRE, TIPOCLI, ISNULL(DIRECC1,'') AS DIRECC1
             FROM BASECLIENTESPROVEEDORES WHERE CODIGO = ? AND TIPREG = '1'",
            [$codCliente]
        );
        if (! $cliente) {
            return response()->json(['message' => "Cliente '{$codCliente}' no encontrado en el ERP."], 422);
        }

        DB::beginTransaction();
        try {
            $empresa = DB::selectOne("SELECT NROINIPRE FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref  = str_pad((string) ((int) ($empresa->NROINIPRE ?? 1)), 10, '0', STR_PAD_LEFT);
            [$dias, $hora, $ale] = $this->cadena->componentes();
            $control  = "{$dias}{$hora}{$ale}PR";
            $fecemis  = (int) now('America/Panama')->format('Ymd');
            $fecemiss = now('America/Panama')->format('Ymd');

            $montoBru = 0.0; $montoImp = 0.0;
            $montoDes = (float) ($data['descuentoGlobal'] ?? 0);
            $itemsCalc = [];

            foreach ($data['items'] as $item) {
                $sub = (float) $item['cantidad'] * ((float) $item['precio'] - (float) ($item['descuento'] ?? 0));
                $itb = round($sub * (int) $item['imppor'] / 100, 2);
                $montoBru += $sub; $montoImp += $itb;
                $itemsCalc[] = array_merge($item, ['_sub' => $sub, '_itb' => $itb]);
            }

            $montoTot = round($montoBru + $montoImp - $montoDes, 2);

            // TRANSACCMAESTRO — esquema completo del ERP
            DB::statement(
                "INSERT INTO TRANSACCMAESTRO (
                    CONTROL,TIPREG,CODIGO,TIPTRAN,NUMREF,DESCRIP1,
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
                    :ctrl,1,:codigo,'PRE',:numref,:descrip1,
                    :fecemis,:fecemiss,0,:fecemis,:fecemiss,
                    :montobru,:montodes,0,:montobru,:montoimp,0,
                    0,:montotot,:montotot,:montotot,0,0,
                    :nombre,0,1,0,:ctrl,0,
                    :codigo,'',0,0,0,:codven,:tipocli,
                    0,0,0,0,0,0,0,
                    :direcc,:codalmacen,0,:montodes,0,:hora,
                    :coduser,0,:montobru,0,:montobru,
                    1,'Balboa',1
                )",
                [
                    'ctrl'      => $control,
                    'codigo'    => $codCliente,
                    'numref'    => $numref,
                    'descrip1'  => "Cotizacion {$numref}",
                    'fecemis'   => $fecemis,
                    'fecemiss'  => $fecemiss,
                    'montobru'  => round($montoBru, 2),
                    'montodes'  => round($montoDes, 2),
                    'montoimp'  => round($montoImp, 2),
                    'montotot'  => $montoTot,
                    'nombre'    => $cliente->NOMBRE,
                    'codven'    => $codven,
                    'tipocli'   => $cliente->TIPOCLI ?? '01',
                    'direcc'    => $cliente->DIRECC1,
                    'codalmacen'=> $codAlm,
                    'hora'      => $hora,
                    'coduser'   => $coduser,
                ]
            );

            foreach ($itemsCalc as $item) {
                [$d2, $h2, $a2] = $this->cadena->componentes();
                $fechora = "{$d2}{$h2}{$a2}PD";
                $codpro  = strtoupper(trim($item['codpro']));
                $dsc     = (float) ($item['descuento'] ?? 0);
                $pordes  = ($item['precio'] > 0 && $item['cantidad'] > 0)
                    ? round($dsc / ($item['precio'] * $item['cantidad']) * 100, 4)
                    : 0.0;

                $this->insertDetalle(
                    $control, $fechora, $codpro, $item['descrip'],
                    (float) $item['cantidad'], (float) $item['precio'], $dsc,
                    (float) $item['imppor'], $item['_itb'], $item['_sub'],
                    'PRE', $fecemis, $fecemiss,
                    $codCliente, $codAlm, $codven, $pordes
                );
            }

            if (!empty($data['observacion'])) {
                DB::statement(
                    "INSERT INTO TRANSACCOBSERVACIONES (CONTROL,OBS1) VALUES (:c,:o)",
                    ['c' => $control, 'o' => $data['observacion']]
                );
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
            $fechaVenc = (int) now('America/Panama')->addDays($diasVenc)->format('Ymd');

            $erpUser   = $this->getErpUserData($request->user()->erp_coduser ?? '');
            $codven    = $pres->CODVEN ?? $erpUser['codven'];
            $codAlm    = $erpUser['codalmacen'];
            $fecemis   = (int) now('America/Panama')->format('Ymd');
            $fecemiss  = now('America/Panama')->format('Ymd');
            $coduser   = $request->user()->erp_coduser ?? '';

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
                    :montobru,:montodes,0,:montobru,:montoimp,0,
                    0,:montotot,:montosal,:montotot,0,0,
                    :nombre,0,1,0,:ctrl,0,
                    :codigo,'',0,0,:cambio,:codven,:tipocli,
                    0,0,0,0,0,0,0,
                    '',:codalmacen,0,:montodes,0,:hora,
                    :coduser,0,:montobru,0,:montobru,
                    1,'Balboa',1
                )",
                [
                    'ctrl'      => $controlFac,
                    'codigo'    => $pres->CODIGO,
                    'tipofac'   => $data['tipoFactura'],
                    'numref'    => $numref,
                    'descrip1'  => "Factura {$numref}",
                    'fecemis'   => $fecemis,
                    'fecemiss'  => $fecemiss,
                    'diasven'   => $diasVenc,
                    'fecvenc'   => $fechaVenc,
                    'fecvencs'  => (string) $fechaVenc,
                    'montobru'  => $pres->MONTOBRU,
                    'montodes'  => $pres->MONTODES,
                    'montoimp'  => $pres->MONTOIMP,
                    'montotot'  => round($montoTot, 2),
                    'montosal'  => round($montoSal, 2),
                    'nombre'    => $pres->NOMBRE,
                    'cambio'    => round($cambio, 2),
                    'codven'    => $codven,
                    'tipocli'   => $pres->TIPOCLI ?? '',
                    'codalmacen'=> $codAlm,
                    'hora'      => $hora,
                    'coduser'   => $coduser,
                ]
            );

            // Copiar detalles del presupuesto a la factura
            foreach ($detalles as $det) {
                [$d2, $h2, $a2] = $this->cadena->componentes();
                $fechora = "{$d2}{$h2}{$a2}02";
                $sub = (float) $det->CANTIDAD * (float) $det->PRECOSUNI - (float) $det->MONTODESCUENTO;

                $this->insertDetalle(
                    $controlFac, $fechora, $det->CODPRO, $det->DESCRIP1,
                    (float) $det->CANTIDAD, (float) $det->PRECOSUNI,
                    (float) $det->MONTODESCUENTO,
                    (float) $det->IMPPOR, (float) $det->MONTOIMP, $sub,
                    'FAC', $fecemis, $fecemiss,
                    $pres->CODIGO, $codAlm, $codven
                );

                DB::statement(
                    "UPDATE INVENTARIO SET EXISTENCIA = EXISTENCIA - :c
                     WHERE CODPRO = :p AND TIPINV NOT IN('S','SRV')",
                    ['c' => $det->CANTIDAD, 'p' => $det->CODPRO]
                );
            }

            foreach ($data['formasPago'] as $fp) {
                [$d3, $h3, $a3] = $this->cadena->componentes();
                $ctrlPago = "{$d3}{$h3}{$a3}03";
                $codtar   = $fp['instrumento'];
                $this->insertPago(
                    $controlFac, $ctrlPago, $codtar,
                    (float) $fp['monto'], $fecemis,
                    $this->getFuncionInstrumento($codtar)
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
