<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CacheHeaders
 *
 * Añade cabeceras HTTP de caché en respuestas GET de endpoints de solo lectura.
 * Permite que los clientes (y proxies privados) cacheen las respuestas sin
 * necesidad de repetir la consulta a la DB en cada petición.
 *
 * Uso en rutas:
 *   Route::get(...)->middleware('cache.headers:60');   // max-age=60 segundos
 *   Route::get(...)->middleware('cache.headers:300');  // max-age=5 minutos
 *
 * Solo actúa en peticiones GET exitosas (2xx). Las rutas autenticadas
 * usan `private` para evitar que proxies intermedios guarden datos personales.
 */
class CacheHeaders
{
    public function handle(Request $request, Closure $next, int $maxAge = 60): Response
    {
        $response = $next($request);

        // Solo cachear GET/HEAD con respuesta 2xx
        if (
            $request->isMethod('GET') || $request->isMethod('HEAD')
            && $response->isSuccessful()
        ) {
            $response->headers->set(
                'Cache-Control',
                "private, max-age={$maxAge}, must-revalidate"
            );

            // ETag basado en el contenido para que el cliente pueda validar
            $etag = '"' . md5((string) $response->getContent()) . '"';
            $response->headers->set('ETag', $etag);

            // Si el cliente envía If-None-Match y el ETag coincide → 304
            $ifNoneMatch = $request->header('If-None-Match');
            if ($ifNoneMatch && trim($ifNoneMatch, '"') === trim($etag, '"')) {
                return response('', 304, [
                    'ETag'          => $etag,
                    'Cache-Control' => "private, max-age={$maxAge}, must-revalidate",
                ]);
            }
        }

        return $response;
    }
}
