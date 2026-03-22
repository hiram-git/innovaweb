<?php

declare(strict_types=1);

namespace App\Services\FE;

/**
 * Contrato común para todos los Proveedores Autorizados de Certificación (PAC)
 *
 * Todos los PACs (TFHKA, Digifact, EBI) deben implementar esta interface.
 * Esto permite intercambiar el PAC sin modificar el código del FEController.
 */
interface PACServiceInterface
{
    /**
     * Enviar una Factura (tipo 01) a la DGI
     *
     * @param  string $control  TRANSACCMAESTRO.CONTROL
     * @param  object $config   Fila de FELINNOVA con la configuración del PAC
     * @return array{estado: int, mensaje: string, cufe: string|null, qr: string|null,
     *               codigo: int|null, resultado: string|null,
     *               fechaRecepcionDGI: string|null, nroProtocoloAutorizacion: string|null,
     *               fechaLimite: string|null}
     */
    public function enviarFactura(string $control, object $config): array;

    /**
     * Enviar una Nota de Crédito (tipo 04)
     */
    public function enviarNotaCredito(string $control, object $config): array;

    /**
     * Enviar una Nota de Débito (tipo 05)
     */
    public function enviarNotaDebito(string $control, object $config): array;

    /**
     * Descargar el PDF del documento desde el PAC (base64)
     *
     * @return string  PDF en base64
     */
    public function descargarPDF(string $cufe, object $config): string;

    /**
     * Consultar el estado de un documento en el PAC por CUFE
     */
    public function consultarEstado(string $cufe, object $config): array;
}
