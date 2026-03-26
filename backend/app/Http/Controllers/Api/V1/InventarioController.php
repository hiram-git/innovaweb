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

        $where = "inv.ACTIVO = 0
              AND (  inv.CODPRO  LIKE :q
                  OR inv.CODREF  LIKE :q2
                  OR inv.CODREF2 LIKE :q3
                  OR inv.CODREF3 LIKE :q4
                  OR inv.DESCRIP1 LIKE :q5
                  OR inv.DESCRIP2 LIKE :q6)";

        $count = DB::selectOne(
            "SELECT COUNT(*) AS total FROM INVENTARIO inv WHERE {$where}",
            $binds
        );
        $total = $count?->total ?? 0;

        $items = DB::select(
            "SELECT inv.CODPRO, inv.DESCRIP1, inv.DESCRIP2,
                    ISNULL(inv.EXISTENCIA,0)    AS EXISTENCIA,
                    ISNULL(inv.CANRESERVADA,0)  AS CANRESERVADA,
                    ISNULL(inv.CANVEN,0)        AS CANVEN,
                    ISNULL(inv.{$precioCol},0)  AS PRECVEN1,
                    ISNULL(inv.IMPPOR,0)        AS IMPPOR,
                    ISNULL(inv.PROCOMPUESTO,0)  AS PROCOMPUESTO,
                    inv.TIPINV,
                    ISNULL(inv.EXENTO,0)        AS EXENTO,
                    ISNULL(emp.CANTIDAD_EMP,0)  AS CANTIDAD_EMP,
                    ISNULL(emp.PRECIO_EMPAQUE,0) AS PRECIO_EMPAQUE
             FROM INVENTARIO inv
             LEFT JOIN INVENTARIOEMPAQUESV emp ON emp.CODPRO = inv.CODPRO
             WHERE {$where}
             ORDER BY inv.DESCRIP1
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

    /**
     * Return components of a composite product (PROCOMPUESTO=1).
     * Mirrors ajax/mostrarComponentes.php.
     */
    public function componentes(string $codigo): JsonResponse
    {
        $precioCol = $this->precioCol();

        $rows = DB::select(
            "SELECT ic.CODPRO_COMP                                         AS CODPRO,
                    ISNULL(inv.DESCRIP1, ic.CODPRO_COMP)                   AS DESCRIP1,
                    ISNULL(ic.CANTIDAD, 1)                                 AS CANTIDAD,
                    ISNULL(inv.{$precioCol}, 0)                            AS PRECVEN1,
                    ISNULL(inv.EXISTENCIA,0)
                        - ISNULL(inv.CANRESERVADA,0)
                        - ISNULL(inv.CANVEN,0)                             AS disponible,
                    ISNULL(inv.TIPINV,'')                                  AS TIPINV,
                    ISNULL(inv.IMPPOR,0)                                   AS IMPPOR,
                    ISNULL(inv.EXENTO,0)                                   AS EXENTO
             FROM INVENTARIOCOMPONENTES ic
             LEFT JOIN INVENTARIO inv ON inv.CODPRO = ic.CODPRO_COMP
             WHERE ic.CODPRO = ?
             ORDER BY ic.CODPRO_COMP",
            [trim($codigo)]
        );

        return response()->json(['data' => $rows]);
    }
}
