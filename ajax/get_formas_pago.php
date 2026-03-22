<?php
include_once "../config/db.php";


$sql3 = "SELECT CODTAR , NOMBRE, FUNCION  FROM BASEINSTRUMENTOS WHERE CODTAR IN ( '01', '02', '03', '04', '05', '06');";
//echo $sql3;
$result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
$total_reg = $result->fetchColumn();
$sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
]);

ob_clean();
$sentencia4->execute();
http_response_code(200);
header('content-type: application/json');
echo json_encode( $sentencia4->fetchAll(PDO::FETCH_ASSOC) );
