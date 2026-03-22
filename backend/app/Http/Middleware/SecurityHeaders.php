<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders — aplica cabeceras de seguridad HTTP en cada respuesta
 *
 * Mitiga: clickjacking, MIME sniffing, XSS reflejado, información de servidor,
 * y fuerza HTTPS en producción.
 *
 * Ref: OWASP Secure Headers Project
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $isApi        = $request->is('api/*');
        $isProduction = app()->environment('production');

        // ─── Siempre ─────────────────────────────────────────────────────────

        // Evitar que el navegador infiera el Content-Type
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Ocultar tecnología del servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->set('X-Powered-By', 'InnovaWeb');

        // Evitar clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Referrer policy — no filtrar paths en navegación
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy — deshabilitar APIs no usadas
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        // ─── Content-Security-Policy ─────────────────────────────────────────

        if (! $isApi) {
            // Para la app React (rutas web / recursos estáticos)
            $nonce = base64_encode(random_bytes(16));
            $request->attributes->set('csp_nonce', $nonce);

            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                "style-src 'self' 'unsafe-inline'",  // Tailwind inline styles
                "img-src 'self' data: blob:",
                "font-src 'self'",
                "connect-src 'self' " . config('app.url'),
                "worker-src 'self' blob:",  // Service Worker
                "manifest-src 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);

            $response->headers->set('Content-Security-Policy', $csp);
        } else {
            // Para la API — CSP restrictivo (no se renderiza HTML)
            $response->headers->set('Content-Security-Policy', "default-src 'none'");
        }

        // ─── Solo producción ─────────────────────────────────────────────────

        if ($isProduction) {
            // HSTS — forzar HTTPS por 1 año, incluir subdominios
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
