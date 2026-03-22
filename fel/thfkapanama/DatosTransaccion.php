<?php
class DatosTransaccion
{
  public $tipoEmision;
  public $tipoDocumento;
  public $numeroDocumentoFiscal;
  public $puntoFacturacionFiscal;
  public $fechaEmision;
  public $naturalezaOperacion;
  public $tipoOperacion;
  public $destinoOperacion;
  public $formatoCAFE;
  public $entregaCAFE;
  public $envioContenedor;
  public $procesoGeneracion;
  public $tipoVenta;
  public $informacionInteres;
  public $cliente;

  public function __construct($data = [])
  {
    $this->tipoEmision = isset($data['tipoEmision']) ? $data['tipoEmision'] : "01";
    $this->tipoDocumento = isset($data['tipoDocumento']) ? $data['tipoDocumento'] : "01";
    $this->numeroDocumentoFiscal = isset($data['numeroDocumentoFiscal']) ? $data['numeroDocumentoFiscal'] : null;
    $this->puntoFacturacionFiscal = isset($data['puntoFacturacionFiscal']) ? $data['puntoFacturacionFiscal'] : "001";
    $this->fechaEmision = isset($data['fechaEmision']) ? $data['fechaEmision'] : null;
    $this->naturalezaOperacion = isset($data['naturalezaOperacion']) ? $data['naturalezaOperacion'] : "01";
    $this->tipoOperacion = isset($data['tipoOperacion']) ? $data['tipoOperacion'] : "1";
    $this->destinoOperacion = isset($data['destinoOperacion']) ? $data['destinoOperacion'] : "1";
    $this->formatoCAFE = isset($data['formatoCAFE']) ? $data['formatoCAFE'] : "1";
    $this->entregaCAFE = isset($data['entregaCAFE']) ? $data['entregaCAFE'] : "1";
    $this->envioContenedor = isset($data['envioContenedor']) ? $data['envioContenedor'] : "1";
    $this->procesoGeneracion = isset($data['procesoGeneracion']) ? $data['procesoGeneracion'] : "1";
    $this->tipoVenta = isset($data['tipoVenta']) ? $data['tipoVenta'] : "1";
    $this->informacionInteres = isset($data['informacionInteres']) ? $data['informacionInteres'] : "";
    $this->cliente = isset($data['cliente']) ? $data['cliente'] : null;
  }
  
  public function getTipoEmision() { return $this->tipoEmision; }
  public function getTipoDocumento() { return $this->tipoDocumento; }
  public function getNumeroDocumentoFiscal() { return $this->numeroDocumentoFiscal; }
  public function getPuntoFacturacionFiscal() { return $this->puntoFacturacionFiscal; }
  public function getFechaEmision() { return $this->fechaEmision; }
  public function getNaturalezaOperacion() { return $this->naturalezaOperacion; }
  public function getTipoOperacion() { return $this->tipoOperacion; }
  public function getDestinoOperacion() { return $this->destinoOperacion; }
  public function getFormatoCAFE() { return $this->formatoCAFE; }
  public function getEntregaCAFE() { return $this->entregaCAFE; }
  public function getEnvioContenedor() { return $this->envioContenedor; }
  public function getProcesoGeneracion() { return $this->procesoGeneracion; }
  public function getTipoVenta() { return $this->tipoVenta; }
  public function getInformacionInteres() { return $this->informacionInteres; }
  public function getCliente() { return $this->cliente; }

  // setters
  public function setTipoEmision($tipoEmision) { $this->tipoEmision = $tipoEmision; }
  public function setTipoDocumento($tipoDocumento) { $this->tipoDocumento = $tipoDocumento; }
  public function setNumeroDocumentoFiscal($numeroDocumentoFiscal) { $this->numeroDocumentoFiscal = $numeroDocumentoFiscal; }
  public function setPuntoFacturacionFiscal($puntoFacturacionFiscal) { $this->puntoFacturacionFiscal = $puntoFacturacionFiscal; }
  public function setFechaEmision($fechaEmision) { $this->fechaEmision = $fechaEmision; }
  public function setNaturalezaOperacion($naturalezaOperacion) { $this->naturalezaOperacion = $naturalezaOperacion; }
  public function setTipoOperacion($tipoOperacion) { $this->tipoOperacion = $tipoOperacion; }
  public function setDestinoOperacion($destinoOperacion) { $this->destinoOperacion = $destinoOperacion; }
  public function setFormatoCAFE($formatoCAFE) { $this->formatoCAFE = $formatoCAFE; }
  public function setEntregaCAFE($entregaCAFE) { $this->entregaCAFE = $entregaCAFE; }
  public function setEnvioContenedor($envioContenedor) { $this->envioContenedor = $envioContenedor; }
  public function setProcesoGeneracion($procesoGeneracion) { $this->procesoGeneracion = $procesoGeneracion; }
  public function setTipoVenta($tipoVenta) { $this->tipoVenta = $tipoVenta; }
  public function setInformacionInteres($informacionInteres) { $this->informacionInteres = $informacionInteres; }
  public function setCliente($cliente) { $this->cliente = $cliente; }
}