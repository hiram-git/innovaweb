<?php

include_once "permiso.php";
include_once "config/db.php";

$accion = $_POST['accion'];
if($accion == "grabarCobro"){
    $control = $_POST['control'];
    $codigo = $_POST['codigo'];
    $formasPago   = $_POST['formasPago'];
    $ref   = $_POST['ref'];
    $formas_pago = json_decode($formasPago, true);
    
    $sql3 = "SELECT PREFAC_CONFIN , NROFAC_CONFIN,PREFAC_CONTRI , NROFAC_CONTRI,PREFAC_GUBER , NROFAC_GUBER,PREFAC_REGESP , NROFAC_REGESP,NROINIFAC, NROINIPRE, NROPEDCLI, MONEDA, NROINIREC
    FROM BASEEMPRESA WHERE CONTROL='" . trim($_SESSION['id_control']) . "'";
    
    $sentencia4 = $base_de_datos->prepare($sql3, [
       PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);
    
    $sentencia4->execute();
    $result100 = $sentencia4->fetch(PDO::FETCH_ASSOC);
    
    $nro_pago = (int)$result100['NROINIREC'];
    $nro_pago = str_pad($nro_pago,10,"0",STR_PAD_LEFT);
    
    $tip_tran = "FAC";
    
    $MONTOTAR   = 0;
    $MONTOCHE   = 0;
    $MONTOINTS1 = 0;
    $MONTOINTS2 = 0;
    $MONTOEFE   = 0;
    
    
    $pagado = 0;
    foreach ($formas_pago as $forma_pago ) {
        $sql_fp = "SELECT b.CODTAR , b.NOMBRE , b.FUNCION  FROM BASEINSTRUMENTOS b WHERE b.CODTAR = '".$forma_pago['id']."';";
        $result_fp = $base_de_datos->query($sql_fp);
        $INTRUMENTO = $result_fp->fetch(PDO::FETCH_ASSOC);
        $pagado = $pagado + $forma_pago["value"];
        
        switch ( $INTRUMENTO["FUNCION"] ) {
            case 0:
                $MONTOTAR += $forma_pago["value"];
                break;
            case 1:
                $MONTOCHE += $forma_pago["value"];
                break;
            case 2:
                $MONTOINTS1 += $forma_pago["value"];
                break;
            case 3:
                $MONTOINTS1 += $forma_pago["value"];
                break;
            case 4:
                $MONTOINTS2 += $forma_pago["value"];
                break;
            case 6:
                $MONTOEFE += $forma_pago["value"];
                break;
            
            default:
                # code...
                break;
        }
    
    }
    $PAGO = $MONTOTAR +$MONTOCHE + $MONTOINTS1 + $MONTOINTS2 + $MONTOEFE;

    $control_fp  = cadena_control();
    $exp_control_fp = explode('|', $control_fp);

    $dias_fp                 = $exp_control_fp[0];
    $hora_actual_fp          = $exp_control_fp[1];
    $aleatorio_fp            = $exp_control_fp[2];
    $fecha_actual_clarion_fp = $exp_control_fp[3];
    $fecha_actual_ymd_fp     = $exp_control_fp[4];
    $hora_actual_clarion_fp  = $exp_control_fp[1];
    $pago_contro_uni = "$dias_fp$hora_actual_fp$aleatorio_fp" . "01";
    

    $sql_factura = "SELECT * FROM TRANSACCMAESTRO WHERE NUMREF = '{$ref}' AND TIPTRAN = 'FAC' AND CODIGO = '{$codigo}' AND CONTROL = '{$control}';";;
    $result_factura = $base_de_datos->query($sql_factura);
    $valores_pago = $result_factura->fetch(PDO::FETCH_ASSOC);
    //Se se usa el mismo array de maestro para grabar el cobro
    $valores_pago["CONTROL"]            = "$pago_contro_uni";
    $valores_pago["TIPREG"]             = (int)$valores_pago["TIPREG"];
    $valores_pago["MARCA"]              = (int)$valores_pago["MARCA"];
    $valores_pago["RIF"]                = str_replace("'", "''", $valores_pago["RIF"]);
    $valores_pago["CODIGO"]             = str_replace("'", "''", $valores_pago["CODIGO"]);
    
    $valores_pago["CONTROLDOC"]         = "$control";
    $valores_pago["CONTROLCXCCXP"]      = "$pago_contro_uni";
    $valores_pago["TIPTRAN"]            = "PAGxFAC";
    $valores_pago["DESCRIP1"]           = "CxC Cobro factura  $ref";
    $valores_pago["DESCRIP2"]           = "Por ".round($PAGO, 2)."";
    $valores_pago["DIASVEN"]            = 0;
    $valores_pago["MONTOBRU"]           = round($PAGO, 2);
    $valores_pago["MONTOSUB"]           = round($PAGO, 2);
    $valores_pago["MONTOTOT"]           = round($PAGO, 2);
    $valores_pago["MONTOIMP"]           = null;
    $valores_pago["CONTADOR"]           = 2;
    $valores_pago["TOTCONTADOR"]        = 0;
    $valores_pago["MONTOPAGF"]          = null;
    $valores_pago["MONTOCOS"]           = null;
    $valores_pago["MONTOSAL"]           = null;
    $valores_pago["PORIMP"]             = null;
    $valores_pago["COMISV"]             = 0;
    $valores_pago["FECHAEMISIONCOMPRA"] = 0;
    $valores_pago["DIASVEN"]            = 0;
    $valores_pago["FACTORCAMBIO"]       = 1;
    $valores_pago["HORA"]               = "$hora_actual_clarion_fp";
    $valores_pago["BASEIMPONIBLEIVA"]   = null;
    $valores_pago["NROCONTRATO"]        = null;
    //$valores_pago["TIPOFACTURA"]        = $valores_pago["TIPOFACTURA"];
    $valores_pago["TIPODOC"]        = 1;
    $valores_pago["FECULTIMOPAGO"]      = (int)$valores_pago["FECULTIMOPAGO"];
    $valores_pago["FECHAENTREGA"]       = (int)$valores_pago["FECHAENTREGA"];
    $valores_pago["FECEMIS"]       = (int)$valores_pago["FECEMIS"];
    $valores_pago["FECVENC"]       = (int)$valores_pago["FECVENC"];
    $valores_pago["FECDOCORIG"]       = (int)$valores_pago["FECEMIS"];
    $valores_pago["FECVENCDOCORIG"]       = (int)$valores_pago["FECVENC"];
    $valores_pago["NUMDOC"]           = "$nro_pago";

    //$valores_pago["HORA"]       = $valores_pago["HORA"]; 

    foreach ($valores_pago as $clave => $valor) {
        if ($valor === null || $valor === "") {
            unset($valores_pago[$clave]);
        }
    }

    $campos_insert_pago = implode(", ", array_keys($valores_pago));
    $fcall = function($valor, $indice ) {
        
        $indices_float = array("BASEIMPONIBLE","BASEIMPONIBLEIVA", "BASEPARARET_ISLR","FACTORCAMBIO","MONTOPAGF","MONTOCOS","MONTOSAL","PORIMP","MONTOIMP","MARCARE","COM_FISCAL_Z","FUNCIONTAR","FUNCIONINSTRU1","FUNCIONINSTRU2");
        $indices_int = array("TIPREG", "TOTCONTADOR", "FECULTIMOPAGO","FECHAENTREGA","FECEMIS", "FECVENC","MARCA","HORA", "TIPREG","COMISV","COMISC","ACTBANCO","OTRAPLAZA","DIASVEN","TEMP_BASE_IMP","TIPODOC","FECHAEMISIONCOMPRA");
        if (in_array($indice, $indices_float )) {
            return (float)$valor; 
        }
        if (in_array($indice, $indices_int )) {
            return (int)$valor; 
        }
        switch($valor){
            case "":
                $valor = "''";
            break;
            case ".00":
                $valor = "0.00";
            break;
            case null:
                $valor = "NULL";
            break;
            default:
                if(gettype($valor)== "string"){
                    $valor = "'".$valor."'";

                }else{
                    if(gettype($valor)== "int"){
                        $valor = (int)$valor;
    
                    }else{
                        $valor = $valor;
                    }
                }
            break;

        }
        return $valor;
    };
    $array_mapeado = array_map($fcall, $valores_pago, array_keys($valores_pago));
    $result = array_combine(array_keys($valores_pago), $array_mapeado);

    $sql_insert_pago = implode(", ", $array_mapeado);

    $sql_pago = "INSERT INTO TRANSACCMAESTRO
    ($campos_insert_pago)
    VALUES ($sql_insert_pago) ";
    //echo "<br /><br />".$sql_pago;exit;
    $sentencia_pago = $base_de_datos->prepare("$sql_pago");
    $sentencia_pago->execute();



    $sql_update_maestro = "UPDATE TRANSACCMAESTRO SET MONTOSAL = MONTOSAL - ".(round($PAGO, 2))." WHERE NUMREF = '{$ref}' AND TIPTRAN = 'FAC';";
    //echo "<br /><br />".$sql_update_maestro;exit;
    $sentencia_upd = $base_de_datos->prepare("$sql_update_maestro");
    $sentencia_upd->execute();



    $query_comp = "UPDATE BASEEMPRESA SET NROINIREC = (NROINIREC + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "'";
    $sentencia_comp = $base_de_datos->prepare("$query_comp");
    $resultado4 = $sentencia_comp->execute();
    

    echo "Cobro registrado exitosamente";exit;
    
    

}


function cadena_control()
{    
    usleep(100000);
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime(date("Y-m-d H:i:s"));

    $diff = $fecha1->diff($fecha2);
    $dias = $diff->days;
    $fecha_actual_clarion = $diff->days;
    $fecha_actual_ymd = date("Ymd");
    
    $hora_actual = (date('H') * 360000) + (date("i") * 6000) + (date("s") * 100) + (date("v") / 10) + 1;
    if (strlen($hora_actual) == 7) {
    } else if (strlen($hora_actual) > 7) {
        $hora_actual = substr($hora_actual, 0, 7);
    } else if (strlen($hora_actual) < 7) {
        $res = 7 - strlen($hora_actual);
        for ($t = 1; $t <= $res; $t++) {
            $hora_actual = "0" . $hora_actual;
        }
    }
    
    $aleatorio = str_pad(mt_rand(90000, 94999), 5, "0", STR_PAD_LEFT);

    return "$dias|$hora_actual|$aleatorio|$fecha_actual_clarion|$fecha_actual_ymd";
}
