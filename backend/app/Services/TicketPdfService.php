<?php

declare(strict_types=1);

namespace App\Services;

// TCPDF se carga desde la librería compartida del proyecto legacy
require_once base_path('../../library/vendor/autoload.php');

use TCPDF;

/**
 * TicketPdfService — Genera tickets térmicos en PDF usando TCPDF.
 *
 * Replica fielmente la lógica de /ticket/ticket.php, crearPresupuesto.php
 * y crearPedido.php del código legacy, adaptada para recibir los datos
 * ya hidratados por ReciboData::buildRecibo().
 *
 * Ancho fijo: 82 mm (papel térmico estándar).
 * Alto:       calculado dinámicamente según la cantidad de ítems y si hay QR.
 */
class TicketPdfService
{
    private const ANCHO_MM = 82;

    /**
     * Genera el PDF del ticket y devuelve el contenido binario.
     *
     * @param  array $data  Resultado de ReciboData::buildRecibo()
     * @return string       Contenido binario del PDF
     */
    public function generar(array $data): string
    {
        $maestro  = $data['maestro'];
        $empresa  = $data['empresa'];
        $cliente  = $data['cliente'];
        $vendedor = $data['vendedor'];
        $detalles = $data['detalles'];
        $pagos    = $data['pagos'];
        $doc      = $data['documento'];

        $tiptran = $maestro->TIPTRAN ?? 'FAC';

        // ── Empresa ────────────────────────────────────────────────────────────
        $nombreEmpresa = $empresa->NOMBRE     ?? '';
        $rucEmpresa    = $empresa->NUMFISCAL  ?? '';
        $dirEmpresa    = trim(($empresa->DIRECC1 ?? '') . ' ' . ($empresa->DIRECC2 ?? ''));
        $telEmpresa    = $empresa->NUMTEL     ?? '';

        // ── Cliente ────────────────────────────────────────────────────────────
        $nombreCliente = !empty($cliente->NOMBRE)  ? $cliente->NOMBRE  : '-----';
        $rucCliente    = !empty($cliente->RIF)     ? $cliente->RIF     : '-----';
        $dirCliente    = !empty($cliente->DIRECC1) ? $cliente->DIRECC1 : '-----';
        $nombreVend    = !empty($vendedor->NOMBRE) ? $vendedor->NOMBRE : '-----';

        // ── Documento ──────────────────────────────────────────────────────────
        $codigoQr      = !empty($doc?->QR)                        ? $doc->QR                        : '';
        $nroAutorizac  = !empty($doc?->NROPROTOCOLOAYTORIZACION)  ? $doc->NROPROTOCOLOAYTORIZACION  : '';
        $fechaRecepDgi = !empty($doc?->FECHARECEPCIONDGI)         ? $doc->FECHARECEPCIONDGI          : '';

        // ── Maestro ────────────────────────────────────────────────────────────
        $numref    = trim($maestro->NUMREF    ?? '');
        $fecemiss  = $maestro->FECEMISS       ?? '';
        $montosub  = (float) ($maestro->MONTOSUB ?? $maestro->MONTOBRU ?? 0);
        $montodes  = (float) ($maestro->MONTODES  ?? 0);
        $montoimp  = (float) ($maestro->MONTOIMP  ?? 0);
        $montotot  = (float) ($maestro->MONTOTOT  ?? 0);
        $cambio    = (float) ($maestro->CAMBIO     ?? 0);

        // Fecha formateada dd/mm/YYYY
        $fechaFmt = '';
        if (strlen($fecemiss) === 8) {
            $fechaFmt = substr($fecemiss, 6, 2) . '/' . substr($fecemiss, 4, 2) . '/' . substr($fecemiss, 0, 4);
        }

        // ── Título según tipo ──────────────────────────────────────────────────
        [$titulo, $labelNro] = match (true) {
            $tiptran === 'PRE'     => ["PRESUPUESTO",                    "Nro. Presupuesto:"],
            $tiptran === 'PEDxCLI' => ["PEDIDO",                         "Nro. Pedido:"],
            default                => ["Comprobante Auxiliar de Factura\nElectrónica", "Factura:"],
        };

        $ancho = 50; // ancho de texto para truncar

        // ── SECCIÓN 1: cabecera empresa ────────────────────────────────────────
        $s1  = "{$titulo}\n\n";
        $s1 .= "{$nombreEmpresa}\n";
        $s1 .= "RUC: {$rucEmpresa}\n";
        $s1 .= "{$dirEmpresa}\n";
        $s1 .= "{$telEmpresa}\n\n\n";

        // ── SECCIÓN 2: datos del cliente ───────────────────────────────────────
        $s2  = substr("RUC: {$rucCliente}", 0, $ancho)     . "\n";
        $s2 .= substr("Nombre: {$nombreCliente}", 0, $ancho) . "\n";
        $s2 .= substr("Dirección: {$dirCliente}", 0, $ancho) . "\n";
        $s2 .= substr("Vendedor: {$nombreVend}", 0, $ancho)  . "\n";
        $s2 .= substr("Fecha: {$fechaFmt}", 0, $ancho)       . "\n";
        $s2 .= substr("{$labelNro} {$numref}", 0, $ancho)    . "\n\n\n";

        // ── SECCIÓN 4: totales ────────────────────────────────────────────────
        $totalesData = [
            'Total Importe'  => number_format($montosub, 2),
            'Descuentos'     => number_format($montodes, 2),
            'Monto Exento'   => '0.00',
            'Monto Gravado'  => number_format($montotot, 2),
            'Total Impuesto' => number_format($montoimp, 2),
            'Total'          => number_format($montotot, 2),
        ];

        $s4 = str_repeat('_', 39) . "\n";
        foreach ($totalesData as $label => $monto) {
            $espacio = 29 - strlen($label) - strlen($monto);
            $fila    = $label . str_repeat(' ', max(1, $espacio)) . $monto;
            $s4     .= str_repeat(' ', 10) . $fila . "\n";
        }
        $s4 .= "\n";

        // ── SECCIÓN 5: formas de pago (solo FAC) ──────────────────────────────
        $s5 = '';
        if ($tiptran === 'FAC' && !empty($pagos)) {
            $s5 .= str_repeat('_', 51) . "\n";
            $s5 .= "\nMÉTODOS DE PAGO:\n";
            foreach ($pagos as $pago) {
                $desc   = $pago->DESCRINSTRUMENTO ?? '';
                $smonto = number_format((float) ($pago->MONTOPAG ?? 0), 2);
                $s5    .= $desc . str_pad($smonto, 51 - strlen($desc), ' ', STR_PAD_LEFT) . "\n";
            }
            $s5 .= str_repeat('_', 51) . "\n";
            if ($cambio > 0) {
                $smontoCambio = number_format($cambio, 2);
                $s5 .= 'CAMBIO:  ' . str_pad($smontoCambio, 42 - strlen($smontoCambio), ' ', STR_PAD_LEFT) . "\n";
                $s5 .= str_repeat('_', 51) . "\n\n";
            }
        }

        // ── SECCIÓN 6: conteo de artículos ────────────────────────────────────
        $cantArt = count($detalles);
        $s6 = "\n\nCant. de artículos = {$cantArt}\n";

        // ── Calcular alto dinámico ────────────────────────────────────────────
        $incremento = $codigoQr ? 180 : 120;
        $alto = 3.8 * (29 + $cantArt) + $incremento;
        $alto = max($alto, self::ANCHO_MM);

        // ── Crear PDF ─────────────────────────────────────────────────────────
        $pdf = new class ('P', 'mm', [self::ANCHO_MM, $alto]) extends TCPDF {
            public function Header(): void {}
            public function Footer(): void {}
        };

        $pdf->SetAuthor('InnovaWeb');
        $pdf->SetTitle('Ticket');
        $pdf->SetMargins(0, 0, 0, 0);
        $pdf->SetDisplayMode('fullpage', 'two');
        $pdf->SetAutoPageBreak(true, 0);
        $pdf->AddPage('P');
        $pdf->SetFont('courier', 'B', 8);

        // Cabecera
        $pdf->MultiCell(78, 4, $s1, 0, 'C', false, 1, 2);
        $pdf->MultiCell(78, 4, $s2, 0, 'L', false, 1, 2);

        // Encabezado columnas
        $pdf->MultiCell(20, 4, 'CANT',        'B', 'L', false, 0, 2);
        $pdf->MultiCell(44, 4, 'DESCRIPCION', 'B', 'L', false, 0, 22);
        $pdf->MultiCell(14, 4, 'TOTAL',       'B', 'R', false, 1, 66);

        // Ítems
        foreach ($detalles as $det) {
            $codpro   = trim($det->CODPRO   ?? '');
            $descrip  = trim($det->DESCRIP1 ?? '');
            $cantidad = rtrim(number_format((float) ($det->CANTIDAD  ?? 0), 2, '.', ''), '0');
            $cantidad = rtrim($cantidad, '.');
            $precio   = number_format((float) ($det->PRECOSUNI ?? 0), 2);
            $total    = number_format((float) ($det->TOTAL     ?? 0), 2);
            $descPor  = (float) ($det->PORDES         ?? 0);
            $descMonto = (float) ($det->MONTODESCUENTO ?? 0);

            $pdf->SetFont('courier', 'B', 7);
            $pdf->MultiCell(18, 4, $codpro,  0, 'L', false, 0, 2);
            $pdf->MultiCell(58, 4, $descrip, 0, 'L', false, 1, 22);

            $pdf->MultiCell(20, 4, "{$cantidad}x{$precio}", 0, 'L', false, 0, 2);
            $pdf->MultiCell(62, 4, $total,                  0, 'R', false, 1, 18);

            if ($descMonto > 0) {
                $pdf->SetFont('courier', '', 7);
                $pdf->MultiCell(76, 4, "DCTO: {$descPor}%  -{$descMonto}", 0, 'R', false, 1, 2);
            }
        }

        $pdf->SetFont('courier', 'B', 8);

        // Totales
        $pdf->MultiCell(78, 4, $s4, 0, 'R', false, 1, 2);

        // Formas de pago (FAC)
        if ($s5 !== '') {
            $pdf->MultiCell(78, 4, $s5, 0, 'R', false, 1, 2);
        }

        // Cant. artículos
        $pdf->MultiCell(78, 4, $s6, 0, 'C', false, 1, 2);

        // ── Bloque FE + QR (solo FAC con QR) ─────────────────────────────────
        if ($tiptran === 'FAC' && $codigoQr !== '') {
            $fechaCafe = '';
            if ($fechaRecepDgi !== '') {
                try {
                    $dt = new \DateTime($fechaRecepDgi);
                    $fechaCafe = $dt->format('d/m/Y H:i:s');
                } catch (\Throwable) {}
            }

            $sFe  = "\nCAFE de emisión previa, transmisión de la DIRECCIÓN GENERAL DE INGRESOS";
            if ($fechaCafe) $sFe .= " hasta {$fechaCafe}";
            $sFe .= "\n\nPara verificar el CUFE consulte en:\n\n https://fe.dgi.mef.gob.pa/consulta \nusando el código:\n";
            $sFe .= "\n{$nroAutorizac}\n";
            $sFe .= "\nó escaneando el código QR:\n";

            $pdf->MultiCell(72, 4, $sFe, 0, 'C', false, 1, 2);

            $posY = $pdf->GetY() + 5;
            $pdf->write2DBarcode($codigoQr, 'QRCODE', 6, $posY, 70, 70, [
                'border'        => 0,
                'vpadding'      => 'auto',
                'hpadding'      => 'auto',
                'fgcolor'       => [0, 0, 0],
                'bgcolor'       => false,
                'module_width'  => 1,
                'module_height' => 1,
            ]);
        }

        $pdf->SetFont('courier', 'B', 9);
        $pdf->MultiCell(72, 4, "\n\nCopia de cliente", 0, 'C', false, 1, 2);

        return $pdf->Output('ticket.pdf', 'S');
    }
}
