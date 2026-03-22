<?php
session_start();
include_once "permiso.php";
include_once "config/db.php";

if(!isset($_SESSION['ventamenos'])){
    session_start();
}
$codpro=trim($_GET['codpro']);
$can=trim($_GET['cantidad']);
$txt_CodAlmacen=(int)trim($_GET['codalmacen']);
$ventamenos = $_SESSION["ventamenos"];
$actfacexi  = $_SESSION["actfacexi"];
if($txt_CodAlmacen<=1){
    $txt_CodAlmacen="";
}

$sql3="SELECT CODPRO, EXISTENCIA$txt_CodAlmacen AS EXISTENCIA, CANRESERVADA$txt_CodAlmacen AS RESERVADA, PROCOMPUESTO, TIPINV  FROM INVENTARIO WHERE CODPRO='$codpro'";
//echo $sql3." --- $can";
$result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
$total_reg = $result->fetchColumn();
ob_clean();
if($total_reg!=''){
    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    $contador=0;

    while ($data2 = $sentencia4->fetchObject()){
        $disponible=$data2->EXISTENCIA-$data2->RESERVADA;
        $tipo_prod =$data2->EXISTENCIA-$data2->TIPINV;
        
        if($can<=$disponible ){
            if($_SESSION['tipo_tarea']=='presupuesto'){
                echo "1|La tarea es presupuesto";
                $contador++;
            }else{
                if( $disponible>0 || $tipo_prod == 1){
                    echo "1|Si hay producto disponible";
                    $contador++;
                }else
                {
                    if($ventamenos){
                        echo "1|Si hay producto disponible.";
                        $contador++;
                    }else{
                        echo "0|No existe producto disponible";
                        $contador++;
                    }
                }
            }
        }
        else{
            if($data2->PROCOMPUESTO == 1 OR $tipo_prod == 1 OR ($ventamenos == "1" OR $actfacexi == "1" )){
                echo "1|Producto Compuesto.";
                $contador++;

            }else{
                echo "0|Esta sobrepasando la cantidad de producto disponible";

            }
        }
    }
}/*fin IF*/          
?>