<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PresupuestoController — Implementación pendiente (Fase 2 del plan de migración)
 */
class PresupuestoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Módulo en migración. Use el sistema legacy hasta completar la Fase 2.'], 501);
    }
    public function show(string $id): JsonResponse { return $this->index(request()); }
    public function store(Request $request): JsonResponse { return $this->index($request); }
    public function update(Request $request, string $id): JsonResponse { return $this->index($request); }
    public function destroy(string $id): JsonResponse { return $this->index(request()); }
}
