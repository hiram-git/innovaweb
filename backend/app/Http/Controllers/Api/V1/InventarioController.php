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
        $q = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 20), 100);
        $items = DB::select(
            "SELECT TOP (:limit) CODPRO, DESCRIP1, EXISTENCIA, CANRESERVADA,
                    PRECVEN1, IMPPOR, PROCOMPUESTO, TIPINV, UNIDAD
             FROM INVENTARIO
             WHERE INTEGRADO = 0
               AND (CODPRO LIKE :q OR DESCRIP1 LIKE :q2)
             ORDER BY DESCRIP1",
            ['limit' => $perPage, 'q' => "%{$q}%", 'q2' => "%{$q}%"]
        );
        return response()->json(['data' => $items]);
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
