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
        $estado  = $request->query('estado', '');  // 0=abierta, 1=cerrada
        $perPage = min((int) $request->query('per_page', 25), 100);

        $params = ['limit' => $perPage];
        $where  = ['ot.INTEGRADO = 0'];

        if ($q) {
            $where[] = "(ot.CONTROLOT LIKE :q OR ot.CODCLIENTE LIKE :q2)";
            $params['q'] = "%{$q}%"; $params['q2'] = "%{$q}%";
        }
        if ($estado !== '') {
            $where[] = "ot.ESTADO = :estado";
            $params['estado'] = (int) $estado;
        }

        $whereStr = implode(' AND ', $where);
        $ots = DB::select(
            "SELECT TOP (:limit) ot.*, cli.NOMBRE AS NOMBRE_CLIENTE
             FROM TRANSACCOT ot
             LEFT JOIN BASECLIENTESPROVEEDORES cli ON cli.CODIGO = ot.CODCLIENTE AND cli.TIPREG='1'
             WHERE {$whereStr}
             ORDER BY ot.FECHA_CREACION DESC", $params
        );
        return response()->json(['data' => $ots]);
    }

    public function show(string $id): JsonResponse
    {
        $ot = DB::selectOne(
            "SELECT ot.*, cli.NOMBRE AS NOMBRE_CLIENTE
             FROM TRANSACCOT ot
             LEFT JOIN BASECLIENTESPROVEEDORES cli ON cli.CODIGO = ot.CODCLIENTE AND cli.TIPREG='1'
             WHERE ot.CONTROLOT = ? AND ot.INTEGRADO = 0", [$id]
        );
        if (! $ot) return response()->json(['message' => 'Orden de trabajo no encontrada.'], 404);

        $detalles = DB::select(
            "SELECT * FROM TRANSACCOTDETALLES WHERE CONTROLOT = ? ORDER BY CONTROLOTDETALLES",
            [$id]
        );
        return response()->json(['data' => ['ot' => $ot, 'detalles' => $detalles]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cliente_cod'    => ['required', 'string', 'max:20'],
            'responsable'    => ['required', 'string', 'max:50'],
            'fecha_entrada'  => ['required', 'date'],
            'fecha_entrega'  => ['required', 'date', 'after_or_equal:fecha_entrada'],
            'control_pres'   => ['nullable', 'string', 'max:30'],
            'productos'      => ['required', 'array', 'min:1'],
            'productos.*.codprod'       => ['required', 'string', 'max:20'],
            'productos.*.cantidad'      => ['required', 'numeric', 'min:1'],
            'productos.*.descripcion'   => ['required', 'string', 'max:200'],
            'productos.*.material'      => ['nullable', 'string', 'max:100'],
            'productos.*.caras'         => ['nullable', 'integer', 'min:1'],
            'productos.*.color'         => ['nullable', 'string', 'max:50'],
            'productos.*.acabado'       => ['nullable', 'string', 'max:100'],
            'productos.*.observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            [$dias, $hora, $ale] = $this->cadena->componentes();
            $controlOT = "{$dias}{$hora}{$ale}OT";

            DB::statement(
                "INSERT INTO TRANSACCOT
                    (CONTROLOT,CODCLIENTE,ATENDIDO,FECHAOT,FECHA_ENTREGA,
                     CONTROLPRES,ESTADO,USUARIO,FECHA_CREACION,CONTROLMAESTRO,INTEGRADO)
                 VALUES (:cot,:cod,:atend,:fent,:fent2,:cpres,0,:resp,GETDATE(),:cmae,0)",
                [
                    'cot'   => $controlOT,
                    'cod'   => strtoupper($data['cliente_cod']),
                    'atend' => strtoupper($data['cliente_cod']),
                    'fent'  => date('Y-m-d H:i:s', strtotime($data['fecha_entrada'])),
                    'fent2' => date('Y-m-d H:i:s', strtotime($data['fecha_entrega'])),
                    'cpres' => $data['control_pres'] ?? '',
                    'resp'  => $data['responsable'],
                    'cmae'  => $data['control_pres'] ?? $controlOT,
                ]
            );

            foreach ($data['productos'] as $prod) {
                [$d2,$h2,$a2] = $this->cadena->componentes();
                $controlDet = "{$d2}{$h2}{$a2}OD";
                DB::statement(
                    "INSERT INTO TRANSACCOTDETALLES
                        (CONTROLOTDETALLES,CONTROLOT,CODPROD,CANTIDAD,DESCRIPCION,
                         MATERIAL,NUM_CARAS,TIPO,ACABADOS,OBSERVACIONES)
                     VALUES (:cd,:cot,:cod,:cant,:des,:mat,:caras,:tipo,:acab,:obs)",
                    [
                        'cd'    => $controlDet, 'cot' => $controlOT,
                        'cod'   => strtoupper($prod['codprod']),
                        'cant'  => $prod['cantidad'], 'des' => $prod['descripcion'],
                        'mat'   => $prod['material'] ?? '',
                        'caras' => $prod['caras'] ?? 1,
                        'tipo'  => $prod['color'] ?? '',
                        'acab'  => $prod['acabado'] ?? '',
                        'obs'   => $prod['observaciones'] ?? '',
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'message'   => 'Orden de trabajo creada.',
                'controlot' => $controlOT,
            ], 201);
        } catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'estado'          => ['nullable', 'integer', 'in:0,1,2'],
            'fecha_entrega'   => ['nullable', 'date'],
            'responsable'     => ['nullable', 'string', 'max:50'],
        ]);

        $sets = []; $bindings = ['id' => $id];
        if (isset($data['estado'])) { $sets[] = 'ESTADO = :estado'; $bindings['estado'] = $data['estado']; }
        if (isset($data['fecha_entrega'])) { $sets[] = 'FECHA_ENTREGA = :fent'; $bindings['fent'] = $data['fecha_entrega']; }
        if (isset($data['responsable'])) { $sets[] = 'USUARIO = :resp'; $bindings['resp'] = $data['responsable']; }
        if (empty($sets)) return response()->json(['message' => 'Nada que actualizar.'], 422);

        DB::statement("UPDATE TRANSACCOT SET " . implode(',', $sets) . " WHERE CONTROLOT = :id", $bindings);
        return response()->json(['message' => 'Orden de trabajo actualizada.']);
    }

    public function destroy(string $id): JsonResponse
    {
        DB::statement("UPDATE TRANSACCOT SET INTEGRADO = 1 WHERE CONTROLOT = ?", [$id]);
        return response()->json(['message' => 'Orden de trabajo eliminada.']);
    }
}
