<?php
include_once "../config/db.php";
$accion = isset( $_POST["accion"] ) ? $_POST["accion"] : '' ;
if($accion == "mostrarListadoCobros"){

    $codigo  = isset( $_POST["codigo"] ) ? $_POST["codigo"] : '' ;
    $cliente = isset( $_POST["cliente"] ) ? $_POST["cliente"] : '' ;
    $control = isset( $_POST["control"] ) ? $_POST["control"] : '' ;
    $sql3 = "SELECT * FROM TRANSACCMAESTRO t WHERE t.TIPTRAN = 'PAGxFAC' AND  CODIGO = '".$cliente."' AND CONTROLDOC='".$control."';";
    
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
        
        $acciones="";
        $acciones="<div class='btn-group'>";
        $acciones.= "<a class='btn btn-danger btnImprimirDocumento'  CONTROL = '".$value["CONTROL"]."' ><i class='fa fa-file-pdf text-white'></i></a>";   

        
        $acciones.="</div>";
        $fecha1 = new DateTime($value["FECEMISS"]);   
        
        $fecha_formateada = $fecha1->format("Y-m-d");

        $datosJson.= '[                        
            "'.$fecha_formateada.'",
            "'.$value["DESCRIP1"]." ".$value["DESCRIP2"].'",
            "'.$value["MONTOTOT"].'"
        ],';
    }

    $datosJson = substr($datosJson, 0, -1);

    $datosJson.=  ']

    }';

    echo $datosJson;exit;

}