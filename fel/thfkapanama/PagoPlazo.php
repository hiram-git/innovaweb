<?php
class PagoPlazo
{
  public $fechaVenceCuota;
  public $valorCuota;
  public $infoPagoCuota;

  public function __construct(array $data)
  {
    $this->fechaVenceCuota = isset($data['fechaVenceCuota']) ? $data['fechaVenceCuota'] : null;
    $this->valorCuota = isset($data['valorCuota']) ? $data['valorCuota'] : null;
    $this->infoPagoCuota = isset($data['infoPagoCuota']) ? $data['infoPagoCuota'] : null;
  }

  // getters
  public function getFechaVenceCuota() { return $this->fechaVenceCuota; }
  public function getValorCuota() { return $this->valorCuota; }
  public function getInfoPagoCuota() { return $this->infoPagoCuota; }

  // setters
  public function setFechaVenceCuota($fechaVenceCuota) { $this->fechaVenceCuota = $fechaVenceCuota; }
  public function setValorCuota($valorCuota) { $this->valorCuota = $valorCuota; }
  public function setInfoPagoCuota($infoPagoCuota) { $this->infoPagoCuota = $infoPagoCuota; }
}