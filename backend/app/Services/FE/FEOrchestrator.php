<?php

declare(strict_types=1);

namespace App\Services\FE;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * FEOrchestrator — Orquestador de Facturación Electrónica
 *
 * Selecciona el PAC (Proveedor Autorizado de Certificación) correcto
 * según la configuración en FELINNOVA y delega el envío.
 *
 * PACs soportados:
 *  - The Factory HKA (SOAP/WSDL)
 *  - Digifact (REST)
 *  - EBI (REST)
 */
class FEOrchestrator
{
    /**
     * Enviar una factura a la DGI vía el PAC configurado
     *
     * @param  string $control  Número de control de la factura (TRANSACCMAESTRO.CONTROL)
     * @return array{estado: int, mensaje: string, cufe: string|null, qr: string|null}
     */
    public function enviarFactura(string $control): array
    {
        $config = $this->getConfig();

        if (! $config || $config->FACELECT === '0') {
            return [
                'estado'  => 0,
                'mensaje' => 'La Facturación Electrónica no está habilitada.',
                'cufe'    => null,
                'qr'      => null,
            ];
        }

        $pac = $this->resolvePAC($config);

        Log::info('FE: Enviando factura', [
            'control' => $control,
            'pac'     => get_class($pac),
        ]);

        try {
            $resultado = $pac->enviarFactura($control, $config);

            if ($resultado['estado'] === 1) {
                $this->persistirRespuesta($control, $resultado);
                Log::info('FE: Factura enviada exitosamente', [
                    'control' => $control,
                    'cufe'    => $resultado['cufe'],
                ]);
            } else {
                Log::warning('FE: Factura rechazada por la DGI', [
                    'control' => $control,
                    'mensaje' => $resultado['mensaje'],
                ]);
            }

            return $resultado;
        } catch (\Throwable $e) {
            Log::error('FE: Error al enviar factura', [
                'control'   => $control,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Enviar Nota de Crédito
     */
    public function enviarNotaCredito(string $control): array
    {
        $config = $this->getConfig();
        $pac    = $this->resolvePAC($config);

        return $pac->enviarNotaCredito($control, $config);
    }

    /**
     * Enviar Nota de Débito
     */
    public function enviarNotaDebito(string $control): array
    {
        $config = $this->getConfig();
        $pac    = $this->resolvePAC($config);

        return $pac->enviarNotaDebito($control, $config);
    }

    /**
     * Obtener configuración FE de la base de datos
     */
    private function getConfig(): ?object
    {
        return DB::selectOne(
            "SELECT A.*, B.NROINIFAC
             FROM FELINNOVA A
             LEFT JOIN BASEEMPRESA B ON B.CONTROL = A.PARCONTROL
             WHERE A.PARCONTROL = 1"
        );
    }

    /**
     * Seleccionar el servicio PAC según la URL de envío configurada
     * (La URL determina si es TFHKA, Digifact o EBI)
     */
    private function resolvePAC(object $config): PACServiceInterface
    {
        $url = strtolower($config->DIRECCIONENVIO ?? '');

        if (str_contains($url, 'epak.com.pa') || str_contains($url, 'tfhka')) {
            return new ThfkapanamaService();
        }

        if (str_contains($url, 'digifact')) {
            return new DigifactService();
        }

        // Por defecto usar TFHKA (el principal del sistema)
        return new ThfkapanamaService();
    }

    /**
     * Persistir la respuesta de la DGI en la tabla Documentos
     */
    private function persistirRespuesta(string $control, array $resultado): void
    {
        DB::statement(
            "MERGE INTO Documentos AS target
             USING (SELECT :control AS CONTROL) AS source
             ON (target.CONTROL = source.CONTROL)
             WHEN MATCHED THEN
                UPDATE SET
                    CODIGO = :codigo, RESULTADO = :resultado,
                    MENSAJE = :mensaje, CUFE = :cufe, QR = :qr,
                    FECHARECEPCIONDGI = :fechaRecepcion,
                    NROPROTOCOLOAYTORIZACION = :protocolo,
                    FECHALIMITE = :fechaLimite
             WHEN NOT MATCHED THEN
                INSERT (CONTROL, CODIGO, RESULTADO, MENSAJE, CUFE, QR,
                        FECHARECEPCIONDGI, NROPROTOCOLOAYTORIZACION, FECHALIMITE)
                VALUES (:control2, :codigo2, :resultado2, :mensaje2, :cufe2, :qr2,
                        :fechaRecepcion2, :protocolo2, :fechaLimite2);",
            [
                'control'         => $control,
                'codigo'          => $resultado['codigo'] ?? 200,
                'resultado'       => $resultado['resultado'] ?? '1',
                'mensaje'         => $resultado['mensaje'] ?? '',
                'cufe'            => $resultado['cufe'] ?? '',
                'qr'              => $resultado['qr'] ?? '',
                'fechaRecepcion'  => $resultado['fechaRecepcionDGI'] ?? null,
                'protocolo'       => $resultado['nroProtocoloAutorizacion'] ?? '',
                'fechaLimite'     => $resultado['fechaLimite'] ?? null,
                'control2'        => $control,
                'codigo2'         => $resultado['codigo'] ?? 200,
                'resultado2'      => $resultado['resultado'] ?? '1',
                'mensaje2'        => $resultado['mensaje'] ?? '',
                'cufe2'           => $resultado['cufe'] ?? '',
                'qr2'             => $resultado['qr'] ?? '',
                'fechaRecepcion2' => $resultado['fechaRecepcionDGI'] ?? null,
                'protocolo2'      => $resultado['nroProtocoloAutorizacion'] ?? '',
                'fechaLimite2'    => $resultado['fechaLimite'] ?? null,
            ]
        );

        // Actualizar la factura maestra con el CUFE
        if (! empty($resultado['cufe'])) {
            DB::statement(
                "UPDATE TRANSACCMAESTRO
                 SET COM_FISCAL = :cufe, URLCONSULTAFEL = :qr
                 WHERE CONTROL = :control",
                [
                    'cufe'    => $resultado['cufe'],
                    'qr'      => $resultado['qr'] ?? '',
                    'control' => $control,
                ]
            );
        }
    }
}
