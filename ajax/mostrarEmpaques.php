<?php
include_once "../config/db.php";
$CODPRO = isset( $_POST["CODPRO"] ) ? $_POST["CODPRO"] : '' ;
$accion = isset( $_POST["accion"] ) ? $_POST["accion"] : '' ;
$txt_precio = isset( $_POST["precio"] ) ? $_POST["precio"] : '' ;

$txt_CodAlmacen=(int)trim($_POST['almacen']);
if($txt_CodAlmacen<=1){
    $txt_CodAlmacen="";
}
if($txt_precio == "precio"){

     $sql_EMP = "SELECT PRECIOVENTAD
     FROM BASEEMPRESA WHERE CONTROL='" . $_SESSION['id_control'] . "'";    

    $sentencia4 = $base_de_datos->prepare($sql_EMP);

    $sentencia4->execute();
    $empresa = $sentencia4->fetch(PDO::FETCH_ASSOC);
    $txt_precio  = "PRECIO".$empresa["PRECIOVENTAD"];
}
if($accion == "mostrarEmpaques"){

    $sql3 = "SELECT * FROM INVENTARIOEMPAQUESV WHERE CODPRO = '$CODPRO';";
    
    $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
    $total_reg = $result->fetchColumn();
    $sentencia4 = $base_de_datos->prepare($sql3, [
        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    if(!isset($_SESSION['tipo_tarea'])){
        session_start();
    }

    ob_clean();
    $sentencia4->execute();
    $datosJson = '{

    "data": [ ';

    foreach ($sentencia4 as $key => $value) {
        
        $sql20="SELECT CODPRO
       , CODIGO AS CODDEP
       , DESCRIP1
       , DESCRIP2
       , DESCRIP3
       , COSTOACT
       , COSTOPRO
       , EXISTENCIA$txt_CodAlmacen AS EXISTENCIA
       , CANRESERVADA$txt_CodAlmacen AS RESERVADA
       , EXENTO AS EXENTO
       , CANVEN AS CANVEN
       , $txt_precio AS Precio_fijo 
       , IMPPOR AS IMPPOR
       , GRUPOINV AS GRUPOINV
       , LINEAINV AS LINEAINV
       , TIPINV AS TIPINV
       , PROCOMPUESTO AS PROCOMPUESTO
       FROM
       INVENTARIO
       WHERE 
       ACTIVO=0 AND CODPRO = '{$CODPRO}'
       ORDER BY 
       DESCRIP1 ASC";
        //echo "$sql20";exit;

        $resp_prod = $base_de_datos->prepare($sql20);
        $resp_prod->execute();
        $data2 = $resp_prod->fetch(PDO::FETCH_ASSOC);

        
        $nombre_formateado = str_replace("'", "&prime;", $data2["DESCRIP1"]);
        $nombre_formateado = str_replace("\"", "&quot;", $nombre_formateado);
        $acciones = "<div class='btn-group'><button class='btn' style='background-color:#2b3644 !important;border:none;width:100%;' CODPRO = '".$data2["CODPRO"]."' NOMBRE= '".$nombre_formateado."' Precio_fijo ='".number_format(round($data2["Precio_fijo"], 2), 2, '.', '')."' IMPPOR ='".$data2["IMPPOR"]."' COSTOACT='".$data2["COSTOACT"]."' COSTOPRO='".$data2["COSTOPRO"]."' GRUPOINV='".$data2["GRUPOINV"]."' CODDEP='".$data2["CODDEP"]."' LINEAINV='".$data2["LINEAINV"]."' DiSPONIBLE = '".$data2["RESERVADA"]."' tipo_tarea ='".$_SESSION["tipo_tarea"]."' EXENTO='".$data2["EXENTO"]."'  TIPINV='".$data2["TIPINV"]."' PROCOMPUESTO='".$data2["PROCOMPUESTO"]."'><i class='fa fa-plus text-white'></i></button></div>";   
        //$acciones  = "<div class='btn-group'><button class='btn btn-warning'  CODPRO = '".$data2["CODPRO"]."' NOMBRE= '".$nombre_formateado."' Precio_fijo =' ".number_format(round($data2["Precio_fijo"], 2), 2, '.', '')."' IMPPOR ='".$data2["IMPPOR"]."' COSTOACT='".$data2["COSTOACT"]."' COSTOPRO='".$data2["COSTOPRO"]."' GRUPOINV='".$data2["GRUPOINV"]."' CODDEP='".$data2["CODDEP"]."' LINEAINV='".$data2["LINEAINV"]."' >X</button></div>";	

        $datosJson.= '[
                        
                    "'.$value["CODPRO"].'",
                    "'.$value["EMPAQUE"].'",
                    "'.$value["CANTIDAD_EMP"].'",
                    "'.$value["PRECIO_EMPAQUE"].'",
                    "'.$value["CONTROLEMP"].'",
                    "'.$acciones.'"
                    
            ],';
    }

    $datosJson = substr($datosJson, 0, -1);

    $datosJson.=  ']

    }';

    echo $datosJson;exit;

}