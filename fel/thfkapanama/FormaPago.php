<?php
class FormaPago
{
  public $formaPagoFact;
  public $valorCuotaPagada;
  public $descFormaPago;

  public function __construct(array $data)
  {
    $this->formaPagoFact = isset($data['formaPagoFact']) ? $data['formaPagoFact'] : null;
    $this->valorCuotaPagada = isset($data['valorCuotaPagada']) ? $data['valorCuotaPagada'] : null;
    $this->descFormaPago = isset($data['descFormaPago']) ? $data['descFormaPago'] : null;
  }

  // getters
  public function getFormaPagoFact() { return $this->formaPagoFact; }
  public function getValorCuotaPagada() { return $this->valorCuotaPagada; }
  public function getDescFormaPago() { return $this->descFormaPago; }

  // setters
  public function setFormaPagoFact($formaPagoFact) { $this->formaPagoFact = $formaPagoFact; }
  public function setValorCuotaPagada($valorCuotaPagada) { $this->valorCuotaPagada = $valorCuotaPagada; }
  public function setDescFormaPago($descFormaPago) { $this->descFormaPago = $descFormaPago; }
}