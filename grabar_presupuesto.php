<?php
include_once "permiso.php";
include_once "config/db.php";

//$id_sistema = $_SESSION['id_sistema'];

$dsn = "sqlsrv:Server=$rutaServidor;Database=$nombreBaseDeDatos";

try {
    $base_de_datos = new PDO($dsn, $usuario, $clave);
    $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    AuditLog("Connection Error: " . $e->getMessage());
    die("Error de conexión: " . $e->getMessage());
}

$max_length = 40;
$desc_global = $_GET['desc_global'];
$nom_cliente_tarea = strtoupper($_GET['nom_cliente_tarea']);
$nropedcli = formatear_texto($_GET['idcod']);
$nomcli = formatear_texto($_GET['nom_cli']);
$nit = formatear_texto($_GET['nit']);
$tipocli = substr($_GET['tipo_cli'], 0, 19);
$dircli = formatear_texto($_GET['dircli']);
$dircli2 = formatear_texto($_GET['dircli2']);
$dircli2 = substr($dircli2, 0, $max_length);
$numtel = $_GET['numtel'];
$exp_almacen = explode('|', formatear_texto($_GET['almacen']));
$almacen_cod = $exp_almacen[0];
$almacen_nom = $exp_almacen[1];
$exp_vendedor = explode('|', formatear_texto($_GET['vendedor']));
$vendedor_cod = $exp_vendedor[0];
$vendedor_nom = $exp_vendedor[1];

$monto_total_ms = 0;
$monto_subtotal_ms = 0;
$monto_impuesto_subtotal_ms = 0;
$monto_descuento_ms = 0;
$montodes_ms = 0;
$monto_baseimponible_ms = 0;
$monto_total_efe_ms = 0;
$monto_total_sal_ms = 0;
/*
$tip_tran = match ($_SESSION['tipo_tarea']) {
    'presupuesto' => 'PRE',
    'pedido' => 'PEDxCLI',
    'factura' => 'FAC',
    default => throw new Exception("Invalid tipo_tarea: " . ($_SESSION['tipo_tarea'] ?? 'undefined')),
};*/
$tipo_tarea = $_SESSION['tipo_tarea'] ?? null;

if ($tipo_tarea === 'presupuesto') {
    $tip_tran = 'PRE';
} elseif ($tipo_tarea === 'pedido') {
    $tip_tran = 'PEDxCLI';
} elseif ($tipo_tarea === 'factura') {
    $tip_tran = 'FAC';
} else {
    throw new Exception("Invalid tipo_tarea: " . ($tipo_tarea ?? 'undefined'));
}

$flag_ = true;
$flag_nom = "";
$transaction_active = false;

try {
    if ($_SESSION['tipo_tarea'] == 'pedido') {
        foreach ($_SESSION['aDatos'] as $producto) {
            $cod_prod = $producto['codigo'];
            $nom_prod = $producto['nombre'];
            $cantidad = $producto['cantidad'];
            $almacen_cod_ = (int)trim($almacen_cod);
            $almacen_cod_ = $almacen_cod_ <= 1 ? "" : $almacen_cod_;

            $sql3 = "SELECT CODPRO, EXISTENCIA$almacen_cod_ AS EXISTENCIA, CANRESERVADA$almacen_cod_ AS RESERVADA, PROCOMPUESTO, TIPINV FROM INVENTARIO WHERE CODPRO='$cod_prod';";
            AuditLog($sql3);

            try {
                $result = $base_de_datos->query($sql3);
                if ($data2 = $result->fetchObject()) {
                    $disponible = $data2->EXISTENCIA - $data2->RESERVADA;
                    if ($cantidad <= $disponible || $data2->PROCOMPUESTO == "1" || $data2->TIPINV == "1" || $_SESSION["ventamenos"] == "1" || $_SESSION["actfacexi"] == "1") {
                        continue;
                    }
                    $flag_ = false;
                    $flag_nom .= "$nom_prod | ";
                } else {
                    $flag_ = false;
                    $flag_nom .= "$nom_prod (no encontrado) | ";
                }
            } catch (PDOException $e) {
                AuditLog("Error executing query: $sql3 - " . $e->getMessage());
                $flag_ = false;
                $flag_nom .= "$nom_prod (error consulta) | ";
            }
        }
    }

    if ($flag_) {
        // Iniciar transacción
        $base_de_datos->beginTransaction();
        $transaction_active = true;

        // Generar control único
        $res_cadena = cadena_control();
        $exp_control = explode('|', $res_cadena);
        $dias = $exp_control[0];
        $hora_actual = $exp_control[1];
        $aleatorio = $exp_control[2];
        $fecha_actual_clarion = $exp_control[3];
        $fecha_actual_ymd = $exp_control[4];
        $cal_contro_uni = "$dias$hora_actual$aleatorio"."01";

        // Procesar productos
        $arr_itbm = [];
        $mack_subtotal = array_fill(0, 40, 0);
        $mack_subtotal_imp = array_fill(0, 40, 0);

        foreach ($_SESSION['aDatos'] as $carg_producto) {
            $cod_prod = $carg_producto['codigo'];
            $nom_prod = $carg_producto['nombre'];
            $precio = $carg_producto['precio'];
            $descuento = $carg_producto['descuento'];
            $cantidad = $carg_producto['cantidad'];
            $itbm = $carg_producto['itbm'];
            $costoact = $carg_producto['costoact'];
            $costopro = $carg_producto['costopro'];
            $grupoinv = $carg_producto['grupoinv'];
            $coddep = $carg_producto['coddep'];
            $lineainv = $carg_producto['lineainv'];
            $codalmacen = $carg_producto['codalmacen'];
            $codvend = $carg_producto['codvend'];
            $nomvend = $carg_producto['nomvend'];
            $exen = $carg_producto['exento'];
            $nota = $carg_producto['nota'];

            if ($exen == 0) {
                $arr_itbm[] = $itbm;
            }

            $montodes_ = 0;
            $pordes_ms = 0;
            $desc_global_frm = str_replace("%", "", $desc_global);
            if ($desc_global_frm > 0) {
                if (strpos($desc_global, '%') === false) {
                    $pordes_ms = ($desc_global * 100) / (($precio * $cantidad) - $descuento);
                    $montodes_ms += $desc_global;
                    $montodes_ = $desc_global;
                } else {
                    $pordes_ms = $desc_global_frm;
                    $montodes_ms += (($precio * $cantidad) - $descuento) * ($pordes_ms / 100);
                    $montodes_ = (($precio * $cantidad) - $descuento) * ($pordes_ms / 100);
                }
            }

            $monto_subtotal = (($precio * $cantidad) - round($descuento, 2)) - round($montodes_, 2);
            $monto_impuesto = ((($precio * $cantidad) - $descuento) - $montodes_) * ($itbm / 100);
            $monto_descuento = $descuento;
            $pordes = ($descuento * 100) / ($precio * $cantidad);
            $monto_desc_global = (($precio * $cantidad) - $descuento) * ($pordes_ms / 100);
            $montocosto = $cantidad * $costopro;

            $monto_subtotal_ms += ($precio * $cantidad) - $descuento;
            $monto_baseimponible_ms += (($precio * $cantidad) - $descuento) - $montodes_;
            $monto_total_ms += ($monto_subtotal + $monto_impuesto);
            $monto_total_efe_ms += (($precio * $cantidad) - $descuento) + ((($precio * $cantidad) - $descuento) * ($itbm / 100));
            $monto_impuesto_subtotal_ms += $monto_impuesto;
            $monto_descuento_ms += $descuento;
            $monto_total_sal_ms = $monto_total_efe_ms;

            $res_cadena=cadena_control();
            $exp_control=explode('|', $res_cadena);

            $dias=$exp_control[0];
            $hora_actual=$exp_control[1];
            $aleatorio=$exp_control[2];
            /*cadena de calculo control unico detalle*/
            $cal_contro_uni_det="$dias$hora_actual$aleatorio"."01";

            $cal_contro_uni_det = "$dias$hora_actual$aleatorio"."01";
            $parcontrol = $_SESSION['id_control'];

            if (in_array($itbm, array_unique($arr_itbm))) {
                $itbm_frm = (int)$itbm;
                $mack_subtotal[$itbm_frm] += ($precio * $cantidad) - $descuento;
                $mack_subtotal_imp[$itbm_frm] += $monto_impuesto;
            }

            $query2 = "INSERT INTO TRANSACCDETALLES (CONTROL,CODPRO,CANTIDAD,PRECOSUNI,COSTOACT,COSTOPRO, 
                    IMPPOR,MONTOIMP,TOTAL,DESCRIP1,TIPTRAN,FECEMIS,FECEMISS,
                    PRECIO1,UTILPRECIO1,PRECIO2,UTILPRECIO2,PRECIO3,UTILPRECIO3,
                    MONTOCOS,TIPINV,FACCAM1,VALFOB1,COSTOFLE1,COSTOSEG1,VALORCIF1,COSTOARA1,COSTONAC1,COSTOADU1,
                    PAGOCOM1,GASTOADU1,OTROGAS1,COSTOFIN1,FACCAM2,VALFOB2,COSTOFLE2,COSTOSEG2,VALORCIF2,
                    COSTOARA2,COSTONAC2,COSTOADU2,PAGOCOM2,GASTOADU2,OTROGAS2,COSTOFIN2,
                    PRECIOE1,PRECIOE2,PRECIOE3,TIPODET,TIPPRO,TIPREG,CODIGO,
                    COMISVEN,COMISCOB,COMISTIP,CODALENT,FECHORA,PORDES,MONTODESCUENTO,COMPONENTE,FHPRODBASE,
                    PORCOMISION,MONTOCOMISION,DEVUELTA,CANTIDADDEV,ORIGEN,PRECIO,FACTORCAMBIO,
                    IMPPOR2,MONTOIMP2,IMPPOR3,MONTOIMP3,PROCESADO,CODIGODEP,GRUPOINV,CONLINEA,CODVEN,
                    PORRETTAR,MONTORETTAR,MESVENC,FECHAVENCE,LINEAOINV,NOMBRE,
                    PORCOMISDETAIL,PORDESGLO,MONTODESCUENTOGLO,CANTIDADFAC,PORREC,MONTORECARGA,PORRECARGOGLO,
                    MONTORECARGOGLO,CONSIGA,PARCONTROL,CANTIDADEMP,PRECOSUNIEMP) 
                    VALUES ('$cal_contro_uni','$cod_prod',$cantidad,$precio,$costoact,$costopro,
                    $itbm,".round($monto_impuesto, 2).",".$monto_subtotal.",'$nom_prod','$tip_tran',
                    '$fecha_actual_clarion','$fecha_actual_ymd',
                    0.00,0.00,0.00,0.00,0.00,0.00,$montocosto,0,
                    0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,
                    0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,
                    0.00,0.00,0.00,0,0,1,'$nropedcli',
                    0.00,0.00,0,'$codalmacen','$cal_contro_uni_det',".$pordes.",
                    ".sprintf('%.2f', $monto_descuento).",0,'$cal_contro_uni_det',
                    0.00,0.00,0,0.00,0,3,0.00,0.00,0.00,0.00,0.00,0,
                    '$coddep','$grupoinv',0,'$codvend',
                    0.00,0.00,0,0,'$lineainv','$nomvend',
                    0.00,$pordes_ms,$monto_desc_global,0.00,0.00,0.00,0.00,0.00,0.00,
                    $parcontrol,0.00,0.00);";

            AuditLog($query2);
            $sentencia = $base_de_datos->prepare($query2);
            if (!$sentencia->execute()) {
                throw new PDOException("Failed to execute TRANSACCDETALLES insert: " . implode(", ", $sentencia->errorInfo()));
            }

            if ($_SESSION['tipo_tarea'] == 'pedido') {
                $txt_CodAlmacen = (int)trim($almacen_cod);
                $txt_CodAlmacen = $txt_CodAlmacen <= 1 ? "" : $txt_CodAlmacen;

                $sql_prod = "SELECT PROCOMPUESTO FROM INVENTARIO WHERE CODPRO='$cod_prod';";
                $result_prod = $base_de_datos->query($sql_prod);
                $producto = $result_prod->fetch(PDO::FETCH_ASSOC);

                if ($producto && $producto["PROCOMPUESTO"] == "1") {
                    $sql_compuesto = "SELECT CODPROPRO, CANTIDAD FROM INVENTARIOCOMPONENTES WHERE CODPRO='$cod_prod';";
                    $sentencia_compuesto = $base_de_datos->prepare($sql_compuesto);
                    $sentencia_compuesto->execute();
                    foreach ($sentencia_compuesto->fetchAll(PDO::FETCH_ASSOC) as $componente) {
                        $cod_prod_comp = $componente["CODPROPRO"];
                        $cantidad_componente = $cantidad * $componente["CANTIDAD"];
                        $query_alm_componente = "UPDATE INVENTARIO SET CANRESERVADA$txt_CodAlmacen=ISNULL(CANRESERVADA$txt_CodAlmacen,0)+$cantidad_componente WHERE CODPRO='$cod_prod_comp';";
                        AuditLog($query_alm_componente);
                        $sentencia = $base_de_datos->prepare($query_alm_componente);
                        if (!$sentencia->execute()) {
                            throw new PDOException("Failed to update INVENTARIO for component: " . implode(", ", $sentencia->errorInfo()));
                        }
                    }
                } else {
                    $query2 = "UPDATE INVENTARIO SET CANRESERVADA$txt_CodAlmacen=ISNULL(CANRESERVADA$txt_CodAlmacen,0)+$cantidad WHERE CODPRO='$cod_prod';";
                    AuditLog($query2);
                    $sentencia = $base_de_datos->prepare($query2);
                    if (!$sentencia->execute()) {
                        throw new PDOException("Failed to update INVENTARIO: " . implode(", ", $sentencia->errorInfo()));
                    }
                }
            }

            if ($nota != '') {
                $query = "INSERT INTO TRANSACCAMPLIADA (CONTROL,FECHORA,DESAPLIADA) VALUES ('$cal_contro_uni','$cal_contro_uni_det','$nota');";
                AuditLog($query);
                $sentencia = $base_de_datos->prepare($query);
                if (!$sentencia->execute()) {
                    throw new PDOException("Failed to insert TRANSACCAMPLIADA: " . implode(", ", $sentencia->errorInfo()));
                }
            }
        }

        // Generar número correlativo
        $sql3 = "SELECT NROINIFAC, NROINIPRE, NROPEDCLI FROM BASEEMPRESA WHERE CONTROL='{$_SESSION['id_control']}';";
        $sentencia4 = $base_de_datos->prepare($sql3);
        $sentencia4->execute();
        $result100 = $sentencia4->fetch(PDO::FETCH_ASSOC);

        if ($result100) {
            $tipo_tarea = $_SESSION['tipo_tarea'] ?? null;

            if ($tipo_tarea === 'presupuesto') {
                $nro_correlativo = $result100['NROINIPRE'];
            } elseif ($tipo_tarea === 'pedido') {
                $nro_correlativo = $result100['NROPEDCLI'];
            } elseif ($tipo_tarea === 'factura') {
                $nro_correlativo = $result100['NROINIFAC'];
            } else {
                throw new PDOException("Invalid tipo_tarea for correlativo");
            }
            $query4 = "UPDATE BASEEMPRESA SET " . ($_SESSION['tipo_tarea'] == 'presupuesto' ? 'NROINIPRE' : ($_SESSION['tipo_tarea'] == 'pedido' ? 'NROPEDCLI' : 'NROINIFAC')) . " = ($nro_correlativo + 1) WHERE CONTROL='{$_SESSION['id_control']}';";
            $sentencia4 = $base_de_datos->prepare($query4);
            if (!$sentencia4->execute()) {
                throw new PDOException("Failed to update BASEEMPRESA: " . implode(", ", $sentencia4->errorInfo()));
            }
        } else {
            throw new PDOException("No data found in BASEEMPRESA for CONTROL: {$_SESSION['id_control']}");
        }

        $nro_ = str_pad("", 10 - strlen($nro_correlativo), "0");
        $nro_correlativo = $nro_ . $nro_correlativo;

        // Verificar existencia de VALIDOREIMPRIMIR
        $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='TRANSACCMAESTRO' AND COLUMN_NAME='VALIDOREIMPRIMIR';";
        $stmt = $base_de_datos->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $has_validoreimprimir = $result['count'] > 0;

        // Insertar en TRANSACCMAESTRO con parámetros preparados
        $columns = [
            'CONTROL', 'TIPREG', 'CODIGO', 'TIPTRAN', 'NUMREF', 'DESCRIP1',
            'FECEMIS', 'FECEMISS', 'DIASVEN', 'FECVENC', 'FECVENCS', 'MONTOBRU',
            'MONTODES', 'PORDES', 'MONTOSUB', 'MONTOIMP', 'PORIMP', 'MONTOPAG',
            'MONTOTOT', 'MONTOSAL', 'MONTOEFE', 'MONTOCHE', 'MONTOTAR',
            'NOMBRE', 'MARCA', 'CONTADOR', 'TOTCONTADOR', 'CONTROLDOC', 'MONTOPAGF', 'RIF', 'NIT',
            'MONTOCOS', 'TIPODOC', 'CAMBIO', 'CODVEN', 'TIPOCLI',
            'COMISV', 'COMISC', 'MONTORET', 'PORRET', 'MONTOPA', 'COMISVEN', 'COMISCOB',
            'DIRECCION', 'CODALENT', 'ACTBANCO', 'MONTODESCUENTO', 'MARCARE', 'HORA',
            'CODUSER', 'TOTALEXENTAS', 'BASEIMPONIBLE', 'OTRAPLAZA', 'BASEIMPONIBLEIVA', 'FACTORCAMBIO', 'SIGNOMONEDA',
            'PARCONTROL'
        ];
        $placeholders = array_map(function($col) {
            return ":$col";
        }, $columns);
        if ($has_validoreimprimir) {
            $columns[] = 'VALIDOREIMPRIMIR';
            $placeholders[] = ':VALIDOREIMPRIMIR';
        }

        $query = "INSERT INTO TRANSACCMAESTRO (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ");";
        AuditLog("TRANSACCMAESTRO Query: $query");

        $sentencia = $base_de_datos->prepare($query);
        $params = [
            ':CONTROL' => $cal_contro_uni,
            ':TIPREG' => 1,
            ':CODIGO' => $nropedcli,
            ':TIPTRAN' => $tip_tran,
            ':NUMREF' => $nro_correlativo,
            ':DESCRIP1' => "Cotizaciones $nro_correlativo",
            ':FECEMIS' => $fecha_actual_clarion,
            ':FECEMISS' => $fecha_actual_ymd,
            ':DIASVEN' => 0,
            ':FECVENC' => $fecha_actual_clarion,
            ':FECVENCS' => $fecha_actual_ymd,
            ':MONTOBRU' => round($monto_subtotal_ms, 2),
            ':MONTODES' => $montodes_ms,
            ':PORDES' => $pordes_ms,
            ':MONTOSUB' => round($monto_subtotal_ms, 2),
            ':MONTOIMP' => $monto_impuesto_subtotal_ms,
            ':PORIMP' => 0.00,
            ':MONTOPAG' => 0.00,
            ':MONTOTOT' => round($monto_total_ms, 2),
            ':MONTOSAL' => round($monto_total_sal_ms, 2),
            ':MONTOEFE' => round($monto_total_efe_ms, 2),
            ':MONTOCHE' => 0.00,
            ':MONTOTAR' => 0.00,
            ':NOMBRE' => $nomcli,
            ':MARCA' => 0,
            ':CONTADOR' => 1,
            ':TOTCONTADOR' => 0,
            ':CONTROLDOC' => $cal_contro_uni,
            ':MONTOPAGF' => 0.00,
            ':RIF' => $nropedcli,
            ':NIT' => $nit,
            ':MONTOCOS' => 0.00,
            ':TIPODOC' => 0,
            ':CAMBIO' => 0.00,
            ':CODVEN' => $vendedor_cod,
            ':TIPOCLI' => $tipocli,
            ':COMISV' => 0,
            ':COMISC' => 0,
            ':MONTORET' => 0.00,
            ':PORRET' => 0.00,
            ':MONTOPA' => 0.00,
            ':COMISVEN' => 0.00,
            ':COMISCOB' => 0.00,
            ':DIRECCION' => $dircli,
            ':CODALENT' => $almacen_cod,
            ':ACTBANCO' => 0,
            ':MONTODESCUENTO' => round($monto_descuento_ms, 2),
            ':MARCARE' => 0,
            ':HORA' => $hora_actual,
            ':CODUSER' => $_SESSION['coduser'],
            ':TOTALEXENTAS' => 0.00,
            ':BASEIMPONIBLE' => round($monto_baseimponible_ms, 2),
            ':OTRAPLAZA' => 0,
            ':BASEIMPONIBLEIVA' => round($monto_baseimponible_ms, 2),
            ':FACTORCAMBIO' => 1.00,
            ':SIGNOMONEDA' => 'Balboa',
            ':PARCONTROL' => $_SESSION['id_control']
        ];
        if ($has_validoreimprimir) {
            $params[':VALIDOREIMPRIMIR'] = '';
        }

        foreach ($params as $key => $value) {
            $sentencia->bindValue($key, $value);
        }

        AuditLog("TRANSACCMAESTRO Params: " . json_encode($params));
        if (!$sentencia->execute()) {
            throw new PDOException("Failed to insert TRANSACCMAESTRO: " . implode(", ", $sentencia->errorInfo()));
        }

        // Insertar impuestos
        foreach (array_unique($arr_itbm) as $it) {
            $res_cadena = cadena_control();
            $exp_control = explode('|', $res_cadena);
            $cal_contro_uni_imp = $exp_control[0] . $exp_control[1] . $exp_control[2] . "01";

            $query10 = $base_de_datos->prepare("SELECT CODIGOIMP FROM BASEIMPUESTOS WHERE VALORIMP=:valimp;");
            $query10->bindParam("valimp", $it, PDO::PARAM_STR);
            $query10->execute();
            $cod_imp = ($result10 = $query10->fetch(PDO::FETCH_ASSOC)) ? $result10['CODIGOIMP'] : 0;

            $query = "INSERT INTO TRANSACCIMPUESTOS (CONTROL,CONTROLIMP,VALORIMP,BASEIMPUESTO,
                    MONTOIMPUESTO,SUMABASE,SUMAIMP,CODIGOIMP) 
                    VALUES ('$cal_contro_uni','$cal_contro_uni_imp',$it,".$mack_subtotal[round($it)].",
                    ".$mack_subtotal_imp[round($it)].",1,1,$cod_imp);";
            AuditLog($query);
            $sentencia = $base_de_datos->prepare($query);
            if (!$sentencia->execute()) {
                throw new PDOException("Failed to insert TRANSACCIMPUESTOS: " . implode(", ", $sentencia->errorInfo()));
            }
        }

        // Insertar observaciones
        $obv = str_replace("%23", "#", $_GET['obv']);
        if ($obv != '') {
            $query = "INSERT INTO TRANSACCOBSERVACIONES (CONTROL,OBS1) VALUES ('$cal_contro_uni','$obv');";
            AuditLog($query);
            $sentencia = $base_de_datos->prepare($query);
            if (!$sentencia->execute()) {
                throw new PDOException("Failed to insert TRANSACCOBSERVACIONES: " . implode(", ", $sentencia->errorInfo()));
            }
        }

        // Confirmar transacción
        $base_de_datos->commit();
        $transaction_active = false;

        $_SESSION['aDatos'] = [];
        unset($_SESSION['tipo_tarea']);
        echo "1|$cal_contro_uni|$nro_correlativo|$tip_tran|$obv";
    } else {
        echo "0|Los siguientes productos sobrepasan la existencia en almacen: $flag_nom";
    }
} catch (Exception $e) {
    // Revertir transacción solo si está activa
    if ($transaction_active) {
        try {
            $base_de_datos->rollBack();
            $transaction_active = false;
        } catch (PDOException $rollback_e) {
            AuditLog("Rollback Error: " . $rollback_e->getMessage());
        }
    }
    AuditLog("Transaction Error: " . $e->getMessage());
    echo "0|Error al procesar la transacción";
}

function cadena_control() {
    usleep(100000);
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime(date("Y-m-d H:i:s"));
    $diff = $fecha1->diff($fecha2);
    $dias = $diff->days;
    $fecha_actual_clarion = $diff->days;
    $fecha_actual_ymd = date("Ymd");

    $hora_actual = (date('H') * 360000) + (date("i") * 6000) + (date("s") * 100) + (date("v") / 10) + 1;
    $hora_actual = str_pad(substr($hora_actual, 0, 7), 7, "0", STR_PAD_LEFT);

    //$aleatorio = mt_rand(10000, 99999);    

    // Generar 'aleatorio' secuencial basado en microtime para mayor precisión temporal
    // Usando microtime(true) para obtener microsegundos con más decimales

    $aux = microtime(true);
    $now = DateTime::createFromFormat('U.u', $aux);        
    if (is_bool($now)) {
        $now = DateTime::createFromFormat('U.u', $aux += 0.001);
    }
    $aleatorio = str_pad(floor($now->format('u') / 10), 5, '0', STR_PAD_LEFT);
    
    return "$dias|$hora_actual|$aleatorio|$fecha_actual_clarion|$fecha_actual_ymd";
}

function formatear_texto($texto) {
    $texto = str_replace("%23", "#", $texto);
    $texto = str_replace("%26", "&", $texto);
    $texto = str_replace("%27", "'", $texto);
    return $texto;
}

function AuditLog($string) {
    $logDir = __DIR__ . "/logs/";
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $data = date("Y-m-d_H-i-s");
    $pathLog = $logDir . $data . "-logs.log";
    $log = date("F j, Y, g:i a") . PHP_EOL . $string . PHP_EOL . "-------------------------" . PHP_EOL;
    file_put_contents($pathLog, $log, FILE_APPEND);
}
?>