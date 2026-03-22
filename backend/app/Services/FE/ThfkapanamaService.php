<?php

declare(strict_types=1);

namespace App\Services\FE;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ThfkapanamaService — Integración con The Factory HKA (TFHKA)
 *
 * Protocolo: SOAP/WSDL
 * Documentación: https://ws.epak.com.pa/
 *
 * Esta clase es la migración del archivo legacy:
 *   fel/thfkapanama/factura.php
 *
 * Principales mejoras sobre el código legacy:
 *  1. Sin dependencias de $_SESSION ni $_REQUEST
 *  2. Queries con parámetros (no concatenación SQL)
 *  3. Manejo de errores con excepciones tipadas
 *  4. Lógica de negocio separada de la persistencia
 *  5. Fecha de emisión calculada correctamente (no hardcodeada)
 */
class ThfkapanamaService implements PACServiceInterface
{
    // Tasas ITBMS según la DGI Panamá
    private const TASA_ITBMS = [
        0  => '00',  // Exento
        7  => '01',  // 7%
        10 => '02',  // 10%
        15 => '03',  // 15%
    ];

    // Tipos de cliente FE según la DGI
    private const TIPO_CLIENTE_FE = [
        'Contribuyente'        => '01',
        'Exento'               => '01',
        'Otros exentos'        => '01',
        'Consumidor Final'     => '02',
        'Consumidor Final exento' => '02',
        'Gobierno'             => '03',
        'Gubernamental'        => '03',
    ];

    // Formas de pago DGI (BASEINSTRUMENTOS.FUNCION → código DGI)
    private const FORMA_PAGO_DGI = [
        '0' => '03',  // Crédito
        '1' => '01',  // Cheque
        '2' => '08',  // Transferencia
        '3' => '04',  // Tarjeta crédito
        '6' => '02',  // Efectivo
    ];

    public function enviarFactura(string $control, object $config): array
    {
        return $this->enviarDocumento($control, $config, '01');
    }

    public function enviarNotaCredito(string $control, object $config): array
    {
        return $this->enviarDocumento($control, $config, '04');
    }

    public function enviarNotaDebito(string $control, object $config): array
    {
        return $this->enviarDocumento($control, $config, '05');
    }

    public function descargarPDF(string $cufe, object $config): string
    {
        $wsdlUrl = $this->resolveWsdlUrl($config);
        $soapClient = new \SoapClient($wsdlUrl, ['trace' => true, 'exceptions' => true]);

        $respuesta = $soapClient->__soapCall('DescargaPDF', [[
            'tokenEmpresa'  => $config->USUARIO_RUC,
            'tokenPassword' => $config->CONTRASEÑA,
            'cufe'          => $cufe,
        ]]);

        return (string) ($respuesta->DescargaPDFResult->documento ?? '');
    }

    public function consultarEstado(string $cufe, object $config): array
    {
        $wsdlUrl    = $this->resolveWsdlUrl($config);
        $soapClient = new \SoapClient($wsdlUrl, ['trace' => true, 'exceptions' => true]);

        $respuesta = $soapClient->__soapCall('ConsultarEstado', [[
            'tokenEmpresa'  => $config->USUARIO_RUC,
            'tokenPassword' => $config->CONTRASEÑA,
            'cufe'          => $cufe,
        ]]);

        return (array) $respuesta;
    }

    // ─── Métodos privados ─────────────────────────────────────────────────────

    private function enviarDocumento(string $control, object $config, string $tipoDocumento): array
    {
        if (! extension_loaded('soap')) {
            throw new \RuntimeException('La extensión PHP SOAP no está habilitada. Activar en php.ini: extension=soap');
        }

        $maestro  = $this->getMaestro($control);
        $cliente  = $this->getCliente($maestro->CODIGO);
        $detalles = $this->getDetalles($control);
        $pagos    = $this->getPagos($control);
        $adenda   = $this->getAdenda($control);

        $documento = $this->construirDocumento(
            $maestro, $cliente, $detalles, $pagos, $adenda, $config, $tipoDocumento
        );

        $wsdlUrl    = $this->resolveWsdlUrl($config);
        $soapClient = new \SoapClient($wsdlUrl, ['trace' => true, 'exceptions' => true]);

        $respuesta = $soapClient->__soapCall('Enviar', [[
            'tokenEmpresa'  => $config->USUARIO_RUC,
            'tokenPassword' => $config->CONTRASEÑA,
            'documento'     => $documento,
        ]]);

        // Guardar XML de la petición para auditoría
        $this->guardarXmlLog($control, $soapClient->__getLastRequest());

        $result = $respuesta->EnviarResult;
        $estado = ((int) $result->codigo) === 200 ? 1 : 0;

        $respArray = [
            'estado'                 => $estado,
            'codigo'                 => $result->codigo,
            'resultado'              => $result->resultado ?? '',
            'mensaje'                => $result->mensaje ?? '',
            'cufe'                   => $result->cufe ?? '',
            'qr'                     => $result->qr ?? '',
            'fechaRecepcionDGI'      => $result->fechaRecepcionDGI ?? null,
            'nroProtocoloAutorizacion' => $result->nroProtocoloAutorizacion ?? '',
            'fechaLimite'            => $result->fechaLimite ?? null,
        ];

        // Si fue aceptado, descargar el PDF del PAC
        if ($estado === 1) {
            try {
                $pdfBase64 = $this->descargarPDF($result->cufe, $config);
                $respArray['pdf'] = $pdfBase64;

                DB::statement(
                    "UPDATE Documentos SET PDF = :pdf, NUMDOCFISCAL = :nrodoc WHERE CONTROL = :control",
                    [
                        'pdf'     => $pdfBase64,
                        'nrodoc'  => str_pad($maestro->NUMREF, 10, '0', STR_PAD_LEFT),
                        'control' => $control,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('FE TFHKA: No se pudo descargar el PDF', [
                    'control' => $control,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return $respArray;
    }

    private function construirDocumento(
        object $maestro,
        object $cliente,
        array  $detalles,
        array  $pagos,
        ?object $adenda,
        object $config,
        string $tipoDocumento
    ): object {
        [$tipoClienteFE, $tipoContribuyente] = $this->resolverTipoCliente($cliente);
        [$numeroRUC, $dv]                    = $this->resolverRucDv($cliente, $tipoClienteFE, $tipoContribuyente);
        $codigoUbicacion                     = $this->resolverUbicacion($cliente);
        $numeroDocFiscal                     = str_pad($maestro->NUMREF, 10, '0', STR_PAD_LEFT);

        // Fecha de emisión en formato DGI: "yyyy-MM-ddTHH:mm:ss-05:00"
        $fechaEmision = now('America/Panama')->format('Y-m-d\TH:i:s-05:00');

        $retencion  = null;
        $valRetenc  = 0;
        $descuento  = null;

        if ((int) $cliente->CONESPECIAL === 1) {
            $retencionMaestro = $this->getRetencion($maestro->NUMREF);
            $valRetenc        = (float) ($retencionMaestro?->MONTOTOT ?? 0);
            $codRetencion     = $this->resolverCodigoRetencion((int) $cliente->PORRETIMP);
            if ($valRetenc > 0) {
                $retencion = ['codigoRetencion' => $codRetencion, 'montoRetencion' => $this->dec($valRetenc)];
            }
        }

        if ((float) $maestro->MONTODES > 0) {
            $descuento = ['descDescuento' => 'DESC. GLOBAL', 'montoDescuento' => $this->dec($maestro->MONTODES)];
        }

        $items        = $this->construirItems($detalles, $maestro, $cliente);
        $sumaItbms    = array_sum(array_column($items, '_itbms_raw'));
        $sumaPrecioNeto = array_sum(array_column($items, '_precio_neto_raw'));

        // Limpiar campos internos antes de enviar al PAC
        foreach ($items as &$item) {
            unset($item['_itbms_raw'], $item['_precio_neto_raw']);
        }
        unset($item);

        if ((float) $maestro->MONTODES <= 0) {
            $sumaItbms = (float) $maestro->MONTOIMP;
        }

        $listaFormaPago = $this->construirFormaPago($pagos, $maestro, $sumaItbms);
        $totalFactura   = (float) $maestro->MONTOBRU + $sumaItbms - (float) $maestro->MONTODES;

        $totales = [
            'totalPrecioNeto'    => $this->dec($maestro->MONTOBRU),
            'totalITBMS'         => $this->dec($sumaItbms),
            'totalISC'           => '0.00',
            'totalMontoGravado'  => $this->dec($sumaItbms),
            'totalDescuento'     => $this->dec($maestro->MONTODES),
            'totalAcarreoCobrado'=> '0.00',
            'valorSeguroCobrado' => '0.00',
            'totalFactura'       => $this->dec($totalFactura),
            'totalValorRecibido' => $maestro->TIPOFACTURA === 'CREDITO'
                ? $this->dec((float) $maestro->MONTOSAL + $valRetenc)
                : $this->dec($totalFactura + (float) $maestro->CAMBIO),
            'vuelto'             => $this->dec($maestro->CAMBIO),
            'tiempoPago'         => (int) $maestro->DIASVEN <= 1 ? 1 : 2,
            'nroItems'           => count($detalles),
            'totalTodosItems'    => $this->dec($sumaPrecioNeto),
            'listaFormaPago'     => $listaFormaPago,
            'listaDescBonificacion' => $descuento,
        ];

        if ((int) $maestro->DIASVEN > 1) {
            $fechaVenc = new \DateTime($maestro->FECVENCS);
            $totales['listaPagoPlazo'] = [
                'pagoPlazo' => [
                    'fechaVenceCuota' => $fechaVenc->format('Y-m-d\TH:i:s-05:00'),
                    'valorCuota'      => $this->dec((float) $maestro->MONTOSAL + $valRetenc),
                    'infoPagoCuota'   => 'PAGO POR CUOTA ' . $this->dec((float) $maestro->MONTOSAL + $valRetenc),
                ],
            ];
        }

        if ($retencion) {
            $totales['listaRetencion'] = ['retencion' => $retencion];
        }

        if ((float) $maestro->CAMBIO == 0) {
            unset($totales['vuelto']);
        }

        return (object) [
            'codigoSucursalEmisor'   => $config->CODSUC ?? '0000',
            'tipoSucursal'           => $config->TIPOSUCURSAL ?? '1',
            'datosTransaccion'       => (object) [
                'tipoEmision'            => $config->TIPOEMISION,
                'tipoDocumento'          => $tipoDocumento,
                'numeroDocumentoFiscal'  => $numeroDocFiscal,
                'puntoFacturacionFiscal' => $config->CODPFACT,
                'fechaEmision'           => $fechaEmision,
                'naturalezaOperacion'    => $config->NATURALEZAOPERACION,
                'tipoOperacion'          => $config->TIPOOPERACION,
                'destinoOperacion'       => $config->CODPFACT,
                'formatoCAFE'            => $config->FORMATOCAFE,
                'entregaCAFE'            => $config->ENTREGACAFE,
                'envioContenedor'        => 1,
                'procesoGeneracion'      => 1,
                'informacionInteres'     => $adenda?->OBS1 ?? '',
                'cliente'                => (object) [
                    'tipoClienteFE'        => $tipoClienteFE,
                    'tipoContribuyente'    => $tipoContribuyente,
                    'numeroRUC'            => $numeroRUC,
                    'digitoVerificadorRUC' => $dv,
                    'razonSocial'          => $cliente->NOMBRE,
                    'direccion'            => $cliente->DIRECC1,
                    'codigoUbicacion'      => $codigoUbicacion,
                    'telefono1'            => $this->validarTelefono($cliente->NUMTEL ?? ''),
                    'correoElectronico1'   => $this->validarCorreo($cliente->DIRCORREO ?? ''),
                    'pais'                 => 'PA',
                ],
            ],
            'listaItems'             => ['item' => $items],
            'totalesSubTotales'      => (object) $totales,
        ];
    }

    private function construirItems(array $detalles, object $maestro, object $cliente): array
    {
        $items = [];
        foreach ($detalles as $detalle) {
            $tasaItbms = self::TASA_ITBMS[(int) $detalle->IMPPOR] ?? '00';
            $itbms     = ($detalle->CANTIDAD * $detalle->PRECOSUNI) * (int) $detalle->IMPPOR / 100;

            $items[] = array_merge([
                'descripcion'              => $detalle->DESCRIP1,
                'codigo'                   => $detalle->CODPRO,
                'unidadMedida'             => 'und',
                'cantidad'                 => $detalle->CANTIDAD,
                'precioUnitario'           => $this->dec($detalle->PRECOSUNI),
                'precioUnitarioDescuento'  => $this->dec($detalle->MONTODESCUENTO),
                'precioItem'               => $this->dec($detalle->CANTIDAD * ($detalle->PRECOSUNI - $detalle->MONTODESCUENTO)),
                'valorTotal'               => $this->dec($detalle->COSTOADU1),
                'tasaITBMS'               => $tasaItbms,
                'valorITBMS'              => $this->dec($detalle->MONTOIMP),
                // Código CPBS requerido solo para clientes Gobierno
                ...($maestro->TIPOCLI === 'Gobierno' || $maestro->TIPOCLI === 'Gubernamental'
                    ? ['codigoCPBS' => $detalle->CODCATD ?? '', 'codigoCPBSAbrev' => $detalle->CODCATH ?? '']
                    : []),
                '_itbms_raw'       => $itbms,
                '_precio_neto_raw' => (float) $detalle->COSTOADU1,
            ]);
        }
        return $items;
    }

    private function construirFormaPago(array $pagos, object $maestro, float $sumaItbms): array
    {
        if (empty($pagos)) {
            $monto = (float) $maestro->MONTOSAL > 0
                ? (float) $maestro->MONTOSAL
                : (float) $maestro->MONTOTOT;
            return [['formaPagoFact' => '01', 'valorCuotaPagada' => $this->dec($monto)]];
        }

        $lista    = [];
        $totPagos = 0;
        foreach ($pagos as $pago) {
            $codigo       = self::FORMA_PAGO_DGI[$pago->FUNCION ?? ''] ?? '99';
            $monto        = (float) $pago->MONTOPAG;
            $totPagos    += $monto;
            $lista[] = [
                'formaPagoFact'    => $codigo,
                'valorCuotaPagada' => $this->dec($monto),
                'descFormaPago'    => $codigo === '99' ? ($pago->DESCRIP_PAGO ?? '') : '',
            ];
        }

        // Ajustar último pago si hay descuento global
        if ((float) $maestro->MONTODES > 0 && ! empty($lista)) {
            $diferencia  = $totPagos - ((float) $maestro->MONTOBRU + $sumaItbms - (float) $maestro->MONTODES);
            $ultimo      = count($lista) - 1;
            $lista[$ultimo]['valorCuotaPagada'] = $this->dec(
                (float) $lista[$ultimo]['valorCuotaPagada'] - $diferencia
            );
        }

        return $lista;
    }

    private function resolverTipoCliente(object $cliente): array
    {
        $tipocli       = $cliente->TIPOCLI ?? '';
        $tipocomercio  = (int) ($cliente->TIPOCOMERCIO ?? 0);

        $tipoClienteFE    = self::TIPO_CLIENTE_FE[$tipocli] ?? '02';
        $tipoContribuyente = $tipocomercio === 2 ? '' : ($tipocomercio === 1 ? '2' : '1');

        if ($tipocomercio === 2) {
            $tipoClienteFE = '04';
        }

        if ($tipocli === 'Consumidor Final' && $tipocomercio === 1) {
            $tipoContribuyente = '1';
            $tipoClienteFE     = '02';
        }

        return [$tipoClienteFE, $tipoContribuyente];
    }

    private function resolverRucDv(object $cliente, string $tipoClienteFE, string $tipoContribuyente): array
    {
        if ($tipoClienteFE === '02' && $tipoContribuyente === '1') {
            return [str_pad($cliente->RIF ?? '', 5, '0', STR_PAD_RIGHT), ''];
        }
        return [$cliente->RIF ?? '', $cliente->NIT ?? ''];
    }

    private function resolverUbicacion(object $cliente): string
    {
        $g1 = is_numeric($cliente->NOMBREEGEO1 ?? '') ? $cliente->NOMBREEGEO1 : 8;
        $g2 = is_numeric($cliente->NOMBREEGEO2 ?? '') ? $cliente->NOMBREEGEO2 : 8;
        $g3 = is_numeric($cliente->NOMBREEGEO3 ?? '') ? $cliente->NOMBREEGEO3 : 8;
        return "{$g1}-{$g2}-{$g3}";
    }

    private function resolverCodigoRetencion(int $porRetimp): int
    {
        return match ($porRetimp) {
            100    => 1,
            50     => 4,
            default => 8,
        };
    }

    private function resolveWsdlUrl(object $config): string
    {
        $url = $config->DIRECCIONENVIO ?? '';
        if (empty($url)) {
            $ambiente = config('services.fe.ambiente', 'sandbox');
            return $ambiente === 'production'
                ? config('services.tfhka.wsdl_prod')
                : config('services.tfhka.wsdl_sandbox');
        }
        return $url;
    }

    private function guardarXmlLog(string $control, string $xml): void
    {
        $path = storage_path("logs/fe_xml/{$control}.xml");
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        try {
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            file_put_contents($path, $dom->saveXML());
        } catch (\Throwable) {
            file_put_contents($path, $xml);
        }
    }

    private function getMaestro(string $control): object
    {
        $result = DB::selectOne(
            "SELECT * FROM TRANSACCMAESTRO WHERE CONTROL = ?",
            [$control]
        );
        if (! $result) {
            throw new \RuntimeException("Factura no encontrada: CONTROL={$control}");
        }
        return $result;
    }

    private function getCliente(string $codigo): object
    {
        $result = DB::selectOne(
            "SELECT cp.*, bp.DESNOMBREEGEO1 geo1, bd.DESNOMBREEGEO2 geo2, bc.DESNOMBREEGEO3 geo3
             FROM BASECLIENTESPROVEEDORES cp
             LEFT JOIN BASEPROVINCIA bp ON cp.NOMBREEGEO1 = bp.NOMBREEGEO1
             LEFT JOIN BASEDISTRITO bd ON bd.NOMBREEGEO2 = cp.NOMBREEGEO2 AND bd.NOMBREEGEO1 = cp.NOMBREEGEO1
             LEFT JOIN BASECORREGIMIENTO bc ON bc.NOMBREEGEO3 = cp.NOMBREEGEO3
                   AND bc.NOMBREEGEO2 = cp.NOMBREEGEO2 AND bc.NOMBREEGEO1 = cp.NOMBREEGEO1
             WHERE cp.CODIGO = ? AND cp.TIPREG = '1' AND cp.INTEGRADO = '0'",
            [$codigo]
        );
        if (! $result) {
            throw new \RuntimeException("Cliente no encontrado: CODIGO={$codigo}");
        }
        return $result;
    }

    private function getDetalles(string $control): array
    {
        return DB::select(
            "SELECT * FROM TRANSACCDETALLES WHERE CONTROL = ? AND COMPONENTE = 0 ORDER BY FECHORA DESC",
            [$control]
        );
    }

    private function getPagos(string $control): array
    {
        return DB::select(
            "SELECT p.*, b.NOMBRE AS DESCRIP_PAGO FROM TRANSACCPAGOS p
             LEFT JOIN BASEINSTRUMENTOS b ON b.CODTAR = p.CODTAR
             WHERE p.CONTROL = ? ORDER BY b.FUNCION",
            [$control]
        );
    }

    private function getAdenda(string $control): ?object
    {
        return DB::selectOne(
            "SELECT * FROM TRANSACCOBSERVACIONES WHERE CONTROL = ?",
            [$control]
        );
    }

    private function getRetencion(string $numref): ?object
    {
        return DB::selectOne(
            "SELECT * FROM TRANSACCMAESTRO WHERE NUMDOC = ? AND TIPTRAN = 'N/CxIMP'",
            [$numref]
        );
    }

    private function validarTelefono(string $tel): string
    {
        return preg_match('/^\d{4}-\d{4}$/', trim($tel)) ? trim($tel) : '9999-9999';
    }

    private function validarCorreo(string $email): string
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) ? trim($email) : '';
    }

    private function dec(mixed $valor): string
    {
        return number_format((float) $valor, 2, '.', '');
    }
}
