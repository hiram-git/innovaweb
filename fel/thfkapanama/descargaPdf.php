<?php
class descargaPDF
{
    public function descargarPDF( $parametros, $direccion_envio, $db ){
        if($db == null){

        }
        $options = array('trace' => true,  'exceptions' => true);
        $documento = array(
            "codigoSucursalEmisor" => $parametros['documento']->codigoSucursalEmisor,
            "numeroDocumentoFiscal" => $parametros['documento']->datosTransaccion->numeroDocumentoFiscal,
            "puntoFacturacionFiscal" => $parametros['documento']->datosTransaccion->puntoFacturacionFiscal,
            "tipoDocumento" => $parametros['documento']->datosTransaccion->tipoDocumento,
            "tipoEmision" => $parametros['documento']->datosTransaccion->tipoEmision,
        );
        $wsPa = new SoapClient($direccion_envio, $options);
        $descargaPDF = array(
            'tokenEmpresa'  => $parametros["tokenEmpresa"],
            'tokenPassword' => $parametros["tokenPassword"],
            'datosDocumento'     => $documento
        );
        //- A continuación, enviamos el documento al método "Enviar" del Servicio Web de Integración de TFHKA

        $respWsPa = $wsPa->__soapCall('DescargaPDF', array($descargaPDF));

        /*$pathLog = "../request/".$documento["numeroDocumentoFiscal"]."-PDF.xml";
        // Formatear el XML
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($wsPa->__getLastRequest());
        $formattedXml = $dom->saveXML();
        file_put_contents($pathLog, $formattedXml);*/
        return $respWsPa;

    }
}