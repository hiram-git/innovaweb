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
     * Lógica idéntica a cadena_control() del legacy (grabar_factura.php):
     *   $hora_actual = (H*360000) + (i*6000) + (s*100) + (v/10) + 1
     *   $aleatorio   = str_pad(floor(microsegundos / 10), 5, '0', STR_PAD_LEFT)
     *
     * @return array{0: int, 1: string, 2: string}  [dias, horaClarion7, aleatorio5]
     */
    public function componentes(): array
    {
        $epoch  = new \DateTime(self::CLARION_EPOCH);
        $ahora  = new \DateTime();
        $diff   = $epoch->diff($ahora);
        $dias   = $diff->days;

        // Hora Clarion: centisegundos del día con +1 (exacto al legacy)
        $hora = (int) date('H') * 360000
              + (int) date('i') * 6000
              + (int) date('s') * 100
              + (int) ((int) date('v') / 10)
              + 1;

        $horaClarion = str_pad((string) $hora, 7, '0', STR_PAD_LEFT);

        // Aleatorio: microsegundos actuales ÷ 10, con padding a 5 dígitos
        $ts        = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $aleatorio = str_pad(
            (string) (int) floor((int) $ts->format('u') / 10),
            5,
            '0',
            STR_PAD_LEFT
        );

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
