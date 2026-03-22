<?php
include_once "../config/db.php";
$codigo = isset( $_POST["codigo"] ) ? $_POST["codigo"] : '' ;
$accion = isset( $_POST["accion"] ) ? $_POST["accion"] : '' ;
$nomcliente = isset( $_POST["nomcliente"] ) ? $_POST["nomcliente"] : '' ;

if($accion == "mostrarOT"){

    $sql3 = "SELECT T.CONTROL, T.NOMBRE, BC.NUMTEL, BC.DIRCORREO, T.CODVEN, '' ESTADO FROM TRANSACCMAESTRO AS T 
    LEFT JOIN BASECLIENTESPROVEEDORES BC ON T.CODIGO = BC.CODIGO
    WHERE T.TIPTRAN IN ('PEDxCLI', 'PRE') AND T.CODIGO = '{$codigo}'";
    
    $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
    $total_reg = $result->fetchColumn();
    $sentencia4 = $base_de_datos->prepare($sql3, [
        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);



    ob_clean();
    $sentencia4->execute();
    $datosJson = '{

    "data": [ ';

    foreach ($sentencia4 as $key => $value) 
    {
        $nombre_formateado = $value["NOMBRE"];
        $nombre_formateado = str_replace("\"", "&quot;", $nombre_formateado);
        $acciones = "<div class='btn-group'><button class='btn btn-primary btnAgregarOT' CONTROL = '".$value["CONTROL"]."' NOMBRE= '".$nombre_formateado."' ><i class='fa fa-plus text-white'></i></button></div>";   

        $datosJson.= '[                        
            "'.$value["CONTROL"].'",
            "'.$nombre_formateado.'",
            "'.$value["NUMTEL"].'",
            "'.$value["DIRCORREO"].'",
            "'.$value["CODVEN"].'",
            "'.$value["ESTADO"].'",
            "'.$acciones.'"
            ],';
    }

    $datosJson = substr($datosJson, 0, -1);

    $datosJson.=  ']

    }';

    echo $datosJson;exit;

}