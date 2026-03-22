<?php 
class DocumentoElectronico
{
  public $codigoSucursalEmisor ;
  public $tipoSucursal;
  public $numeroDocumentoFiscal;
  public $datosTransaccion;
  public $listaItems;
  public $totalesSubTotales;

  public function __construct($data = [])
  {
    $this->codigoSucursalEmisor = isset($data['codigoSucursalEmisor']) ? $data['codigoSucursalEmisor'] : null;
    $this->tipoSucursal = isset($data['tipoSucursal']) ? $data['tipoSucursal'] : null;
    $this->numeroDocumentoFiscal = isset($data['numeroDocumentoFiscal']) ? $data['numeroDocumentoFiscal'] : null;
    $this->datosTransaccion = isset($data['datosTransaccion']) ? $data['datosTransaccion'] : null;
    $this->listaItems = isset($data['listaItems']) ? $data['listaItems'] : null;
    $this->totalesSubTotales = isset($data['totalesSubTotales']) ? $data['totalesSubTotales'] : null;
  }

  public function getCodigoSucursalEmisor() { return $this->codigoSucursalEmisor; }
  public function getTipoSucursal() { return $this->tipoSucursal; }
  public function getNumeroDocumentoFiscal() { return $this->numeroDocumentoFiscal; }
  public function getDatosTransaccion() { return $this->datosTransaccion; }
  public function getListaItems() { return $this->listaItems; }
  public function getTotalesSubTotales() { return $this->totalesSubTotales; }

  public function setCodigoSucursalEmisor($codigoSucursalEmisor) { $this->codigoSucursalEmisor = $codigoSucursalEmisor; }
  public function setTipoSucursal($tipoSucursal) { $this->tipoSucursal = $tipoSucursal; }
  public function setNumeroDocumentoFiscal($numeroDocumentoFiscal) { $this->numeroDocumentoFiscal = $numeroDocumentoFiscal; }
  public function setDatosTransaccion($datosTransaccion) { $this->datosTransaccion = $datosTransaccion; }
  public function setListaItems($listaItems) { $this->listaItems = $listaItems; }
  public function setTotalesSubTotales($totalesSubTotales) { $this->totalesSubTotales = $totalesSubTotales; }
}