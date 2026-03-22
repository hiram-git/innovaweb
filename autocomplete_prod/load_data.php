<?php
include_once "../config/db.php";
/*recuperando todo los productos comandados*/
$sql3="SELECT CODPRO, DESCRIP1 FROM INVENTARIO WHERE (PRECIO1>0 OR PRECIO2>0 OR PRECIO3>0)";
$sentencia4 = $base_de_datos->prepare($sql3, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
]);
$arenita="";
$sentencia4->execute();
while ($data2 = $sentencia4->fetchObject()){
    /*Arenita la amiga de bob esponja*/
    $arenita.=$data2->CODPRO."|";
    $arenita.=$data2->DESCRIP1."|";
}
echo $arenita;
?>