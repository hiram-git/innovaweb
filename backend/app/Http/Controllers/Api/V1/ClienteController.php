<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Listado paginado de clientes desde BASECLIENTESPROVEEDORES
     */
    public function index(Request $request): JsonResponse
    {
        $q       = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 20), 100);
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $where  = ["cp.TIPREG = '1'", "cp.INTEGRADO = '0'"];
        $params = [];

        if ($q) {
            $where[]       = "(cp.NOMBRE LIKE :q OR cp.RIF LIKE :q2 OR cp.CODIGO LIKE :q3)";
            $params['q']   = "%{$q}%";
            $params['q2']  = "%{$q}%";
            $params['q3']  = "%{$q}%";
        }

        $whereStr = implode(' AND ', $where);

        $total = (int) (DB::selectOne(
            "SELECT COUNT(*) AS total FROM BASECLIENTESPROVEEDORES cp WHERE {$whereStr}", $params
        )?->total ?? 0);

        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $clientes = DB::select(
            "SELECT cp.CODIGO, cp.NOMBRE, cp.RIF, cp.NIT,
                cp.DIRECC1, cp.NUMTEL, cp.DIRCORREO,
                cp.TIPOCLI, cp.TIPOCOMERCIO, cp.DIASCRE,
                cp.CONESPECIAL, cp.PORRETIMP,
                bp.DESNOMBREEGEO1 AS provincia,
                bd.DESNOMBREEGEO2 AS distrito,
                bc.DESNOMBREEGEO3 AS corregimiento
             FROM BASECLIENTESPROVEEDORES cp
             LEFT JOIN BASEPROVINCIA bp ON cp.NOMBREEGEO1 = bp.NOMBREEGEO1
             LEFT JOIN BASEDISTRITO bd
                    ON bd.NOMBREEGEO2 = cp.NOMBREEGEO2
                   AND bd.NOMBREEGEO1 = cp.NOMBREEGEO1
             LEFT JOIN BASECORREGIMIENTO bc
                    ON bc.NOMBREEGEO3 = cp.NOMBREEGEO3
                   AND bc.NOMBREEGEO2 = cp.NOMBREEGEO2
                   AND bc.NOMBREEGEO1 = cp.NOMBREEGEO1
             WHERE {$whereStr}
             ORDER BY cp.NOMBRE
             OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY", $params
        );

        return response()->json([
            'data' => $clientes,
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / max(1, $perPage)),
            ],
        ]);
    }

    /**
     * Detalle de un cliente por CODIGO
     */
    public function show(string $codigo): JsonResponse
    {
        $cliente = DB::selectOne(
            "SELECT
                cp.*,
                bp.DESNOMBREEGEO1 AS provincia,
                bd.DESNOMBREEGEO2 AS distrito,
                bc.DESNOMBREEGEO3 AS corregimiento
             FROM BASECLIENTESPROVEEDORES cp
             LEFT JOIN BASEPROVINCIA bp ON cp.NOMBREEGEO1 = bp.NOMBREEGEO1
             LEFT JOIN BASEDISTRITO bd
                    ON bd.NOMBREEGEO2 = cp.NOMBREEGEO2
                   AND bd.NOMBREEGEO1 = cp.NOMBREEGEO1
             LEFT JOIN BASECORREGIMIENTO bc
                    ON bc.NOMBREEGEO3 = cp.NOMBREEGEO3
                   AND bc.NOMBREEGEO2 = cp.NOMBREEGEO2
                   AND bc.NOMBREEGEO1 = cp.NOMBREEGEO1
             WHERE cp.CODIGO = ?
               AND cp.TIPREG = '1'
               AND cp.INTEGRADO = '0'",
            [strtoupper(trim($codigo))]
        );

        if (! $cliente) {
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        }

        return response()->json(['data' => $cliente]);
    }

    /**
     * Buscar cliente por RUC para autocompletar en la factura
     */
    public function buscarPorRuc(string $ruc): JsonResponse
    {
        $cliente = DB::selectOne(
            "SELECT CODIGO, NOMBRE, RIF, NIT, DIRECC1, NUMTEL, DIRCORREO,
                    TIPOCLI, TIPOCOMERCIO, CONESPECIAL, PORRETIMP
             FROM BASECLIENTESPROVEEDORES
             WHERE RIF = ?
               AND TIPREG = '1'
               AND INTEGRADO = '0'",
            [trim($ruc)]
        );

        if (! $cliente) {
            return response()->json(['message' => 'No se encontró cliente con ese RUC.'], 404);
        }

        return response()->json(['data' => $cliente]);
    }

    /**
     * Crear nuevo cliente en BASECLIENTESPROVEEDORES
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo'        => ['required', 'string', 'max:20'],
            'nombre'        => ['required', 'string', 'max:150'],
            'rif'           => ['nullable', 'string', 'max:20'],
            'nit'           => ['nullable', 'string', 'max:5'],
            'direcc1'       => ['nullable', 'string', 'max:200'],
            'numtel'        => ['nullable', 'string', 'max:20'],
            'dircorreo'     => ['nullable', 'email', 'max:100'],
            'tipocli'       => ['required', 'string'],
            'tipocomercio'  => ['nullable', 'integer'],
            'diascre'       => ['nullable', 'integer'],
            'conespecial'   => ['nullable', 'boolean'],
            'porretimp'     => ['nullable', 'integer'],
        ]);

        DB::statement(
            "INSERT INTO BASECLIENTESPROVEEDORES
                (CODIGO, TIPREG, INTEGRADO, NOMBRE, RIF, NIT,
                 DIRECC1, NUMTEL, DIRCORREO, TIPOCLI, TIPOCOMERCIO,
                 DIASCRE, CONESPECIAL, PORRETIMP)
             VALUES
                (:codigo, '1', '0', :nombre, :rif, :nit,
                 :direcc1, :numtel, :dircorreo, :tipocli, :tipocomercio,
                 :diascre, :conespecial, :porretimp)",
            [
                'codigo'       => strtoupper(trim($data['codigo'])),
                'nombre'       => $data['nombre'],
                'rif'          => $data['rif'] ?? '',
                'nit'          => $data['nit'] ?? '',
                'direcc1'      => $data['direcc1'] ?? '',
                'numtel'       => $data['numtel'] ?? '',
                'dircorreo'    => $data['dircorreo'] ?? '',
                'tipocli'      => $data['tipocli'],
                'tipocomercio' => $data['tipocomercio'] ?? 1,
                'diascre'      => $data['diascre'] ?? 0,
                'conespecial'  => $data['conespecial'] ? 1 : 0,
                'porretimp'    => $data['porretimp'] ?? 0,
            ]
        );

        return response()->json([
            'message' => 'Cliente creado exitosamente.',
            'codigo'  => strtoupper(trim($data['codigo'])),
        ], 201);
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, string $codigo): JsonResponse
    {
        $data = $request->validate([
            'nombre'        => ['sometimes', 'string', 'max:150'],
            'rif'           => ['sometimes', 'nullable', 'string', 'max:20'],
            'nit'           => ['sometimes', 'nullable', 'string', 'max:5'],
            'direcc1'       => ['sometimes', 'nullable', 'string', 'max:200'],
            'numtel'        => ['sometimes', 'nullable', 'string', 'max:20'],
            'dircorreo'     => ['sometimes', 'nullable', 'email', 'max:100'],
            'tipocli'       => ['sometimes', 'string'],
            'tipocomercio'  => ['sometimes', 'nullable', 'integer'],
        ]);

        $sets = [];
        $bindings = [];
        foreach ($data as $campo => $valor) {
            $sets[] = strtoupper($campo) . ' = :' . $campo;
            $bindings[$campo] = $valor;
        }

        if (empty($sets)) {
            return response()->json(['message' => 'No se proporcionaron campos para actualizar.'], 422);
        }

        $bindings['codigo'] = strtoupper(trim($codigo));
        DB::statement(
            "UPDATE BASECLIENTESPROVEEDORES SET " . implode(', ', $sets) .
            " WHERE CODIGO = :codigo AND TIPREG = '1'",
            $bindings
        );

        return response()->json(['message' => 'Cliente actualizado exitosamente.']);
    }

    /**
     * Eliminar cliente (soft delete marcando INTEGRADO = 1)
     */
    public function destroy(string $codigo): JsonResponse
    {
        DB::statement(
            "UPDATE BASECLIENTESPROVEEDORES SET INTEGRADO = '1'
             WHERE CODIGO = ? AND TIPREG = '1'",
            [strtoupper(trim($codigo))]
        );

        return response()->json(['message' => 'Cliente eliminado exitosamente.']);
    }
}
