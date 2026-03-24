<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /** Resolve the configured sale-price column, e.g. "PRECIO1" */
    private function precioCol(): string
    {
        $emp = DB::selectOne("SELECT TOP 1 ISNULL(PRECIOVENTAD,1) AS PRECIOVENTAD FROM BASEEMPRESA");
        $n   = max(1, (int) ($emp?->PRECIOVENTAD ?? 1));
        return 'PRECIO' . $n;          // PRECIO1 … PRECIO5
    }

    public function index(Request $request): JsonResponse
    {
        $q       = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 20), 100);
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $precioCol = $this->precioCol();   // safe – always "PRECIO{digit}"

        $like = "%{$q}%";
        $binds = [
            'q'  => $like, 'q2' => $like, 'q3' => $like,
            'q4' => $like, 'q5' => $like, 'q6' => $like,
        ];

        $where = "ACTIVO = 0
              AND (  CODPRO  LIKE :q
                  OR CODREF  LIKE :q2
                  OR CODREF2 LIKE :q3
                  OR CODREF3 LIKE :q4
                  OR DESCRIP1 LIKE :q5
                  OR DESCRIP2 LIKE :q6)";

        $count = DB::selectOne(
            "SELECT COUNT(*) AS total FROM INVENTARIO WHERE {$where}",
            $binds
        );
        $total = $count?->total ?? 0;

        $items = DB::select(
            "SELECT CODPRO, DESCRIP1, DESCRIP2,
                    ISNULL(EXISTENCIA,0)    AS EXISTENCIA,
                    ISNULL(CANRESERVADA,0)  AS CANRESERVADA,
                    ISNULL(CANVEN,0)        AS CANVEN,
                    ISNULL({$precioCol},0)  AS PRECVEN1,
                    ISNULL(IMPPOR,0)        AS IMPPOR,
                    ISNULL(PROCOMPUESTO,0)  AS PROCOMPUESTO,
                    TIPINV,
                    ISNULL(EXENTO,0)        AS EXENTO
             FROM INVENTARIO
             WHERE {$where}
             ORDER BY DESCRIP1
             OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY",
            array_merge($binds, ['offset' => $offset, 'limit' => $perPage])
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
        $item = DB::selectOne("SELECT * FROM INVENTARIO WHERE CODPRO = ? AND ACTIVO = 0", [trim($codigo)]);
        if (! $item) return response()->json(['message' => 'Producto no encontrado.'], 404);
        return response()->json(['data' => $item]);
    }

    public function disponibilidad(string $codigo): JsonResponse
    {
        $item = DB::selectOne(
            "SELECT CODPRO, ISNULL(EXISTENCIA,0) AS EXISTENCIA, ISNULL(CANRESERVADA,0) AS CANRESERVADA
             FROM INVENTARIO WHERE CODPRO = ?",
            [trim($codigo)]
        );
        if (! $item) return response()->json(['message' => 'Producto no encontrado.'], 404);
        return response()->json(['data' => ['disponible' => (float)$item->EXISTENCIA - (float)$item->CANRESERVADA]]);
    }
}
