<?php

declare(strict_types=1);

namespace App\Services;

/**
 * CadenaControlService — Generador de claves de control formato Clarion
 *
 * El ERP Clarion usa un sistema de claves basado en:
 *   - Días transcurridos desde 1800-12-28 (formato Clarion para fechas)
 *   - Tiempo del día en formato centisegundos Clarion (7 dígitos)
 *   - Número aleatorio de 5 dígitos
 *   - Sufijo de tipo de registro (ej: "01", "02")
 *
 * Formato final: {dias}{hora_clarion}{aleatorio}{sufijo}
 * Ejemplo:       81977123456789012301
 *
 * IMPORTANTE: Esta lógica es idéntica a la función cadena_control() del
 * archivo legacy ajax/guardar_ot.php. Se extrae aquí para reutilizarla
 * en todos los módulos que necesiten generar claves de control.
 */
class CadenaControlService
{
    /** Fecha base del calendario Clarion */
    private const CLARION_EPOCH = '1800-12-28 00:00:00';

    /**
     * Genera una clave de control completa con sufijo
     *
     * @param  string $sufijo  Ej: "01" para maestro, "02" para detalles
     * @return string          Clave de control en formato Clarion
     */
    public function generar(string $sufijo = '01'): string
    {
        [$dias, $horaClarion, $aleatorio] = $this->componentes();
        return "{$dias}{$horaClarion}{$aleatorio}{$sufijo}";
    }

    /**
     * Genera solo los componentes base (sin sufijo)
     * Útil cuando se necesitan múltiples claves con el mismo base
     *
     * @return array{dias: int, hora: string, aleatorio: int}
     */
    public function componentes(): array
    {
        $epoch  = new \DateTime(self::CLARION_EPOCH);
        $ahora  = new \DateTime();
        $diff   = $epoch->diff($ahora);
        $dias   = $diff->days;

        // Hora en formato Clarion: centisegundos del día (7 dígitos)
        $hora   = (int) date('H') * 360000
                + (int) date('i') * 6000
                + (int) date('s') * 100
                + (int) (round(microtime(true) * 1000) % 1000) / 10;

        $horaClarion = str_pad(
            (string) (int) $hora,
            7,
            '0',
            STR_PAD_LEFT
        );

        $aleatorio = mt_rand(10000, 99999);

        return [$dias, $horaClarion, $aleatorio];
    }

    /**
     * Devuelve la fecha actual en formato Clarion (días desde epoch)
     */
    public function fechaClarion(): int
    {
        $epoch = new \DateTime(self::CLARION_EPOCH);
        $ahora = new \DateTime();
        return (int) $epoch->diff($ahora)->days;
    }
}
