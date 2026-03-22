<?php

class Facturacion {
  public $base_de_datos;

  public function __construct(PDO $base_de_datos) {
    $this->base_de_datos = $base_de_datos;
  }

  public function getEmpresa() {
    $sql_EMP = "SELECT USUARIO_RUC, CONTRASEÑA, CODSUC, CODPFACT, NEMPRESA_DIGI, TEL_DIGI, RUC_DIGI, DV_DIGI, JURIDICO_DIGI, DIRECCION_DIGI, UBUCACION_DIGI, COORDENADAS_DIGI, EMAIL_DIGI, FACELECT
                FROM FELINNOVA;";    

    $sentencia4 = $this->base_de_datos->prepare($sql_EMP);
    $sentencia4->execute();
    return $sentencia4->fetch(PDO::FETCH_ASSOC);
  }
  public function getFacturaMaestro($CONTROL) {
    $sql_MAESTRO = "SELECT * FROM TRANSACCMAESTRO WHERE CONTROL = '".$CONTROL."' ;";
    $res_fact_maestro = $this->base_de_datos->prepare($sql_MAESTRO);
    $res_fact_maestro->execute();
    return $res_fact_maestro->fetch(PDO::FETCH_ASSOC);
  }
  public function getFacturaMaestroRef($NUMREF) {
    $sql_MAESTRO = "SELECT * FROM TRANSACCMAESTRO WHERE NUMREF = '".$NUMREF."' AND TIPTRAN = 'FAC';";
    $res_fact_maestro = $this->base_de_datos->prepare($sql_MAESTRO);
    $res_fact_maestro->execute();
    return $res_fact_maestro->fetch(PDO::FETCH_ASSOC);
  }
  public function getCobrosMaestro($NUMREF) {
    $sql_MAESTRO = "SELECT * FROM TRANSACCMAESTRO WHERE NUMREF = '$NUMREF' AND TIPTRAN = 'PAGxFAC';";
    $res_fact_maestro = $this->base_de_datos->prepare($sql_MAESTRO);
    $res_fact_maestro->execute();
    return $res_fact_maestro->fetchAll(PDO::FETCH_ASSOC);
  }
  public function getRetencionMaestro( $numdoc) {
    $sql_MAESTRO = "SELECT * FROM TRANSACCMAESTRO WHERE NUMDOC = '$numdoc' AND TIPTRAN = 'N/CxIMP';";
    $res_fact_maestro = $this->base_de_datos->prepare($sql_MAESTRO);
    $res_fact_maestro->execute();
    return $res_fact_maestro->fetch(PDO::FETCH_ASSOC);
  }
  public function getCliente($codigo) {
    $sql_CLIENTE = "SELECT
      cp.* ,
      bp.DESNOMBREEGEO1 geo1,
      bd.DESNOMBREEGEO2 geo2,
      bc.DESNOMBREEGEO3 geo3 
    FROM
      BASECLIENTESPROVEEDORES cp
      LEFT JOIN BASEPROVINCIA bp ON cp.NOMBREEGEO1 = bp.NOMBREEGEO1
      LEFT JOIN BASEDISTRITO bd ON bd.NOMBREEGEO2 = cp.NOMBREEGEO2 	AND bd.NOMBREEGEO1 = cp.NOMBREEGEO1
      LEFT JOIN BASECORREGIMIENTO bc ON bc.NOMBREEGEO3 = cp.NOMBREEGEO3 AND bc.NOMBREEGEO2 = cp.NOMBREEGEO2 AND bc.NOMBREEGEO1 = cp.NOMBREEGEO1 WHERE cp.CODIGO = '{$codigo}' 
      AND cp.TIPREG = '1' AND cp.INTEGRADO = '0';";
    $res_fact_cliente = $this->base_de_datos->prepare($sql_CLIENTE);
    $res_fact_cliente->execute();
    return $res_fact_cliente->fetch(PDO::FETCH_ASSOC);
  }

  public function getDetalleFactura($CONTROL) {
    $sql_DETALLE = "SELECT * FROM TRANSACCDETALLES WHERE CONTROL = '".$CONTROL."' AND COMPONENTE = 0 
														ORDER BY FECHORA DESC;";
    $res_fact_detalle = $this->base_de_datos->prepare($sql_DETALLE);
    $res_fact_detalle->execute();
    return $res_fact_detalle->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPagos($CONTROL) {
    $sql_PAGOS = "SELECT *, b.NOMBRE AS DESCRIP_PAGO FROM TRANSACCPAGOS p LEFT JOIN BASEINSTRUMENTOS b ON b.CODTAR = p.CODTAR WHERE p.CONTROL = '".$CONTROL."' ORDER BY b.FUNCION;";
    $res_fact_pagos = $this->base_de_datos->prepare($sql_PAGOS);
    $res_fact_pagos->execute();
    return $res_fact_pagos->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getDocumentos($CONTROL) {
    $sql_documentos = "SELECT * FROM Documentos WHERE CONTROL = '".$CONTROL."' ;";
    $res_doc = $this->base_de_datos->prepare($sql_documentos);
    $res_doc->execute();
    return $res_doc->fetch(PDO::FETCH_ASSOC);
  }


  public function getAmpliadaDetalle($FECHORA) {
    $sql_documentos = "SELECT * FROM TRANSACCAMPLIADA WHERE FECHORA = '".$FECHORA."' ;";
    $res_doc = $this->base_de_datos->prepare($sql_documentos);
    $res_doc->execute();
    return $res_doc->fetch(PDO::FETCH_ASSOC);
  }

  public function getProdDetalle($CODPRO) {
    $sql_documentos = "SELECT * FROM INVENTARIO WHERE CODPRO = '".$CODPRO."' ;";
    $res_doc = $this->base_de_datos->prepare($sql_documentos);
    $res_doc->execute();
    return $res_doc->fetch(PDO::FETCH_ASSOC);
  }

  public function getProdCompuesto( $EMPAQUE ) {
    $sql_emp = "SELECT * FROM INVENTARIOEMPAQUESV  WHERE EMPAQUE = '".$EMPAQUE."';";
    $res_emp = $this->base_de_datos->prepare($sql_emp);
    $res_emp->execute();
    return $res_emp->fetch(PDO::FETCH_ASSOC);
  }


  public function getAdenda($CONTROL) {
    $sql_documentos = "SELECT * FROM TRANSACCOBSERVACIONES WHERE CONTROL = '".$CONTROL."' ;";
    $res_doc = $this->base_de_datos->prepare($sql_documentos);
    $res_doc->execute();
    return $res_doc->fetch(PDO::FETCH_ASSOC);
  }

  public function getVendedor($CODVEN) {
    $sql_documentos = "SELECT * FROM BASEVENDEDORES WHERE CODVEN = '".$CODVEN."' ;";
    $res_doc = $this->base_de_datos->prepare($sql_documentos);
    $res_doc->execute();
    return $res_doc->fetch(PDO::FETCH_ASSOC);
  }

  public function getConfig( $PARCONTROL ) {
    $sql_FACT_CONFIG = "SELECT A.*, B.NROINIFAC FROM FELINNOVA as A LEFT JOIN BASEEMPRESA AS B ON B.CONTROL = A.PARCONTROL WHERE  A.PARCONTROL = '".trim($PARCONTROL)."'";;
    $res_CONFIG = $this->base_de_datos->prepare($sql_FACT_CONFIG);
    $res_CONFIG->execute();
    return $res_CONFIG->fetch(PDO::FETCH_ASSOC);
  }

  public function getEmpresaCod( $codigo) {
    $sql_EMPRESA = "SELECT * FROM BASEEMPRESA WHERE CONTROL = '".$codigo."';";
    $res_EMPRESA = $this->base_de_datos->prepare($sql_EMPRESA);
    $res_EMPRESA->execute();
    return $res_EMPRESA->fetch(PDO::FETCH_ASSOC);
  }
  public function insertarDocumento($respWsPa) {
    $datos = array(
        'codigo' => $respWsPa->EnviarResult->codigo,
        'resultado' => $respWsPa->EnviarResult->resultado,
        'mensaje' => $respWsPa->EnviarResult->mensaje,
        'cufe' => !empty($respWsPa->EnviarResult->cufe) ? $respWsPa->EnviarResult->cufe : '',
        'qr' => !empty($respWsPa->EnviarResult->qr) ? $respWsPa->EnviarResult->qr : '',
        'fechaRecepcionDGI' => !empty($respWsPa->EnviarResult->fechaRecepcionDGI) ? $respWsPa->EnviarResult->fechaRecepcionDGI : '',
        'nroProtocoloAutorizacion' => !empty($respWsPa->EnviarResult->nroProtocoloAutorizacion) ? $respWsPa->EnviarResult->nroProtocoloAutorizacion : '',
        'fechaLimite' => !empty($respWsPa->EnviarResult->fechaLimite) ? $respWsPa->EnviarResult->fechaLimite : '',
        'control' => !empty($respWsPa->EnviarResult->control) ? $respWsPa->EnviarResult->control : ''
    );

    $sql = "INSERT INTO Documentos (CODIGO, CONTROL, RESULTADO, MENSAJE, CUFE, QR, FECHARECEPCIONDGI, NROPROTOCOLOAYTORIZACION, FECHALIMITE)
        VALUES (:codigo, :control, :resultado, :mensaje, :cufe, :qr, :fechaRecepcionDGI, :nroProtocoloAutorizacion, :fechaLimite)";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($datos);
}
}
/*
$empresa = $facturacion->getEmpresa();
$maestro = $facturacion->getFacturaMaestro($CONTROL);
$cliente = $facturacion->getCliente($maestro["CODIGO"]);
$detalleFactura = $facturacion->getDetalleFactura($CONTROL);
$pagos = $facturacion->getPagos($CONTROL);
$config = $facturacion->getConfig();*/