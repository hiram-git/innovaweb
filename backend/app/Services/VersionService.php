<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Parsea el campo CTAVENIMP de BASEEMPRESA para obtener el número de versión
 * relevante del ERP Clarion.
 *
 * Regla: se toma el TERCER segmento numérico (índice 2). Si hay menos de tres
 * segmentos se toma el último. Las letras pegadas o sueltas al lado de los
 * números (sufijos de pre-release, letras de revisión, etc.) se ignoran.
 *
 * Ejemplos:
 *   "Ver. 1.97.27.1"       → 27
 *   "Versión 1.97.10 H"    → 10
 *   "Ver. 1.9.21b"         → 21
 *   "Version 1.8.53"       → 53
 *   "Ver. CR 25"           → 25
 *   "Versión 6"            → 6
 *   "V. 1.97.22.3.8-alpha" → 22
 */
class VersionService
{
    public static function parse(?string $versionString): ?int
    {
        if ($versionString === null || trim($versionString) === '') {
            return null;
        }

        // Extraer todos los grupos de dígitos consecutivos, ignorando letras y símbolos
        preg_match_all('/\d+/', $versionString, $matches);
        $parts = $matches[0];

        if (empty($parts)) {
            return null;
        }

        // Tercer segmento (índice 2) si existe; si no, el último disponible
        return (int) (count($parts) > 2 ? $parts[2] : end($parts));
    }
}
