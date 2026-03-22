<?php
class Cliente
{
  public $tipoClienteFE;
  public $tipoContribuyente;
  public $numeroRUC;
  public $digitoVerificadorRUC;
  public $razonSocial;
  public $direccion;
  public $codigoUbicacion;
  public $corregimiento;
  public $distrito;
  public $provincia;
  public $telefono1;
  public $telefono2;
  public $telefono3;
  public $correoElectronico1;
  public $pais;
  public $paisOtro;
  
  public function __construct($data = [])
  {
    $this->tipoClienteFE = isset($data['tipoClienteFE']) ? $data['tipoClienteFE'] : null;
    $this->tipoContribuyente = isset($data['tipoContribuyente']) ? $data['tipoContribuyente'] : null;
    $this->numeroRUC = isset($data['numeroRUC']) ? $data['numeroRUC'] : null;
    $this->digitoVerificadorRUC = isset($data['digitoVerificadorRUC']) ? $data['digitoVerificadorRUC'] : null;
    $this->razonSocial = isset($data['razonSocial']) ? $data['razonSocial'] : null;
    $this->direccion = isset($data['direccion']) ? $data['direccion'] : null;
    $this->codigoUbicacion = isset($data['codigoUbicacion']) ? $data['codigoUbicacion'] : null;
    $this->corregimiento = isset($data['corregimiento']) ? $data['corregimiento'] : null;
    $this->distrito = isset($data['distrito']) ? $data['distrito'] : null;
    $this->provincia = isset($data['provincia']) ? $data['provincia'] : null;
    $this->telefono1 = isset($data['telefono1']) ? $data['telefono1'] : null;
    $this->telefono2 = isset($data['telefono2']) ? $data['telefono2'] : null;
    $this->telefono3 = isset($data['telefono3']) ? $data['telefono3'] : null;
    $this->correoElectronico1 = isset($data['correoElectronico1']) ? $data['correoElectronico1'] : null;
    $this->pais = isset($data['pais']) ? $data['pais'] : "PA";
    $this->paisOtro = isset($data['paisOtro']) ? $data['paisOtro'] : null;
  }
  // getters
  public function getTipoClienteFE() { return $this->tipoClienteFE; }
  public function getTipoContribuyente() { return $this->tipoContribuyente; }
  public function getNumeroRUC() { return $this->numeroRUC; }
  public function getDigitoVerificadorRUC() { return $this->digitoVerificadorRUC; }
  public function getRazonSocial() { return $this->razonSocial; }
  public function getDireccion() { return $this->direccion; }
  public function getCodigoUbicacion() { return $this->codigoUbicacion; }
  public function getCorregimiento() { return $this->corregimiento; }
  public function getDistrito() { return $this->distrito; }
  public function getProvincia() { return $this->provincia; }
  public function getTelefono1() { return $this->telefono1; }
  public function getTelefono2() { return $this->telefono2; }
  public function getTelefono3() { return $this->telefono3; }
  public function getCorreoElectronico1() { return $this->correoElectronico1; }
  public function getPais() { return $this->pais; }
  public function getPaisOtro() { return $this->paisOtro; }

  // setters
  public function setTipoClienteFE($tipoClienteFE) { $this->tipoClienteFE = $tipoClienteFE; }
  public function setTipoContribuyente($tipoContribuyente) { $this->tipoContribuyente = $tipoContribuyente; }
  public function setNumeroRUC($numeroRUC) { $this->numeroRUC = $numeroRUC; }
  public function setDigitoVerificadorRUC($digitoVerificadorRUC) { $this->digitoVerificadorRUC = $digitoVerificadorRUC; }
  public function setRazonSocial($razonSocial) { $this->razonSocial = $razonSocial; }
  public function setDireccion($direccion) { $this->direccion = $direccion; }
  public function setCodigoUbicacion($codigoUbicacion) { $this->codigoUbicacion = $codigoUbicacion; }
  public function setCorregimiento($corregimiento) { $this->corregimiento = $corregimiento; }
  public function setDistrito($distrito) { $this->distrito = $distrito; }
  public function setProvincia($provincia) { $this->provincia = $provincia; }
  public function setTelefono1($telefono1) { $this->telefono1 = $telefono1; }
  public function setTelefono2($telefono2) { $this->telefono2 = $telefono2; }
  public function setTelefono3($telefono3) { $this->telefono3 = $telefono3; }
  public function setCorreoElectronico1($correoElectronico1) { $this->correoElectronico1 = $correoElectronico1; }
  public function setPais($pais) { $this->pais = $pais; }
  public function setPaisOtro($paisOtro) { $this->paisOtro = $paisOtro; }
}
