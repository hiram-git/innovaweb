<?php
session_start();
include_once "../../config/db.php";
require_once("Documento.php");

$control_encode = $_POST["control"];

$digifact = new Documento($base_de_datos);
$CONTROL  = base64_decode($control_encode);
$response = $digifact->enviarDocDigifact( $CONTROL, $_SESSION["id_control"] ?? 1 );

$responsexml = $response["xml"];
$respuesta = array();
$respuesta["codigo"]  = $response["codigo"];
$respuesta["mensaje"] = $response["mensaje"];
ob_clean();
if ($response["codigo"] == 1) {    
  echo json_encode(array("estado"=> $response["codigo"],
   "mensaje" => $response,
   //"respWS"=> $response,
   "PDF"=> ""));
}
else{    
  echo json_encode(array("estado"=> $response["codigo"],
   "mensaje" => $response,
   //"respWS"=> $response,
   "PDF"=> ""));
}


?>