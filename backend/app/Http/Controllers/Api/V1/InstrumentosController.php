<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * InstrumentosController
 *
 * Devuelve los instrumentos de pago configurados en BASEINSTRUMENTOS.
 * Usado por los módulos de Facturas y Cobros para poblar selectores.
 *
 * FUNCION: 0=tarjeta, 1=cheque, 2=transferencia, 3=depósito, 6=efectivo
 */
class InstrumentosController extends Controller
{
    public function index(): JsonResponse
    {
        $instrumentos = DB::select(
            "SELECT CODTAR AS CODINSTRUMENTO, NOMBRE AS DESCRINSTRUMENTO, FUNCION
             FROM BASEINSTRUMENTOS
             ORDER BY FUNCION ASC, NOMBRE ASC"
        );

        return response()->json($instrumentos);
    }
}
