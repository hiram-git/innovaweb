<?php
include_once "permiso.php";
//include_once "config/db.php";
$tipo=$_GET['tipo'];
$valor=$_GET['valor'];
$arr=explode("|", $valor);
if($tipo=='vendedor'){
    $_SESSION['codvendedor_opt'] = $arr[0];
}else if($tipo=='almacen'){
    $_SESSION['codalmacen_opt'] = $arr[0];
}
?>