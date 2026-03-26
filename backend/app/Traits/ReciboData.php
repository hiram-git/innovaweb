<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Shared data-fetching logic for document receipts (recibo).
 * Used by FacturaController, PresupuestoController, PedidoController.
 */
trait ReciboData
{
    /**
     * Build the full receipt payload for a TRANSACCMAESTRO document.
     * Returns null if the control does not exist.
     */
    protected function buildRecibo(string $control): ?array
    {
        $maestro = DB::selectOne(
            "SELECT CONTROL, TIPTRAN, NUMREF, CODIGO, NOMBRE, CODVEN,
                    FECEMIS, FECEMISS,
                    ISNULL(TIPOFACTURA,'')   AS TIPOFACTURA,
                    ISNULL(MONTOBRU,0)       AS MONTOBRU,
                    ISNULL(MONTOSUB,0)       AS MONTOSUB,
                    ISNULL(MONTODES,0)       AS MONTODES,
                    ISNULL(MONTOIMP,0)       AS MONTOIMP,
                    ISNULL(MONTOTOT,0)       AS MONTOTOT,
                    ISNULL(CAMBIO,0)         AS CAMBIO
             FROM TRANSACCMAESTRO WHERE CONTROL = ?",
            [$control]
        );

        if (! $maestro) {
            return null;
        }

        $empresa = DB::selectOne(
            "SELECT TOP 1
                    ISNULL(NOMBRE,'')    AS NOMBRE,
                    ISNULL(NUMFISCAL,'') AS NUMFISCAL,
                    ISNULL(DIRECC1,'')   AS DIRECC1,
                    ISNULL(DIRECC2,'')   AS DIRECC2,
                    ISNULL(NUMTEL,'')    AS NUMTEL
             FROM BASEEMPRESA"
        );

        $cliente = DB::selectOne(
            "SELECT ISNULL(NOMBRE,'')    AS NOMBRE,
                    ISNULL(RIF,'')       AS RIF,
                    ISNULL(DIRECC1,'')   AS DIRECC1,
                    ISNULL(DIRCORREO,'') AS DIRCORREO
             FROM BASECLIENTESPROVEEDORES
             WHERE CODIGO = ? AND TIPREG = '1'",
            [$maestro->CODIGO ?? '']
        );

        $vendedor = DB::selectOne(
            "SELECT ISNULL(NOMBRE,'') AS NOMBRE
             FROM BASECLIENTESPROVEEDORES
             WHERE CODIGO = ? AND TIPREG = '2'",
            [$maestro->CODVEN ?? '']
        );

        $detalles = DB::select(
            "SELECT CODPRO, DESCRIP1, CANTIDAD, PRECOSUNI,
                    ISNULL(MONTODESCUENTO,0) AS MONTODESCUENTO,
                    ISNULL(PORDES,0)         AS PORDES,
                    ISNULL(IMPPOR,0)         AS IMPPOR,
                    ISNULL(MONTOIMP,0)       AS MONTOIMP,
                    ISNULL(TOTAL,0)          AS TOTAL
             FROM TRANSACCDETALLES
             WHERE CONTROL = ? AND COMPONENTE = 0
             ORDER BY FECHORA",
            [$control]
        );

        $pagos = DB::select(
            "SELECT p.CODTAR,
                    ISNULL(b.DESCRINSTRUMENTO, p.CODTAR) AS DESCRINSTRUMENTO,
                    ISNULL(p.MONTOPAG,0) AS MONTOPAG
             FROM TRANSACCPAGOS p
             LEFT JOIN BASEINSTRUMENTOS b ON b.CODINSTRUMENTO = p.CODTAR
             WHERE p.CONTROL = ?",
            [$control]
        );

        // Documentos (FE) — may not exist for this document
        $doc = DB::selectOne(
            "SELECT ISNULL(CUFE,'')                       AS CUFE,
                    ISNULL(QR,'')                         AS QR,
                    ISNULL(RESULTADO,'')                  AS RESULTADO,
                    ISNULL(NROPROTOCOLOAYTORIZACION,'')   AS NROPROTOCOLOAYTORIZACION,
                    FECHARECEPCIONDGI,
                    ISNULL(NUMDOCFISCAL,'')               AS NUMDOCFISCAL,
                    CASE WHEN PDF IS NOT NULL THEN 1 ELSE 0 END AS tiene_pdf
             FROM Documentos WHERE CONTROL = ?",
            [$control]
        );

        // FELINNOVA print settings — table may not exist on first run
        try {
            $fel = DB::selectOne(
                "SELECT ISNULL(FACELECT,'0')       AS FACELECT,
                        ISNULL(TIPO_FACTURA,'PDF') AS TIPO_FACTURA
                 FROM FELINNOVA WHERE PARCONTROL = 1"
            );
        } catch (\Throwable) {
            $fel = null;
        }

        return [
            'empresa'   => $empresa,
            'maestro'   => $maestro,
            'cliente'   => $cliente,
            'vendedor'  => $vendedor,
            'detalles'  => $detalles,
            'pagos'     => $pagos,
            'documento' => $doc,
            'config'    => [
                'facelect'     => ($fel?->FACELECT ?? '0') === '1',
                'tipo_factura' => $fel?->TIPO_FACTURA ?? 'PDF',
            ],
        ];
    }
}
