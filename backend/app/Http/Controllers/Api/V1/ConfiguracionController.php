<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ConfiguracionController
 *
 * Gestiona la configuración de la empresa y de Facturación Electrónica DGI.
 * Lee/escribe en BASEEMPRESA y FELINNOVA del ERP Clarion.
 */
class ConfiguracionController extends Controller
{
    // ─── Empresa ──────────────────────────────────────────────────────────────

    public function getEmpresa(): JsonResponse
    {
        $empresa = DB::selectOne(
            "SELECT RAZONSOCIAL, RUC, DV, DIRECC1 AS DIRECCION, TEL1 AS TEL,
                EMAIL, LOGO_URL
             FROM BASEEMPRESA WHERE CONTROL = 1"
        );

        if (! $empresa) {
            return response()->json(['message' => 'Configuración de empresa no encontrada.'], 404);
        }

        return response()->json($empresa);
    }

    public function updateEmpresa(Request $request): JsonResponse
    {
        $data = $request->validate([
            'RAZONSOCIAL' => ['required', 'string', 'max:150'],
            'RUC'         => ['required', 'string', 'max:20'],
            'DV'          => ['required', 'string', 'max:3'],
            'DIRECCION'   => ['nullable', 'string', 'max:200'],
            'TEL'         => ['nullable', 'string', 'max:20'],
            'EMAIL'       => ['nullable', 'email', 'max:100'],
            'LOGO_URL'    => ['nullable', 'url', 'max:500'],
        ]);

        DB::statement(
            "UPDATE BASEEMPRESA SET
                RAZONSOCIAL = :rs, RUC = :ruc, DV = :dv,
                DIRECC1 = :dir, TEL1 = :tel, EMAIL = :email, LOGO_URL = :logo
             WHERE CONTROL = 1",
            [
                'rs'    => $data['RAZONSOCIAL'],
                'ruc'   => $data['RUC'],
                'dv'    => $data['DV'],
                'dir'   => $data['DIRECCION'] ?? '',
                'tel'   => $data['TEL'] ?? '',
                'email' => $data['EMAIL'] ?? '',
                'logo'  => $data['LOGO_URL'] ?? '',
            ]
        );

        return response()->json(['message' => 'Datos de empresa actualizados.']);
    }

    // ─── FE / DGI ─────────────────────────────────────────────────────────────

    public function getFE(): JsonResponse
    {
        $config = DB::selectOne(
            "SELECT
                CASE WHEN FACELECT = '1' THEN 'produccion' ELSE 'sandbox' END AS ambiente,
                CASE WHEN NEMPRESA_DIGI IS NOT NULL THEN 'DIGIFACT' ELSE 'TFHKA' END AS pac,
                RUC_DIGI AS ruc_emisor,
                DV_DIGI AS dv_emisor
             FROM FELINNOVA WHERE PARCONTROL = 1"
        );

        if (! $config) {
            // Retornar defaults si no existe configuración
            return response()->json([
                'ambiente'   => 'sandbox',
                'pac'        => 'TFHKA',
                'ruc_emisor' => '',
                'dv_emisor'  => '',
            ]);
        }

        return response()->json($config);
    }

    public function updateFE(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ambiente'   => ['required', 'in:sandbox,produccion'],
            'pac'        => ['required', 'in:TFHKA,DIGIFACT'],
            'ruc_emisor' => ['required', 'string', 'max:20'],
            'dv_emisor'  => ['required', 'string', 'max:2'],
        ]);

        $facelect = $data['ambiente'] === 'produccion' ? '1' : '0';

        // MERGE para crear o actualizar
        DB::statement(
            "MERGE INTO FELINNOVA AS t USING (SELECT 1 AS PARCONTROL) AS s ON (t.PARCONTROL = s.PARCONTROL)
             WHEN MATCHED THEN
                UPDATE SET FACELECT = :fe, RUC_DIGI = :ruc, DV_DIGI = :dv
             WHEN NOT MATCHED THEN
                INSERT (PARCONTROL, FACELECT, RUC_DIGI, DV_DIGI, TIPOEMISION, TIPOSUCURSAL,
                        NATURALEZAOPERACION, TIPOOPERACION, FORMATOCAFE, ENTREGACAFE, DIRECCIONENVIO)
                VALUES (1, :fe2, :ruc2, :dv2, '01', '01', '01', '01', '03', '01', '01');",
            [
                'fe'   => $facelect, 'ruc'  => $data['ruc_emisor'], 'dv'   => $data['dv_emisor'],
                'fe2'  => $facelect, 'ruc2' => $data['ruc_emisor'], 'dv2'  => $data['dv_emisor'],
            ]
        );

        return response()->json(['message' => 'Configuración FE actualizada.']);
    }
}
