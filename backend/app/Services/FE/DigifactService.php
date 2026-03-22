<?php

declare(strict_types=1);

namespace App\Services\FE;

/**
 * DigifactService — Integración con Digifact
 *
 * Protocolo: REST/HTTP
 *
 * NOTE: Implementación pendiente — actualmente el sistema usa TFHKA como primario.
 * Esta clase es el stub que permite registrar Digifact como PAC alternativo.
 */
class DigifactService implements PACServiceInterface
{
    public function enviarFactura(string $control, object $config): array
    {
        throw new \RuntimeException('Digifact: implementación pendiente. Usar TFHKA como PAC primario.');
    }

    public function enviarNotaCredito(string $control, object $config): array
    {
        throw new \RuntimeException('Digifact: implementación pendiente.');
    }

    public function enviarNotaDebito(string $control, object $config): array
    {
        throw new \RuntimeException('Digifact: implementación pendiente.');
    }

    public function descargarPDF(string $cufe, object $config): string
    {
        throw new \RuntimeException('Digifact: implementación pendiente.');
    }

    public function consultarEstado(string $cufe, object $config): array
    {
        throw new \RuntimeException('Digifact: implementación pendiente.');
    }
}
