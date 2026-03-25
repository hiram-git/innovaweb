<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
use App\Traits\ErpInsert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pedidos de cliente (TIPTRAN = 'PEDxCLI')
 *
 * A diferencia de los presupuestos, los pedidos reservan inventario
 * (CANRESERVADA += cantidad) al crearse y liberan la reserva al
 * eliminarse o convertirse a factura.
 */
class PedidoController extends Controller
{
    use ErpInsert;

    public function __construct(private readonly CadenaControlService $cadena) {}

    // ─────────────────────────────────────────────────────────────────────────
    // LISTADO
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $q       = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 25), 100);
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $params = ['tiptran' => 'PEDxCLI'];
        $where  = ["m.TIPTRAN = :tiptran"];

        if ($q) {
            $where[] = "(m.NUMREF LIKE :q OR m.CODIGO LIKE :q2 OR m.NOMBRE LIKE :q3)";
            $params['q'] = "%{$q}%"; $params['q2'] = "%{$q}%"; $params['q3'] = "%{$q}%";
        }

        $whereClause = implode(' AND ', $where);

        $total = DB::selectOne(
            "SELECT COUNT(*) AS total FROM TRANSACCMAESTRO m WHERE {$whereClause}", $params
        )->total ?? 0;

        $rows = DB::select(
            "SELECT m.CONTROL, m.NUMREF, m.CODIGO, m.NOMBRE,
                    m.FECEMIS, m.MONTOBRU, m.MONTOIMP, m.MONTODES, m.MONTOTOT
             FROM TRANSACCMAESTRO m
             WHERE {$whereClause}
             ORDER BY m.FECEMIS DESC
             OFFSET {$offset} ROWS FETCH NEXT {$perPage} ROWS ONLY",
            $params
        );

        return response()->json([
            'data' => $rows,
            'meta' => [
                'total'        => (int) $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DETALLE
    // ─────────────────────────────────────────────────────────────────────────

    public function show(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $maestro = DB::selectOne(
            "SELECT * FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PEDxCLI'",
            [$control]
        );
        if (! $maestro) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }
        $detalles = DB::select(
            "SELECT * FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0 ORDER BY FECHORA",
            [$control]
        );
        return response()->json(['data' => ['maestro' => $maestro, 'detalles' => $detalles]]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREAR  — valida disponibilidad y reserva inventario
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codcliente'        => ['required', 'string', 'max:20'],
            'descuentoGlobal'   => ['nullable', 'numeric', 'min:0'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.codpro'    => ['required', 'string', 'max:20'],
            'items.*.descrip'   => ['required', 'string', 'max:150'],
            'items.*.cantidad'  => ['required', 'numeric', 'min:0.01'],
            'items.*.precio'    => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'items.*.imppor'    => ['required', 'integer', 'in:0,7,10,15'],
            'observacion'       => ['nullable', 'string', 'max:500'],
        ]);

        $coduser    = $request->user()->erp_coduser ?? '';
        $erpUser    = $this->getErpUserData($coduser);
        $codven     = $erpUser['codven'];
        $codAlm     = $erpUser['codalmacen'];

        $codCliente = strtoupper(trim($data['codcliente']));
        $cliente    = DB::selectOne(
            "SELECT NOMBRE, TIPOCLI, ISNULL(DIRECC1,'') AS DIRECC1
             FROM BASECLIENTESPROVEEDORES WHERE CODIGO = ? AND TIPREG = '1'",
            [$codCliente]
        );
        if (! $cliente) {
            return response()->json(['message' => "Cliente '{$codCliente}' no encontrado."], 422);
        }

        // ── Validar disponibilidad de inventario ─────────────────────────────
        $sinStock = [];
        foreach ($data['items'] as $item) {
            $inv = DB::selectOne(
                "SELECT EXISTENCIA, ISNULL(CANRESERVADA, 0) AS CANRESERVADA, PROCOMPUESTO, TIPINV
                 FROM INVENTARIO WHERE CODPRO = ?",
                [strtoupper(trim($item['codpro']))]
            );
            if (! $inv) {
                $sinStock[] = "{$item['codpro']} (no encontrado)";
                continue;
            }
            if (in_array($inv->TIPINV, ['S', 'SRV', '1']) || $inv->PROCOMPUESTO == '1') {
                continue;
            }
            $disponible = (float) $inv->EXISTENCIA - (float) $inv->CANRESERVADA;
            if ((float) $item['cantidad'] > $disponible) {
                $sinStock[] = "{$item['codpro']} (disponible: {$disponible}, solicitado: {$item['cantidad']})";
            }
        }
        if (!empty($sinStock)) {
            return response()->json([
                'message' => 'Stock insuficiente para: ' . implode(', ', $sinStock),
                'code'    => 'stock_insuficiente',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $empresa  = DB::selectOne("SELECT NROPEDCLI FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref   = str_pad((string) ((int) ($empresa->NROPEDCLI ?? 1)), 10, '0', STR_PAD_LEFT);
            [$dias, $hora, $ale] = $this->cadena->componentes();
            $control  = "{$dias}{$hora}{$ale}PE";
            $fecemis  = (int) now('America/Panama')->format('Ymd');
            $fecemiss = now('America/Panama')->format('Ymd');

            $montoBru = 0.0; $montoImp = 0.0;
            $montoDes = (float) ($data['descuentoGlobal'] ?? 0);
            $itemsCalc = [];

            foreach ($data['items'] as $item) {
                $sub = (float) $item['cantidad'] * ((float) $item['precio'] - (float) ($item['descuento'] ?? 0));
                $itb = round($sub * (int) $item['imppor'] / 100, 2);
                $montoBru += $sub;
                $montoImp += $itb;
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
                    :ctrl,1,:codigo,'PEDxCLI',:numref,:descrip1,
                    :fecemis,:fecemiss,0,:fecvenc,:fecvencs,
                    :montobru,:montodes,0,:montosub,:montoimp,0,
                    0,:montotot,:montosal,:montoefe,0,0,
                    :nombre,0,1,0,:controldoc,0,
                    :rif,'',0,0,0,:codven,:tipocli,
                    0,0,0,0,0,0,0,
                    :direcc,:codalmacen,0,:montodescuento,0,:hora,
                    :coduser,0,:baseimponible,0,:baseimponibleiva,
                    1,'Balboa',1
                )",
                [
                    'ctrl'             => $control,
                    'codigo'           => $codCliente,
                    'numref'           => $numref,
                    'descrip1'         => "Pedido {$numref}",
                    'fecemis'          => $fecemis,
                    'fecemiss'         => $fecemiss,
                    'fecvenc'          => $fecemis,
                    'fecvencs'         => $fecemiss,
                    'montobru'         => round($montoBru, 2),
                    'montodes'         => round($montoDes, 2),
                    'montosub'         => round($montoBru, 2),
                    'montoimp'         => round($montoImp, 2),
                    'montotot'         => $montoTot,
                    'montosal'         => $montoTot,
                    'montoefe'         => $montoTot,
                    'nombre'           => $cliente->NOMBRE,
                    'controldoc'       => $control,
                    'rif'              => $codCliente,
                    'codven'           => $codven,
                    'tipocli'          => $cliente->TIPOCLI ?? '',
                    'direcc'           => $cliente->DIRECC1,
                    'codalmacen'       => $codAlm,
                    'montodescuento'   => round($montoDes, 2),
                    'hora'             => $hora,
                    'coduser'          => $coduser,
                    'baseimponible'    => round($montoBru, 2),
                    'baseimponibleiva' => round($montoBru, 2),
                ]
            );

            // TRANSACCDETALLES + reserva de inventario
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
                    'PEDxCLI', $fecemis, $fecemiss,
                    $codCliente, $codAlm, $codven, $pordes
                );

                // Reservar inventario (excluir servicios y compuestos)
                $inv = DB::selectOne(
                    "SELECT PROCOMPUESTO, TIPINV FROM INVENTARIO WHERE CODPRO = ?", [$codpro]
                );
                if ($inv && $inv->PROCOMPUESTO == '1') {
                    // Producto compuesto: reservar cada componente
                    $componentes = DB::select(
                        "SELECT CODPROPRO, CANTIDAD FROM INVENTARIOCOMPONENTES WHERE CODPRO = ?", [$codpro]
                    );
                    foreach ($componentes as $comp) {
                        DB::statement(
                            "UPDATE INVENTARIO SET CANRESERVADA = ISNULL(CANRESERVADA,0) + :cant WHERE CODPRO = :cod",
                            ['cant' => (float) $item['cantidad'] * (float) $comp->CANTIDAD, 'cod' => $comp->CODPROPRO]
                        );
                    }
                } elseif ($inv && !in_array($inv->TIPINV, ['S', 'SRV', '1'])) {
                    DB::statement(
                        "UPDATE INVENTARIO SET CANRESERVADA = ISNULL(CANRESERVADA,0) + :cant WHERE CODPRO = :cod",
                        ['cant' => $item['cantidad'], 'cod' => $codpro]
                    );
                }
            }

            // Observación
            if (!empty($data['observacion'])) {
                DB::statement(
                    "INSERT INTO TRANSACCOBSERVACIONES (CONTROL,OBS1) VALUES (:c,:o)",
                    ['c' => $control, 'o' => $data['observacion']]
                );
            }

            DB::statement("UPDATE BASEEMPRESA SET NROPEDCLI = NROPEDCLI + 1 WHERE CONTROL = 1");
            DB::commit();

            return response()->json([
                'message' => 'Pedido creado.',
                'control' => base64_encode($control),
                'numref'  => $numref,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ELIMINAR  — libera reservas de inventario
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(string $id): JsonResponse
    {
        $control = base64_decode($id);
        $maestro = DB::selectOne(
            "SELECT CONTROL FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PEDxCLI'", [$control]
        );
        if (! $maestro) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        $detalles = DB::select(
            "SELECT CODPRO, CANTIDAD FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0", [$control]
        );

        DB::beginTransaction();
        try {
            foreach ($detalles as $det) {
                $inv = DB::selectOne(
                    "SELECT PROCOMPUESTO, TIPINV FROM INVENTARIO WHERE CODPRO = ?", [$det->CODPRO]
                );
                if ($inv && $inv->PROCOMPUESTO == '1') {
                    $componentes = DB::select(
                        "SELECT CODPROPRO, CANTIDAD FROM INVENTARIOCOMPONENTES WHERE CODPRO = ?", [$det->CODPRO]
                    );
                    foreach ($componentes as $comp) {
                        DB::statement(
                            "UPDATE INVENTARIO SET CANRESERVADA = ISNULL(CANRESERVADA,0) - :cant WHERE CODPRO = :cod",
                            ['cant' => (float) $det->CANTIDAD * (float) $comp->CANTIDAD, 'cod' => $comp->CODPROPRO]
                        );
                    }
                } elseif ($inv && !in_array($inv->TIPINV, ['S', 'SRV', '1'])) {
                    DB::statement(
                        "UPDATE INVENTARIO SET CANRESERVADA = ISNULL(CANRESERVADA,0) - :cant WHERE CODPRO = :cod",
                        ['cant' => $det->CANTIDAD, 'cod' => $det->CODPRO]
                    );
                }
            }

            DB::statement(
                "DELETE FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PEDxCLI'", [$control]
            );
            DB::commit();

            return response()->json(['message' => 'Pedido eliminado y reservas liberadas.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONVERTIR A FACTURA  — libera CANRESERVADA, descuenta EXISTENCIA
    // ─────────────────────────────────────────────────────────────────────────

    public function convertirAFactura(Request $request, string $id): JsonResponse
    {
        $controlPed = base64_decode($id);
        $data = $request->validate([
            'tipoFactura'              => ['required', 'in:CONTADO,CREDITO'],
            'diasVencimiento'          => ['nullable', 'integer', 'min:0'],
            'formasPago'               => ['required', 'array', 'min:1'],
            'formasPago.*.instrumento' => ['required', 'string'],
            'formasPago.*.monto'       => ['required', 'numeric', 'min:0.01'],
        ]);

        $pedido = DB::selectOne(
            "SELECT * FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PEDxCLI'", [$controlPed]
        );
        if (! $pedido) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        $detalles = DB::select(
            "SELECT * FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0", [$controlPed]
        );
        if (empty($detalles)) {
            return response()->json(['message' => 'El pedido no tiene ítems.'], 422);
        }

        DB::beginTransaction();
        try {
            $empresa  = DB::selectOne("SELECT NROINIFAC FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref   = str_pad((string) ((int) $empresa->NROINIFAC), 10, '0', STR_PAD_LEFT);
            [$dias, $hora, $ale] = $this->cadena->componentes();
            $controlFac = "{$dias}{$hora}{$ale}01";

            $diasVenc  = (int) ($data['diasVencimiento'] ?? 0);
            $montoTot  = (float) $pedido->MONTOTOT;
            $totalPag  = array_sum(array_column($data['formasPago'], 'monto'));
            $cambio    = max(0.0, $totalPag - $montoTot);
            $montoSal  = $data['tipoFactura'] === 'CREDITO' ? $montoTot : 0.0;
            $fechaVenc = (int) now('America/Panama')->addDays($diasVenc)->format('Ymd');

            $erpUser   = $this->getErpUserData($request->user()->erp_coduser ?? '');
            $codven    = $pedido->CODVEN ?? $erpUser['codven'];
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
                    :montobru,:montodes,0,:montosub,:montoimp,0,
                    0,:montotot,:montosal,:montoefe,0,0,
                    :nombre,0,1,0,:controldoc,0,
                    :rif,'',0,0,:cambio,:codven,:tipocli,
                    0,0,0,0,0,0,0,
                    '',:codalmacen,0,:montodescuento,0,:hora,
                    :coduser,0,:baseimponible,0,:baseimponibleiva,
                    1,'Balboa',1
                )",
                [
                    'ctrl'             => $controlFac,
                    'codigo'           => $pedido->CODIGO,
                    'tipofac'          => $data['tipoFactura'],
                    'numref'           => $numref,
                    'descrip1'         => "Factura {$numref}",
                    'fecemis'          => $fecemis,
                    'fecemiss'         => $fecemiss,
                    'diasven'          => $diasVenc,
                    'fecvenc'          => $fechaVenc,
                    'fecvencs'         => (string) $fechaVenc,
                    'montobru'         => $pedido->MONTOBRU,
                    'montodes'         => $pedido->MONTODES,
                    'montosub'         => $pedido->MONTOBRU,
                    'montoimp'         => $pedido->MONTOIMP,
                    'montotot'         => round($montoTot, 2),
                    'montosal'         => round($montoSal, 2),
                    'montoefe'         => round($montoTot, 2),
                    'nombre'           => $pedido->NOMBRE,
                    'controldoc'       => $controlFac,
                    'rif'              => $pedido->CODIGO,
                    'cambio'           => round($cambio, 2),
                    'codven'           => $codven,
                    'tipocli'          => $pedido->TIPOCLI ?? '',
                    'codalmacen'       => $codAlm,
                    'montodescuento'   => $pedido->MONTODES,
                    'hora'             => $hora,
                    'coduser'          => $coduser,
                    'baseimponible'    => $pedido->MONTOBRU,
                    'baseimponibleiva' => $pedido->MONTOBRU,
                ]
            );

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
                    $pedido->CODIGO, $codAlm, $codven
                );

                $inv = DB::selectOne(
                    "SELECT PROCOMPUESTO, TIPINV FROM INVENTARIO WHERE CODPRO = ?", [$det->CODPRO]
                );
                if ($inv && !in_array($inv->TIPINV, ['S', 'SRV', '1'])) {
                    if ($inv->PROCOMPUESTO == '1') {
                        $componentes = DB::select(
                            "SELECT CODPROPRO, CANTIDAD FROM INVENTARIOCOMPONENTES WHERE CODPRO = ?", [$det->CODPRO]
                        );
                        foreach ($componentes as $comp) {
                            $cantComp = (float) $det->CANTIDAD * (float) $comp->CANTIDAD;
                            DB::statement(
                                "UPDATE INVENTARIO
                                 SET EXISTENCIA   = EXISTENCIA - :c,
                                     CANRESERVADA = ISNULL(CANRESERVADA,0) - :c2
                                 WHERE CODPRO = :p",
                                ['c' => $cantComp, 'c2' => $cantComp, 'p' => $comp->CODPROPRO]
                            );
                        }
                    } else {
                        DB::statement(
                            "UPDATE INVENTARIO
                             SET EXISTENCIA   = EXISTENCIA - :c,
                                 CANRESERVADA = ISNULL(CANRESERVADA,0) - :c2
                             WHERE CODPRO = :p AND TIPINV NOT IN('S','SRV')",
                            ['c' => $det->CANTIDAD, 'c2' => $det->CANTIDAD, 'p' => $det->CODPRO]
                        );
                    }
                }
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

            DB::statement("UPDATE BASEEMPRESA SET NROINIFAC = NROINIFAC + 1 WHERE CONTROL = 1");
            DB::statement(
                "DELETE FROM TRANSACCMAESTRO WHERE CONTROL = ? AND TIPTRAN = 'PEDxCLI'", [$controlPed]
            );
            DB::commit();

            return response()->json([
                'message'         => 'Pedido convertido a factura.',
                'control_factura' => base64_encode($controlFac),
                'numref'          => $numref,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
