<?php

//include_once "../../permiso.php";
include_once "../../config/db.php";
$RUC = isset($_POST["ruc"]) ? $_POST["ruc"] : false;
$tipoRuc = isset($_POST["tipoRuc"]) ? $_POST["tipoRuc"] : false;
if($RUC){
    ob_clean();
    $clases = ["ConsultaRucDv", "Facturacion"];
    foreach ($clases as $clase) {
      $archivo = $clase.".php";
      require_once($archivo);
    }
    
    $facturacion = new Facturacion($base_de_datos);
    $CONFIG = $facturacion->getConfig();
    if($CONFIG["FACELECT"] == "false"){
      echo json_encode(array("estado"=> 0, "mensaje" => "Facturación Electrónica no está habilitada") );exit;
    }

    $direccion_envio = $CONFIG["DIRECCIONENVIO"];
    
    $arrayConsultaRuc = array(
        'tokenEmpresa' => $CONFIG["USUARIO_RUC"],
        'tokenPassword' => $CONFIG["CONTRASEÑA"],
        'tipoRuc' => $tipoRuc,
        'ruc' => $RUC
    );
    
    // Crear una instancia de la clase ConsultarRucDV
    $ConsultarRucDV = new ConsultarRucDV( $arrayConsultaRuc);
    $respConsultaRucDv = $ConsultarRucDV->consultarRucDv( $direccion_envio );

    if($respConsultaRucDv->ConsultarRucDVResult->codigo == "200")
    {

        $datos = array(
            'codigo' => $respConsultaRucDv->ConsultarRucDVResult->codigo,
            'infoRuc' => $respConsultaRucDv->ConsultarRucDVResult->infoRuc,
            'mensaje' => $respConsultaRucDv->ConsultarRucDVResult->mensaje,
            'resultado' => $respConsultaRucDv->ConsultarRucDVResult->resultado,
        );
      $estado = 1;
      http_response_code(200);

    }  
    else{
        http_response_code(500);
        $estado = 0;

        $mensaje = str_replace("\r\n", "", $respConsultaRucDv->ConsultarRucDVResult->mensaje );
        $mensaje = str_replace("|", "", $mensaje);

        $datos = array(
            'codigo' => $respConsultaRucDv->ConsultarRucDVResult->codigo,
            'infoRuc' => $respConsultaRucDv->ConsultarRucDVResult->infoRuc,
            'mensaje' => $mensaje,
            'resultado' => $respConsultaRucDv->ConsultarRucDVResult->resultado,
        );

    }
    header('content-type: application/json');
    echo json_encode(array("estado"=> $estado, "ConsultaRuc" => $datos));exit;




}else{
    echo "falso";
}