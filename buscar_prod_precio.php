<?php
include_once "permiso.php";
include_once "config/db.php";
$txt_precio=trim($_GET['t_precio']);
$txt_codigo=trim($_GET['codigo']);

$sql3="SELECT $txt_precio AS Precio_fijo FROM INVENTARIO WHERE CODPRO='$txt_codigo'";
//echo $sql3;
$result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
$total_reg = $result->fetchColumn();
if($total_reg!=''){
    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    while ($data2 = $sentencia4->fetchObject()){
        echo $data2->Precio_fijo;
    }
}else{
    echo 0.00;
}
?>