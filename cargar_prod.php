<?php 
include_once("permiso.php");
include_once "config/db.php";
//session_start();
$codigo         = str_replace("%23", "#", $_GET['codigo']);
$nombre         = $_GET['nombre'];
$nombre         = str_replace("|", "+", $nombre);
$nombre         = str_replace("%23", "#", $nombre);
$precio         = $_GET['precio'];
$precio_noformt = $_GET['precio_noformt'];
$descuento      = $_GET['descuento'];
$cantidad       = $_GET['cantidad'];
$itbm           = $_GET['itbm'];
$costoact       = $_GET['costoact'];
$costopro       = $_GET['costopro'];
$grupoinv       = $_GET['grupoinv'];
$coddep         = $_GET['coddep'];
$lineainv       = $_GET['lineainv'];
$codalmacen     = $_GET['codalmacen'];
$codvend        = $_GET['codvend'];
$nomvend        = $_GET['nomvend'];
$exento         = $_GET['exento'];
$codempaque         = $_GET['codempaque'];
$nota           = substr($_GET['nota'], 0, 79);
$nota           = str_replace("%23", "#", $nota);
$tipocliente    = $_GET['tipocliente'];
$precio_sel     = $_GET['precio_sel'];
$array_tipocli = array("Exento", "Otros exentos", "Contribuyente exento", "Consumidor Final exento", "Gubernamental exento");

if (in_array("$tipocliente", $array_tipocli)) {
    $exento=1;
    $itbm=0.00;
}
// procedemos a insertar items
//$items_total=count($_SESSION['aDatos']);
$b=array(
    "codigo"         => "$codigo",
    "nombre"         => "$nombre",
    "precio"         => "$precio",
    "descuento"      => "$descuento",
    "cantidad"       => "$cantidad",
    "itbm"           => "$itbm",
    "costoact"       => "$costoact",
    "costopro"       => "$costopro",
    "grupoinv"       => "$grupoinv",
    "coddep"         => "$coddep",
    "lineainv"       => "$lineainv",
    "precio_noformt" => "$precio_noformt",
    "codalmacen"     => "$codalmacen",
    "codvend"        => "$codvend",
    "nomvend"        => "$nomvend",
    "exento"         => "$exento",
    "codempaque"     => "$codempaque",
    "nota"           => "$nota",    
    "precio_sel"     => "$precio_sel",    
    "codcomp"        => "$codcomp"
);
array_push($_SESSION['aDatos'],$b);

$t_cant=0;
$t_subt=0;
$max=sizeof($_SESSION['aDatos']);
for($i=0; $i<$max; $i++) {
    $k=0; 
    foreach ($_SESSION['aDatos'][$i] as $key=> $val){
        $k++;
        if($k==1){ // codigo del producto
            $cod_prod=$val;
        }else if($k==2){ // nombre del producto
            $nom_prod=$val;
        }else if($k==3){ // precio del producto
            $precio=$val;
        }else if($k==4){ // cantidad del producto
            $descuento=$val;
        }else if($k==5){ // itbm
            $cantidad=$val;
        }else if($k==6){ // itbm
            $itbm=$val;
        }
    } // fin foreach
    $t_cant+=$cantidad;
    $t_subt+=$cantidad*$precio;
}
echo "$max|$t_cant|$t_subt";
?>