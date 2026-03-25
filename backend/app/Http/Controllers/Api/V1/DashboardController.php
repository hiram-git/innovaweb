<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController
 *
 * Provee KPIs consolidados para la pantalla principal de InnovaWeb.
 * Todas las consultas son de solo lectura sobre tablas del ERP Clarion.
 */
class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        // Facturas emitidas hoy (no integradas al contable)
        $facturasHoy = (int) (DB::selectOne(
            "SELECT COUNT(*) AS total
             FROM TRANSACCMAESTRO
             WHERE TIPTRAN = 'FAC'
               AND FECEMIS = CONVERT(INT, CONVERT(VARCHAR(8), GETDATE(), 112))"
        )?->total ?? 0);

        // Total en saldo pendiente de cobro (crédito)
        $totalCobrar = (float) (DB::selectOne(
            "SELECT ISNULL(SUM(MONTOSAL), 0) AS total
             FROM TRANSACCMAESTRO
             WHERE TIPTRAN = 'FAC'
               AND MONTOSAL > 0"
        )?->total ?? 0);

        // Documentos FE aceptados en el mes actual
        $feAceptadasMes = (int) (DB::selectOne(
            "SELECT COUNT(*) AS total
             FROM Documentos
             WHERE RESULTADO = 'ACEPTADO'
               AND MONTH(FECHARECEPCIONDGI) = MONTH(GETDATE())
               AND YEAR(FECHARECEPCIONDGI)  = YEAR(GETDATE())"
        )?->total ?? 0);

        // Clientes con al menos una factura en los últimos 90 días
        $clientesActivos = (int) (DB::selectOne(
            "SELECT COUNT(DISTINCT CODIGO) AS total
             FROM TRANSACCMAESTRO
             WHERE TIPTRAN = 'FAC'
               AND FECEMIS >= CONVERT(INT, CONVERT(VARCHAR(8), DATEADD(day, -90, GETDATE()), 112))"
        )?->total ?? 0);

        // Top 10 clientes con más facturas
        $topClientes = DB::select(
            "SELECT TOP (10)
                m.CODIGO,
                m.NOMBRE,
                COUNT(*)          AS total_facturas,
                SUM(m.MONTOTOT)   AS monto_total
             FROM TRANSACCMAESTRO m
             WHERE m.TIPTRAN = 'FAC'
             GROUP BY m.CODIGO, m.NOMBRE
             ORDER BY total_facturas DESC"
        );

        return response()->json([
            'kpis' => [
                'facturas_hoy'     => $facturasHoy,
                'total_cobrar'     => round($totalCobrar, 2),
                'fe_aceptadas_mes' => $feAceptadasMes,
                'clientes_activos' => $clientesActivos,
            ],
            'top_clientes' => $topClientes,
        ]);
    }
}
