<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\InventarioController;
use App\Http\Controllers\Api\V1\FacturaController;
use App\Http\Controllers\Api\V1\FEController;
use App\Http\Controllers\Api\V1\OrdenTrabajoController;
use App\Http\Controllers\Api\V1\CobroController;
use App\Http\Controllers\Api\V1\PresupuestoController;

/*
|--------------------------------------------------------------------------
| InnovaWeb API v1
|--------------------------------------------------------------------------
| Base URL: http://innovaweb-api.test/api/v1/
|
| Autenticación: Bearer token (Laravel Sanctum)
| Todas las rutas protegidas requieren: Authorization: Bearer {token}
*/

// ─── Rutas públicas (sin autenticación) ──────────────────────────────────────
Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')  // máx 10 intentos por minuto por IP
        ->name('api.login');

    // Health check
    Route::get('/ping', fn () => response()->json([
        'status'  => 'ok',
        'app'     => config('app.name'),
        'version' => '1.0.0',
        'time'    => now()->toIso8601String(),
    ]))->name('api.ping');
});

// ─── Rutas protegidas (requieren token Sanctum) ───────────────────────────────
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me',      [AuthController::class, 'me'])->name('api.me');

    // ── Clientes ─────────────────────────────────────────────────────────────
    Route::apiResource('clientes', ClienteController::class);
    Route::get('clientes/buscar/ruc/{ruc}', [ClienteController::class, 'buscarPorRuc'])
        ->name('clientes.buscar-ruc');

    // ── Inventario ───────────────────────────────────────────────────────────
    Route::apiResource('inventario', InventarioController::class)->only([
        'index', 'show',
    ]);
    Route::get('inventario/{codigo}/disponibilidad', [InventarioController::class, 'disponibilidad'])
        ->name('inventario.disponibilidad');

    // ── Facturas ─────────────────────────────────────────────────────────────
    Route::apiResource('facturas', FacturaController::class);
    Route::get('facturas/{id}/pdf',    [FacturaController::class, 'pdf'])->name('facturas.pdf');
    Route::get('facturas/{id}/ticket', [FacturaController::class, 'ticket'])->name('facturas.ticket');

    // ── Facturación Electrónica (DGI Panamá) ─────────────────────────────────
    Route::prefix('fe')->name('fe.')->group(function () {
        Route::get('/config',                   [FEController::class, 'getConfig'])->name('config.get');
        Route::put('/config',                   [FEController::class, 'updateConfig'])->name('config.update');
        Route::post('/enviar/{facturaId}',       [FEController::class, 'enviar'])->name('enviar');
        Route::post('/nota-credito/{facturaId}', [FEController::class, 'notaCredito'])->name('nota-credito');
        Route::post('/nota-debito/{facturaId}',  [FEController::class, 'notaDebito'])->name('nota-debito');
        Route::get('/estado/{cufe}',             [FEController::class, 'consultarEstado'])->name('estado');
        Route::get('/pdf/{cufe}',                [FEController::class, 'descargarPDF'])->name('pdf');
        Route::post('/reenviar/{facturaId}',     [FEController::class, 'reenviar'])->name('reenviar');
    });

    // ── Órdenes de Trabajo ───────────────────────────────────────────────────
    Route::apiResource('ordenes-trabajo', OrdenTrabajoController::class);

    // ── Cobros ───────────────────────────────────────────────────────────────
    Route::apiResource('cobros', CobroController::class);

    // ── Presupuestos / Cotizaciones ──────────────────────────────────────────
    Route::apiResource('presupuestos', PresupuestoController::class);
    Route::post('presupuestos/{id}/convertir-factura', [PresupuestoController::class, 'convertirAFactura'])
        ->name('presupuestos.convertir');
});
