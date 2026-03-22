<?php

class Item
{
  public $descripcion;
  public $codigo;
  public $unidadMedida;
  public $cantidad;
  public $fechaFabricacion;
  public $unidadMedidaCPBS;
  public $precioUnitario;
  public $precioUnitarioDescuento;
  public $precioAcarreo;
  public $precioSeguro;
  public $precioItem;
  public $valorTotal;
  public $codigoGTIN;
  public $cantGTINCom;
  public $codigoGTINInv;
  public $cantGTINComInv;
  public $tasaITBMS;
  public $valorITBMS;
  public $tasaISC;
  public $valorISC;
  public $codigoCPBS;
  public $codigoCPBSAbrev;

  public function __construct($data = [])
  {
    $this->descripcion = isset($data['descripcion']) ? $data['descripcion'] : null;
    $this->codigo = isset($data['codigo']) ? $data['codigo'] : null;
    $this->unidadMedida = isset($data['unidadMedida']) ? $data['unidadMedida'] : null;
    $this->cantidad = isset($data['cantidad']) ? $data['cantidad'] : null;
    $this->fechaFabricacion = isset($data['fechaFabricacion']) ? $data['fechaFabricacion'] : null;
    $this->unidadMedidaCPBS = isset($data['unidadMedidaCPBS']) ? $data['unidadMedidaCPBS'] : null;
    $this->precioUnitario = isset($data['precioUnitario']) ? $data['precioUnitario'] : null;
    $this->precioUnitarioDescuento = isset($data['precioUnitarioDescuento']) ? $data['precioUnitarioDescuento'] : null;
    $this->precioAcarreo = isset($data['precioAcarreo']) ? $data['precioAcarreo'] : null;
    $this->precioSeguro = isset($data['precioSeguro']) ? $data['precioSeguro'] : null;
    $this->precioItem = isset($data['precioItem']) ? $data['precioItem'] : null;
    $this->valorTotal = isset($data['valorTotal']) ? $data['valorTotal'] : null;
    $this->codigoGTIN = isset($data['codigoGTIN']) ? $data['codigoGTIN'] : null;
    $this->cantGTINCom = isset($data['cantGTINCom']) ? $data['cantGTINCom'] : null;
    $this->codigoGTINInv = isset($data['codigoGTINInv']) ? $data['codigoGTINInv'] : null;
    $this->cantGTINComInv = isset($data['cantGTINComInv']) ? $data['cantGTINComInv'] : null;
    $this->tasaITBMS = isset($data['tasaITBMS']) ? $data['tasaITBMS'] : null;
    $this->valorITBMS = isset($data['valorITBMS']) ? $data['valorITBMS'] : null;
    $this->tasaISC = isset($data['tasaISC']) ? $data['tasaISC'] : null;
    $this->valorISC = isset($data['valorISC']) ? $data['valorISC'] : null;
    $this->codigoCPBS = isset($data['codigoCPBS']) ? $data['codigoCPBS'] : null;
    $this->codigoCPBSAbrev = isset($data['codigoCPBSAbrev']) ? $data['codigoCPBSAbrev'] : null;
  }

  // getters
  public function getDescripcion() { return $this->descripcion; }
  public function getCodigo() { return $this->codigo; }
  public function getUnidadMedida() { return $this->unidadMedida; }
  public function getCantidad() { return $this->cantidad; }
  public function getFechaFabricacion() { return $this->fechaFabricacion; }
  public function getUnidadMedidaCPBS() { return $this->unidadMedidaCPBS; }
  public function getPrecioUnitario() { return $this->precioUnitario; }
  public function getPrecioUnitarioDescuento() { return $this->precioUnitarioDescuento; }
  public function getPrecioAcarreo() { return $this->precioAcarreo; }
  public function getPrecioSeguro() { return $this->precioSeguro; }
  public function getPrecioItem() { return $this->precioItem; }
  public function getValorTotal() { return $this->valorTotal; }
  public function getCodigoGTIN() { return $this->codigoGTIN; }
  public function getCantGTINCom() { return $this->cantGTINCom; }
  public function getCodigoGTINInv() { return $this->codigoGTINInv; }
  public function getCantGTINComInv() { return $this->cantGTINComInv; }
  public function getTasaITBMS() { return $this->tasaITBMS; }
  public function getValorITBMS() { return $this->valorITBMS; }
  public function getTasaISC() { return $this->tasaISC; }
  public function getValorISC() { return $this->valorISC; }
  public function getCodigoCPBS() { return $this->codigoCPBS; }
  public function getCodigoCPBSAbrev() { return $this->codigoCPBSAbrev; }

  // setters
  public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
  public function setCodigo($codigo) { $this->codigo = $codigo; }
  public function setUnidadMedida($unidadMedida) { $this->unidadMedida = $unidadMedida; }
  public function setCantidad($cantidad) { $this->cantidad = $cantidad; }
  public function setFechaFabricacion($fechaFabricacion) { $this->fechaFabricacion = $fechaFabricacion; }
  public function setUnidadMedidaCPBS($unidadMedidaCPBS) { $this->unidadMedidaCPBS = $unidadMedidaCPBS; }
  public function setPrecioUnitario($precioUnitario) { $this->precioUnitario = $precioUnitario; }
  public function setPrecioUnitarioDescuento($precioUnitarioDescuento) { $this->precioUnitarioDescuento = $precioUnitarioDescuento; }
  public function setPrecioAcarreo($precioAcarreo) { $this->precioAcarreo = $precioAcarreo; }
  public function setPrecioSeguro($precioSeguro) { $this->precioSeguro = $precioSeguro; }
  public function setPrecioItem($precioItem) { $this->precioItem = $precioItem; }
  public function setValorTotal($valorTotal) { $this->valorTotal = $valorTotal; }
  public function setCodigoGTIN($codigoGTIN) { $this->codigoGTIN = $codigoGTIN; }
  public function setCantGTINCom($cantGTINCom) { $this->cantGTINCom = $cantGTINCom; }
  public function setCodigoGTINInv($codigoGTINInv) { $this->codigoGTINInv = $codigoGTINInv; }
  public function setCantGTINComInv($cantGTINComInv) { $this->cantGTINComInv = $cantGTINComInv; }
  public function setTasaITBMS($tasaITBMS) { $this->tasaITBMS = $tasaITBMS; }
  public function setValorITBMS($valorITBMS) { $this->valorITBMS = $valorITBMS; }
  public function setTasaISC($tasaISC) { $this->tasaISC = $tasaISC; }
  public function setValorISC($valorISC) { $this->valorISC = $valorISC; }
  public function setCodigoCPBS($codigoCPBS) { $this->codigoCPBS = $codigoCPBS == "" ? '1310' : $codigoCPBS; }
  public function setCodigoCPBSAbrev($codigoCPBSAbrev) { $this->codigoCPBSAbrev = $codigoCPBSAbrev == "" ? '13' : $codigoCPBSAbrev; }
  // unsetters
  public function unsetDescripcion() { unset( $this->descripcion) ; }
  public function unsetCodigo() { unset( $this->codigo) ; }
  public function unsetUnidadMedida() { unset( $this->unidadMedida) ; }
  public function unsetCantidad() { unset( $this->cantidad) ; }
  public function unsetFechaFabricacion() { unset( $this->fechaFabricacion) ; }
  public function unsetUnidadMedidaCPBS() { unset( $this->unidadMedidaCPBS) ; }
  public function unsetPrecioUnitario() { unset( $this->precioUnitario) ; }
  public function unsetPrecioUnitarioDescuento() { unset( $this->precioUnitarioDescuento) ; }
  public function unsetPrecioAcarreo() { unset( $this->precioAcarreo) ; }
  public function unsetPrecioSeguro() { unset( $this->precioSeguro) ; }
  public function unsetPrecioItem() { unset( $this->precioItem) ; }
  public function unsetValorTotal() { unset( $this->valorTotal) ; }
  public function unsetCodigoGTIN() { unset( $this->codigoGTIN) ; }
  public function unsetCantGTINCom() { unset( $this->cantGTINCom) ; }
  public function unsetCodigoGTINInv() { unset( $this->codigoGTINInv) ; }
  public function unsetCantGTINComInv() { unset( $this->cantGTINComInv) ; }
  public function unsetTasaITBMS() { unset( $this->tasaITBMS) ; }
  public function unsetValorITBMS() { unset( $this->valorITBMS) ; }
  public function unsetTasaISC() { unset( $this->tasaISC) ; }
  public function unsetValorISC() { unset( $this->valorISC) ; }
  public function unsetCodigoCPBS() { unset( $this->codigoCPBS) ; }
  public function unsetCodigoCPBSAbrev() { unset( $this->codigoCPBSAbrev) ; }
}
?>
