<?php
class Retencion
{
  public $codigoRetencion;
  public $montoRetencion;

  public function __construct(array $data)
  {
    $this->codigoRetencion = isset($data['codigoRetencion']) ? $data['codigoRetencion'] : null;
    $this->montoRetencion = isset($data['montoRetencion']) ? $data['montoRetencion'] : null;
  }

  // getters
  public function getCodigoRetencion() { return $this->codigoRetencion; }
  public function getMontoRetencion() { return $this->montoRetencion; }

  // setters
  public function setCodigoRetencion($codigoRetencion) { $this->codigoRetencion = $codigoRetencion; }
  public function setMontoRetencion($montoRetencion) { $this->montoRetencion = $montoRetencion; }
}