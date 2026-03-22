<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CadenaControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenTrabajoController extends Controller
{
    public function __construct(private readonly CadenaControlService $cadena) {}

    public function index(Request $request): JsonResponse
    {
        $q       = $request->query('q', '');
        $estado  = $request->query('estado', '');   // 0=abierta,1=cerrada
        $perPage = min((int) $request->query('per_page', 25), 100);
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $where  = ["o.INTEGRADO = 0"];
        $params = [];

        if ($q) {
            $where[]      = "(o.CONTROLOT LIKE :q OR c.NOMBRE LIKE :q2)";
            $params['q']  = "%{$q}%";
            $params['q2'] = "%{$q}%";
        }
        if ($estado !== '') { $where[] = "o.ESTADO = :estado"; $params['estado'] = (int) $estado; }

        $whereStr = implode(' AND ', $where);
        $total    = (int) (DB::selectOne(
            "SELECT COUNT(*) AS total FROM TRANSACCOT o
             LEFT JOIN BASECLIENTESPROVEEDORES c ON c.CODIGO = o.CODCLIENTE
             WHERE {$whereStr}", $params
        )->total ?? 0);

        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $ots = DB::select(
            "SELECT o.CONTROLOT, o.CODCLIENTE, c.NOMBRE AS NOMCLIENTE,
                o.ATENDIDO, o.FECHAOT, o.FECHA_ENTREGA, o.ESTADO,
                o.DESCRIPCION, o.CONTROLPRES
             FROM TRANSACCOT o
             LEFT JOIN BASECLIENTESPROVEEDORES c ON c.CODIGO = o.CODCLIENTE
             WHERE {$whereStr}
             ORDER BY o.FECHAOT DESC
             OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY", $params
        );

        return response()->json([
            'data' => $ots,
            'meta' => ['total' => $total, 'per_page' => $perPage, 'current_page' => $page,
                       'last_page' => (int) ceil($total / max(1, $perPage))],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $ot = DB::selectOne(
            "SELECT o.*, c.NOMBRE AS NOMCLIENTE
             FROM TRANSACCOT o
             LEFT JOIN BASECLIENTESPROVEEDORES c ON c.CODIGO = o.CODCLIENTE
             WHERE o.CONTROLOT = ?", [$id]
        );
        if (! $ot) return response()->json(['message' => 'Orden de trabajo no encontrada.'], 404);

        $detalles = DB::select(
            "SELECT * FROM TRANSACCOTDETALLES WHERE CONTROLOT = ?", [$id]
        );
        return response()->json(['data' => ['ot' => $ot, 'detalles' => $detalles]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codcliente'     => ['required', 'string', 'max:20'],
            'atendido'       => ['required', 'string', 'max:50'],
            'fecha_entrega'  => ['required', 'date'],
            'descripcion'    => ['required', 'string', 'max:500'],
            'controlpres'    => ['nullable', 'string', 'max:30'],
        ]);

        $codCliente = strtoupper(trim($data['codcliente']));
        $cliente    = DB::selectOne(
            "SELECT NOMBRE FROM BASECLIENTESPROVEEDORES WHERE CODIGO = ? AND TIPREG = '1'",
            [$codCliente]
        );
        if (! $cliente) {
            return response()->json(['message' => "Cliente '{$codCliente}' no encontrado."], 422);
        }

        [$dias, $hora, $ale] = $this->cadena->componentes();
        $controlOT = "{$dias}{$hora}{$ale}OT";

        DB::statement(
            "INSERT INTO TRANSACCOT
                (CONTROLOT,CODCLIENTE,ATENDIDO,FECHAOT,FECHA_ENTREGA,
                 CONTROLPRES,ESTADO,USUARIO,DESCRIPCION,FECHA_CREACION,INTEGRADO)
             VALUES (:cot,:cod,:at,GETDATE(),:fe,:cpres,0,:usr,:desc,GETDATE(),0)",
            [
                'cot'   => $controlOT,
                'cod'   => $codCliente,
                'at'    => $data['atendido'],
                'fe'    => $data['fecha_entrega'],
                'cpres' => $data['controlpres'] ?? '',
                'usr'   => $request->user()->erp_coduser ?? '',
                'desc'  => $data['descripcion'],
            ]
        );

        return response()->json([
            'message' => 'Orden de trabajo creada.',
            'data' => ['CONTROLOT' => $controlOT, 'NOMCLIENTE' => $cliente->NOMBRE],
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'estado'        => ['sometimes', 'integer', 'in:0,1,2'],
            'fecha_entrega' => ['sometimes', 'date'],
            'atendido'      => ['sometimes', 'string', 'max:50'],
            'descripcion'   => ['sometimes', 'string', 'max:500'],
        ]);

        $sets   = [];
        $params = ['id' => $id];

        if (isset($data['estado']))        { $sets[] = 'ESTADO = :estado';           $params['estado']  = $data['estado']; }
        if (isset($data['fecha_entrega'])) { $sets[] = 'FECHA_ENTREGA = :fe';        $params['fe']      = $data['fecha_entrega']; }
        if (isset($data['atendido']))      { $sets[] = 'ATENDIDO = :at';             $params['at']      = $data['atendido']; }
        if (isset($data['descripcion']))   { $sets[] = 'DESCRIPCION = :desc';        $params['desc']    = $data['descripcion']; }

        if (empty($sets)) {
            return response()->json(['message' => 'Nada que actualizar.'], 422);
        }

        $sets[] = 'USUARIO = :usr';
        $params['usr'] = $request->user()->erp_coduser ?? '';

        DB::statement(
            "UPDATE TRANSACCOT SET " . implode(', ', $sets) . " WHERE CONTROLOT = :id",
            $params
        );

        return response()->json(['message' => 'Orden de trabajo actualizada.']);
    }

    public function destroy(string $id): JsonResponse
    {
        DB::statement("UPDATE TRANSACCOT SET INTEGRADO = 1 WHERE CONTROLOT = ?", [$id]);
        return response()->json(['message' => 'Orden de trabajo anulada.']);
    }
}
