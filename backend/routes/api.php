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
use App\Http\Controllers\Api\V1\InstrumentosController;
use App\Http\Controllers\Api\V1\ConfiguracionController;
use App\Http\Controllers\Api\V1\DashboardController;

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
        ->middleware('throttle:10,1')
        ->name('api.login');

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

    // ── Dashboard ────────────────────────────────────────────────────────────
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // ── Instrumentos de pago ─────────────────────────────────────────────────
    Route::get('instrumentos', [InstrumentosController::class, 'index'])->name('instrumentos.index');

    // ── Clientes ─────────────────────────────────────────────────────────────
    Route::apiResource('clientes', ClienteController::class);
    Route::get('clientes/buscar/ruc/{ruc}', [ClienteController::class, 'buscarPorRuc'])
        ->name('clientes.buscar-ruc');

    // ── Inventario ───────────────────────────────────────────────────────────
    Route::apiResource('inventario', InventarioController::class)->only(['index', 'show']);
    Route::get('inventario/{codigo}/disponibilidad', [InventarioController::class, 'disponibilidad'])
        ->name('inventario.disponibilidad');

    // ── Facturas ─────────────────────────────────────────────────────────────
    Route::apiResource('facturas', FacturaController::class);
    Route::get('facturas/{id}/pdf',    [FacturaController::class, 'pdf'])->name('facturas.pdf');
    Route::get('facturas/{id}/ticket', [FacturaController::class, 'ticket'])->name('facturas.ticket');

    // ── Facturación Electrónica (DGI Panamá) ─────────────────────────────────
    // Rutas con prefijo /facturacion-electronica (alineadas con el frontend React)
    Route::prefix('facturacion-electronica')->name('fe.')->group(function () {
        Route::get('/stats',                     [FEController::class, 'stats'])->name('stats');
        Route::get('/documentos',                [FEController::class, 'documentos'])->name('documentos');
        Route::get('/config',                    [FEController::class, 'getConfig'])->name('config.get');
        Route::put('/config',                    [FEController::class, 'updateConfig'])->name('config.update');
        Route::post('/enviar/{facturaId}',        [FEController::class, 'enviar'])->name('enviar');
        Route::post('/reenviar/{facturaId}',      [FEController::class, 'reenviar'])->name('reenviar');
        Route::post('/nota-credito/{facturaId}',  [FEController::class, 'notaCredito'])->name('nota-credito');
        Route::post('/nota-debito/{facturaId}',   [FEController::class, 'notaDebito'])->name('nota-debito');
        Route::get('/estado/{cufe}',              [FEController::class, 'consultarEstado'])->name('estado');
        Route::get('/pdf/{cufe}',                 [FEController::class, 'descargarPDF'])->name('pdf');
    });

    // ── Órdenes de Trabajo ───────────────────────────────────────────────────
    Route::apiResource('ordenes-trabajo', OrdenTrabajoController::class);

    // ── Cobros ───────────────────────────────────────────────────────────────
    Route::apiResource('cobros', CobroController::class);

    // ── Presupuestos / Cotizaciones ──────────────────────────────────────────
    Route::apiResource('presupuestos', PresupuestoController::class);
    // Nota: ruta alineada con el frontend (/convertir-a-factura)
    Route::post('presupuestos/{id}/convertir-a-factura', [PresupuestoController::class, 'convertirAFactura'])
        ->name('presupuestos.convertir');

    // ── Configuración ────────────────────────────────────────────────────────
    Route::get('configuracion/empresa',  [ConfiguracionController::class, 'getEmpresa'])->name('config.empresa.get');
    Route::put('configuracion/empresa',  [ConfiguracionController::class, 'updateEmpresa'])->name('config.empresa.update');
    Route::get('configuracion/fe',       [ConfiguracionController::class, 'getFE'])->name('config.fe.get');
    Route::put('configuracion/fe',       [ConfiguracionController::class, 'updateFE'])->name('config.fe.update');
});
