<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q       = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 20), 100);
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $count = DB::selectOne(
            "SELECT COUNT(*) AS total FROM INVENTARIO
             WHERE ACTIVO = 0
               AND (CODPRO LIKE :q OR DESCRIP1 LIKE :q2 OR CODREF LIKE :q3 OR DESCRIP2 LIKE :q4)",
            ['q' => "%{$q}%", 'q2' => "%{$q}%", 'q3' => "%{$q}%", 'q4' => "%{$q}%"]
        );
        $total = $count?->total ?? 0;

        $items = DB::select(
            "SELECT CODPRO, DESCRIP1, EXISTENCIA, CANRESERVADA, ISNULL(CANVEN,0) AS CANVEN,
                    PRECVEN1, IMPPOR, PROCOMPUESTO, TIPINV, UNIDAD
             FROM INVENTARIO
             WHERE ACTIVO = 0
               AND (CODPRO LIKE :q OR DESCRIP1 LIKE :q2 OR CODREF LIKE :q3 OR DESCRIP2 LIKE :q4)
             ORDER BY DESCRIP1
             OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY",
            ['q' => "%{$q}%", 'q2' => "%{$q}%", 'q3' => "%{$q}%", 'q4' => "%{$q}%", 'offset' => $offset, 'limit' => $perPage]
        );

        return response()->json([
            'data' => $items,
            'meta' => [
                'total'        => (int) $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    public function show(string $codigo): JsonResponse
    {
        $item = DB::selectOne("SELECT * FROM INVENTARIO WHERE CODPRO = ? AND INTEGRADO = 0", [trim($codigo)]);
        if (! $item) return response()->json(['message' => 'Producto no encontrado.'], 404);
        return response()->json(['data' => $item]);
    }

    public function disponibilidad(string $codigo): JsonResponse
    {
        $item = DB::selectOne("SELECT CODPRO, EXISTENCIA, CANRESERVADA FROM INVENTARIO WHERE CODPRO = ?", [trim($codigo)]);
        if (! $item) return response()->json(['message' => 'Producto no encontrado.'], 404);
        return response()->json(['data' => ['disponible' => (float)$item->EXISTENCIA - (float)$item->CANRESERVADA]]);
    }
}
