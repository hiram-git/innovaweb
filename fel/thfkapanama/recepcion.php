<?php
//include_once "../../permiso.php";
include_once "../../config/db.php";
ob_clean();

// Array con los datos a insertar
$datos = array(
    "CONTROL" => $control,
    "CODIGO" => $enviarResult->codigo,
    "RESULTADO" => $enviarResult->resultado,
    "MENSAJE" => $enviarResult->mensaje,
    "CUFE" => $enviarResult->cufe,
    "QR" => $enviarResult->qr,
    "FECHARECEPCIONDGI" => $enviarResult->fechaRecepcionDGI,
    "NROPROTOCOLOAUTORIZACION" => $enviarResult->nroProtocoloAutorizacion,
    "FECHALIMITE" => $enviarResult->fechaLimite
);

// Preparar la consulta de inserción
$sql = "INSERT INTO DocumentosElectronicos (CONTROL, CODIGO, RESULTADO, MENSAJE, CUFE, QR, FECHARECEPCIONDGI, NROPROTOCOLOAUTORIZACION, FECHALIMITE)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la sentencia de inserción

$stmt = $base_de_datos->prepare($sql);
// Verificar si hay error en la preparación de la sentencia
if ($stmt === false) {
    die("Error en la preparación de la sentencia: " . $conn->error);
}

// Bindear los parámetros de la sentencia
$stmt->bind_param($control, $datos['CODIGO'], $datos['RESULTADO'], $datos['MENSAJE'], $datos['CUFE'], $datos['QR'], $datos['FECHARECEPCIONDGI'], $datos['NROPROTOCOLOAUTORIZACION'], $datos['FECHALIMITE']);

// Ejecutar la sentencia
if ($stmt->execute()) {
    $respuesta = array("1", "Inserción exitosa");
} else {
    $respuesta = array("0", "Error en la inserción: " . $stmt->error);
}
// Cerrar la conexión y liberar recursos
$stmt->close();
$base_de_datos->close();

return $respuesta;