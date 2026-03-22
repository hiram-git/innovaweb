<?php 
//include_once "permiso.php";
session_start();
include_once "config/db.php";
//session_start();
$prov=$_GET['txt_provincia'];
$dist=$_GET['txt_distrito'];
$tipo=$_GET['tipo'];
if($tipo==1){
    /*recuperando todo los productos comandados*/
    $sql3="SELECT * FROM BASEDISTRITO WHERE NOMBREEGEO1='$prov'";
    //echo $sql3;

    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    echo "<select class='browser-default custom-select' name='distrito' id='distrito' onChange='cargar_corregimiento(distrito.value);'>";
    echo "<option value='' disabled selected hidden>Distrito</option>";
    while ($data2 = $sentencia4->fetchObject()){
      //$pormaxdespar=$data2->PORMAXDESPAR;
      echo "<option value='".$data2->NOMBREEGEO2."'>".$data2->DESNOMBREEGEO2."</option>";
    }
    echo "</select>";
}else{
    /*recuperando todo los productos comandados*/
    $sql3="SELECT * FROM BASECORREGIMIENTO WHERE NOMBREEGEO1='$prov' AND NOMBREEGEO2='$dist'";
    //echo $sql3;

    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    echo "<select class='browser-default custom-select' name='corregimiento' id='corregimiento'>";
    echo "<option value='' disabled selected hidden>Corregimiento</option>";
    while ($data2 = $sentencia4->fetchObject()){
      //$pormaxdespar=$data2->PORMAXDESPAR;
      echo "<option value='".$data2->NOMBREEGEO3."'>".$data2->DESNOMBREEGEO3."</option>";
    }
    echo "</select>";
}
?>