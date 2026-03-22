<?php
include_once "../config/db.php";
/*
try {
    $base_de_datos = new PDO("sqlsrv:server=".SYS_HOST.";database=".SYS_BBDD, SYS_USER, SYS_PASS);
    $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Ocurri&oacute; un error con la conexi&oacute;n a Base de Datos: " . $e->getMessage();
}*/

/*recuperando todo los productos comandados*/
$sql3="SELECT a.NOMBRE, a.CODIGO FROM BASECLIENTESPROVEEDORES  as a WHERE (a.TIPREG = 1)";
$sentencia4 = $base_de_datos->prepare($sql3, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
]);
$arenita="";
$sentencia4->execute();
while ($data2 = $sentencia4->fetchObject()){
    /*Arenita la amiga de bob esponja*/
    $arenita.=$data2->NOMBRE."|";
    $arenita.=$data2->CODIGO."|";
}
echo $arenita;
?>