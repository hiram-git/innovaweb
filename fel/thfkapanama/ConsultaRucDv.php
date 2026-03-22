<?php
class ConsultarRucDV
{

    public $tokenEmpresa;
    public $tokenPassword;
    public $tipoRuc;
    public $ruc;

    public function __construct($values)
    {
        $this->tokenEmpresa = $values['tokenEmpresa'];
        $this->tokenPassword = $values['tokenPassword'];
        $this->tipoRuc = $values['tipoRuc'];
        $this->ruc = $values['ruc'];
    }

    public function setTokenEmpresa($tokenEmpresa)
    {
        $this->tokenEmpresa = $tokenEmpresa;
    }

    public function getTokenEmpresa()
    {
        return $this->tokenEmpresa;
    }

    public function setTokenPassword($tokenPassword)
    {
        $this->tokenPassword = $tokenPassword;
    }

    public function getTokenPassword()
    {
        return $this->tokenPassword;
    }

    public function setTipoRuc($tipoRuc)
    {
        $this->tipoRuc = $tipoRuc;
    }

    public function getTipoRuc()
    {
        return $this->tipoRuc;
    }

    public function setRuc($ruc)
    {
        $this->ruc = $ruc;
    }

    public function getRuc()
    {
        return $this->ruc;
    }

    public function consultarRucDv($direccion_envio )
    {
        $options = array('trace' => true,  'exceptions' => true);

        $wsPa = new SoapClient($direccion_envio, $options);

        $params = array(
            'tokenEmpresa' => $this->tokenEmpresa,
            'tokenPassword' => $this->tokenPassword,
            'tipoRuc' => $this->tipoRuc,
            'ruc' => $this->ruc
        );
        $arrayRequest = array("consultarRucDVRequest" => $params);

        try {
            $respWsPa = $wsPa->__soapCall('ConsultarRucDV', array($arrayRequest));

            $pathLog = "../request/RUC-".$this->ruc."-". $this->tipoRuc.".xml";
            // Formatear el XML
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($wsPa->__getLastRequest());
            $formattedXml = $dom->saveXML();
            file_put_contents($pathLog, $formattedXml);
            return $respWsPa;
        } catch (SoapFault $e) {
            // Manejar errores de conexión o del Web Service
            return false;
        }
    }
}