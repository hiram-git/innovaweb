<?php
class Descuentos
{
  public $descDescuento;
  public $montoDescuento;

  public function __construct(array $data)
  {
    $this->descDescuento = isset($data['descDescuento']) ? $data['descDescuento'] : null;
    $this->montoDescuento = isset($data['montoDescuento']) ? $data['montoDescuento'] : null;
  }

  // getters
  public function getDescDescuento() { return $this->descDescuento; }
  public function getMontoDescuento() { return $this->montoDescuento; }

  // setters
  public function setDescDescuento($descDescuento) { $this->descDescuento = $descDescuento; }
  public function setMontoDescuento($montoDescuento) { $this->montoDescuento = $valorCuotaPagada; }
}