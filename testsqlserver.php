<?php
phpinfo();exit;

$clave = "Innovasa01$";
$usuario = "sa";
$nombreBaseDeDatos = "xanto";
$rutaServidor = "nmspanama.servebbs.com";
/*
$clave = "12345678";
$usuario = "sa";
$nombreBaseDeDatos = "InnovaWeb";
$rutaServidor = "DESKTOP-3UKE86V\SQLEXPRESS";*/

$serverName = $rutaServidor;
$connectionOptions = array(
    "Database" => $nombreBaseDeDatos,
    "Uid" => $usuario,
    "PWD" => $clave
);

// Intentar establecer la conexión
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

echo "Conexión exitosa a SQL Server!";

// Cerrar la conexión
sqlsrv_close($conn);

?>
