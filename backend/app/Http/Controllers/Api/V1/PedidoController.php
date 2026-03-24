<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
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

        $codCliente = strtoupper(trim($data['codcliente']));
        $cliente    = DB::selectOne(
            "SELECT NOMBRE, TIPOCLI FROM BASECLIENTESPROVEEDORES WHERE CODIGO = ? AND TIPREG = '1'",
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
            // Servicios (TIPINV=S o S*) y productos compuestos no necesitan validación
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
            // Número correlativo
            $empresa = DB::selectOne("SELECT NROPEDCLI FROM BASEEMPRESA WHERE CONTROL = 1");
            $numref  = str_pad((string) ((int) ($empresa->NROPEDCLI ?? 1)), 10, '0', STR_PAD_LEFT);

            [$dias, $hora, $ale] = $this->cadena->componentes();
            $control = "{$dias}{$hora}{$ale}PE";

            // Cálculos de totales
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

            $montoTot = $montoBru + $montoImp - $montoDes;

            // TRANSACCMAESTRO
            DB::statement(
                "INSERT INTO TRANSACCMAESTRO
                    (CONTROL,TIPREG,TIPTRAN,CODIGO,NOMBRE,FECEMIS,NUMREF,
                     MONTOBRU,MONTOIMP,MONTODES,MONTOTOT,TIPOCLI,CODVEN)
                 VALUES (:ctrl,'1','PEDxCLI',:cod,:nom,:fecemis,:numref,
                         :bru,:imp,:des,:tot,:tcli,:cven)",
                [
                    'ctrl'    => $control,
                    'cod'     => $codCliente,
                    'nom'     => $cliente->NOMBRE,
                    'numref'  => $numref,
                    'fecemis' => (int) now('America/Panama')->format('Ymd'),
                    'bru'     => round($montoBru, 2),
                    'imp'     => round($montoImp, 2),
                    'des'     => round($montoDes, 2),
                    'tot'     => round($montoTot, 2),
                    'tcli'    => $cliente->TIPOCLI ?? '',
                    'cven'    => $request->user()->erp_coduser ?? '',
                ]
            );

            // TRANSACCDETALLES + reserva de inventario
            foreach ($itemsCalc as $item) {
                [$d2, $h2, $a2] = $this->cadena->componentes();
                $codpro = strtoupper(trim($item['codpro']));

                DB::statement(
                    "INSERT INTO TRANSACCDETALLES
                        (FECHORA,CONTROL,CODPRO,DESCRIP1,CANTIDAD,PRECOSUNI,
                         MONTODESCUENTO,COSTOADU1,IMPPOR,MONTOIMP,COMPONENTE,INTEGRADO)
                     VALUES (:fh,:ctrl,:cod,:des,:cant,:prec,:dsc,:tot,:imp,:itb,0,0)",
                    [
                        'fh'   => "{$d2}{$h2}{$a2}PD",
                        'ctrl' => $control,
                        'cod'  => $codpro,
                        'des'  => $item['descrip'],
                        'cant' => $item['cantidad'],
                        'prec' => $item['precio'],
                        'dsc'  => $item['descuento'] ?? 0,
                        'tot'  => round($item['_sub'] + $item['_itb'], 2),
                        'imp'  => $item['imppor'],
                        'itb'  => round($item['_itb'], 2),
                    ]
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

            DB::statement(
                "INSERT INTO TRANSACCMAESTRO
                    (CONTROL,TIPREG,TIPTRAN,TIPOFACTURA,CODIGO,NOMBRE,FECEMIS,NUMREF,
                     MONTOBRU,MONTOIMP,MONTODES,MONTOTOT,MONTOSAL,CAMBIO,
                     DIASVEN,FECVENCS,TIPOCLI,CODVEN)
                 VALUES (:ctrl,'1','FAC',:tipofac,:cod,:nom,:fecemis,:numref,
                         :bru,:imp,:des,:tot,:sal,:cambio,:dias,:fvenc,:tcli,:cven)",
                [
                    'ctrl'    => $controlFac,
                    'tipofac' => $data['tipoFactura'],
                    'fecemis' => (int) now('America/Panama')->format('Ymd'),
                    'cod'     => $pedido->CODIGO,
                    'nom'     => $pedido->NOMBRE,
                    'numref'  => $numref,
                    'bru'     => $pedido->MONTOBRU,
                    'imp'     => $pedido->MONTOIMP,
                    'des'     => $pedido->MONTODES,
                    'tot'     => round($montoTot, 2),
                    'sal'     => round($montoSal, 2),
                    'cambio'  => round($cambio, 2),
                    'dias'    => $diasVenc,
                    'fvenc'   => $fechaVenc,
                    'tcli'    => $pedido->TIPOCLI ?? '',
                    'cven'    => $pedido->CODVEN ?? '',
                ]
            );

            foreach ($detalles as $det) {
                [$d2, $h2, $a2] = $this->cadena->componentes();
                DB::statement(
                    "INSERT INTO TRANSACCDETALLES
                        (FECHORA,CONTROL,CODPRO,DESCRIP1,CANTIDAD,PRECOSUNI,
                         MONTODESCUENTO,COSTOADU1,IMPPOR,MONTOIMP,COMPONENTE,INTEGRADO)
                     VALUES (:fh,:ctrl,:cod,:des,:cant,:prec,:dsc,:tot,:imp,:itb,0,0)",
                    [
                        'fh'   => "{$d2}{$h2}{$a2}02",
                        'ctrl' => $controlFac,
                        'cod'  => $det->CODPRO,
                        'des'  => $det->DESCRIP1,
                        'cant' => $det->CANTIDAD,
                        'prec' => $det->PRECOSUNI,
                        'dsc'  => $det->MONTODESCUENTO,
                        'tot'  => $det->COSTOADU1,
                        'imp'  => $det->IMPPOR,
                        'itb'  => $det->MONTOIMP,
                    ]
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
                DB::statement(
                    "INSERT INTO TRANSACCPAGOS (FECHORA,CONTROL,CODTAR,MONTOPAG,INTEGRADO)
                     VALUES (:fh,:ctrl,:cod,:m,0)",
                    [
                        'fh'   => "{$d3}{$h3}{$a3}03",
                        'ctrl' => $controlFac,
                        'cod'  => $fp['instrumento'],
                        'm'    => round((float) $fp['monto'], 2),
                    ]
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
