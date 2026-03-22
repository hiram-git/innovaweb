<?php 
include_once("permiso.php");
include_once "config/db.php";
//session_start();
$codigo=str_replace("%23", "#", $_GET['codigo']);
$nombre=$_GET['nombre'];
$nombre = str_replace("|", "+", $nombre);
$nombre = str_replace("%23", "#", $nombre);
$precio=$_GET['precio'];
$precio_noformt=$_GET['precio_noformt'];
$descuento=$_GET['descuento'];
$cantidad=$_GET['cantidad'];
$itbm=$_GET['itbm'];
$costoact=$_GET['costoact'];
$costopro=$_GET['costopro'];
$grupoinv=$_GET['grupoinv'];
$coddep=$_GET['coddep'];
$lineainv=$_GET['lineainv'];
$iditem=$_GET['iditem'];
$codempaque=$_GET['codempaque'];
$nota=substr($_GET['nota'], 0, 79);
$nota = str_replace("%23", "#", $nota);

$k=0; 
foreach ($_SESSION['aDatos'][$iditem] as $key=> $val){
    if($k==2){ 
        $_SESSION['aDatos'][$iditem]['precio']=$precio;
    }else if($k==3){ 
        $_SESSION['aDatos'][$iditem]['descuento']=$descuento;
    }else if($k==4){ 
        $_SESSION['aDatos'][$iditem]['cantidad']=$cantidad;
    }else if($k==16){ 
        $_SESSION['aDatos'][$iditem]['nota']=$nota;
    }
    $k++;
} // fin foreach
?>