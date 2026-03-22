<?php

class Totales
{
  public $totalPrecioNeto;
  public $totalITBMS;
  public $totalISC;
  public $totalMontoGravado;
  public $totalDescuento;
  public $totalAcarreoCobrado;
  public $valorSeguroCobrado;
  public $totalFactura;
  public $totalValorRecibido;
  public $vuelto;
  public $tiempoPago;
  public $nroItems;
  public $totalTodosItems;
  public $listaFormaPago;
  public $listaPagoPlazo;
  public $listaDescBonificacion;
  public $retencion;

  public function __construct(array $data)
  {
    $this->totalPrecioNeto = isset($data['totalPrecioNeto']) ? $data['totalPrecioNeto'] : null;
    $this->totalITBMS = isset($data['totalITBMS']) ? $data['totalITBMS'] : null;
    $this->totalISC = isset($data['totalISC']) ? $data['totalISC'] : null;
    $this->totalMontoGravado = isset($data['totalMontoGravado']) ? $data['totalMontoGravado'] : null;
    $this->totalDescuento = isset($data['totalDescuento']) ? $data['totalDescuento'] : null;
    $this->totalAcarreoCobrado = isset($data['totalAcarreoCobrado']) ? $data['totalAcarreoCobrado'] : null;
    $this->valorSeguroCobrado = isset($data['valorSeguroCobrado']) ? $data['valorSeguroCobrado'] : null;
    $this->totalFactura = isset($data['totalFactura']) ? $data['totalFactura'] : null;
    $this->totalValorRecibido = isset($data['totalValorRecibido']) ? $data['totalValorRecibido'] : null;
    $this->vuelto = isset($data['vuelto']) ? $data['vuelto'] : null;
    $this->tiempoPago = isset($data['tiempoPago']) ? $data['tiempoPago'] : null;
    $this->nroItems = isset($data['nroItems']) ? $data['nroItems'] : null;
    $this->totalTodosItems = isset($data['totalTodosItems']) ? $data['totalTodosItems'] : null;
    $this->listaFormaPago = isset($data['listaFormaPago']) ? $data['listaFormaPago'] : null;
    $this->listaDescBonificacion = isset($data['listaDescBonificacion']) ? $data['listaDescBonificacion'] : null;
    $this->retencion = isset($data['retencion']) ? $data['retencion'] : null;
  }

  // getters
  public function getTotalPrecioNeto() { return $this->totalPrecioNeto; }
  public function getTotalITBMS() { return $this->totalITBMS; }
  public function getTotalISC() { return $this->totalISC; }
  public function getTotalMontoGravado() { return $this->totalMontoGravado; }
  public function getTotalDescuento() { return $this->totalDescuento; }
  public function getTotalAcarreoCobrado() { return $this->totalAcarreoCobrado; }
  public function getValorSeguroCobrado() { return $this->valorSeguroCobrado; }
  public function getTotalFactura() { return $this->totalFactura; }
  public function getTotalValorRecibido() { return $this->totalValorRecibido; }
  public function getVuelto() { return $this->vuelto; }
  public function getTiempoPago() { return $this->tiempoPago; }
  public function getNroItems() { return $this->nroItems; }
  public function getTotalTodosItems() { return $this->totalTodosItems; }
  public function getListaFormaPago() { return $this->listaFormaPago; }
  public function getListaPagoPlazo() { return $this->listaPagoPlazo; }
  public function getListaDescBonificacion() { return $this->listaDescBonificacion; }
  public function getRetencion() { return $this->retencion; }

  // setters
  public function setTotalPrecioNeto($totalPrecioNeto) { $this->totalPrecioNeto = $totalPrecioNeto; }
  public function setTotalITBMS($totalITBMS) { $this->totalITBMS = $totalITBMS; }
  public function setTotalISC($totalISC) { $this->totalISC = $totalISC; }
  public function setTotalMontoGravado($totalMontoGravado) { $this->totalMontoGravado = $totalMontoGravado; }
  public function setTotalDescuento($totalDescuento) { $this->totalDescuento = $totalDescuento; }
  public function setTotalAcarreoCobrado($totalAcarreoCobrado) { $this->totalAcarreoCobrado = $totalAcarreoCobrado; }
  public function setValorSeguroCobrado($valorSeguroCobrado) { $this->valorSeguroCobrado = $valorSeguroCobrado; }
  public function setTotalFactura($totalFactura) { $this->totalFactura = $totalFactura; }
  public function setTotalValorRecibido($totalValorRecibido) { $this->totalValorRecibido = $totalValorRecibido; }
  public function setVuelto($vuelto) { $this->vuelto = $vuelto; }
  public function setTiempoPago($tiempoPago) { $this->tiempoPago = $tiempoPago; }
  public function setNroItems($nroItems) { $this->nroItems = $nroItems; }
  public function setTotalTodosItems($totalTodosItems) { $this->totalTodosItems = $totalTodosItems; }
  public function setListaFormaPago($listaFormaPago) { $this->listaFormaPago = $listaFormaPago; }
  public function setListaPagoPlazo($listaPagoPlazo) { $this->listaPagoPlazo = $listaPagoPlazo; }
  public function setListaDescBonificacion($listaDescBonificacion) { $this->listaDescBonificacion = $listaDescBonificacion; }
  public function setRetencion($retencion) { $this->retencion = $retencion; }

  public function unsetTotalISC() { unset( $this->totalISC) ; }
  public function unsetListaDescBonificacion() { unset( $this->listaDescBonificacion) ; }
  public function unsetTotalAcarreoCobrado() { unset( $this->totalAcarreoCobrado) ; }
  public function unsetValorSeguroCobrado() { unset( $this->valorSeguroCobrado) ; }
  public function unsetVuelto() { unset( $this->vuelto) ; }
  public function unsetDescuento() { unset( $this->totalDescuento) ; }
  public function unsetListaPagoPlazo() { unset( $this->listaPagoPlazo) ; }
  public function unsetRetencion() { unset( $this->retencion) ; }

}
