<?php
include_once "permiso.php";
include_once "config/db.php";
$desc_global       = $_GET['desc_global'];
$desc_global_frm = str_replace("%", "", $desc_global);
$data       = $_GET['data'];
$formas_pago = json_decode($data, true);
$nom_cliente_tarea = strtoupper($_GET['nom_cliente_tarea']);
$nropedcli         = formatear_texto($_GET['idcod']);
$nropedcli         = str_replace("'", "''", $nropedcli);
ob_clean();
//Es es un cambio para configurar este gitlab
$sql_cli = "SELECT TIPOCLI, DIASCRE, RIF, NUMTEL, CONESPECIAL, PORRETIMP FROM BASECLIENTESPROVEEDORES b WHERE CODIGO = '$nropedcli' AND TIPREG = '1' AND INTEGRADO=0;";
//$sql_cli = "SELECT TIPOCLI FROM BASECLIENTESPROVEEDORES b WHERE CODIGO = '$nropedcli';";
$result_cli = $base_de_datos->query($sql_cli);
$cliente = $result_cli->fetch(PDO::FETCH_ASSOC);

$tipo_cliente     = $cliente["TIPOCLI"];
$dias_cre         = $cliente["DIASCRE"] ? $cliente["DIASCRE"] : $_SESSION['valdiasvenc'];
$rif              = str_replace("'", "''", $cliente["RIF"]);
$telefono_cliente = $cliente["NUMTEL"];
$CONESPECIAL      = (string)$cliente["CONESPECIAL"];
$PORRETIMP        = (float)$cliente["PORRETIMP"];

if(!$tipo_cliente){
    echo "0|Tipo de Cliente inválido";exit;

}
//$nomcli=strtoupper($_GET['nom_cli']);
$nomcli      = formatear_texto($_GET['nom_cli']);
$nit         = formatear_texto($_GET['nit']);
$tipocli     = substr($_GET['tipo_cli'], 0, 19);
$dircli      = formatear_texto($_GET['dircli']);
$dircli2     = substr(formatear_texto($_GET['dircli2']),0,40);
$numtel      = $_GET['numtel'];
$exp_almacen = explode('|', formatear_texto($_GET['almacen']));
$almacen_cod = $exp_almacen[0];
$almacen_nom = $exp_almacen[1];

$exp_vendedor                   = explode('|', formatear_texto($_GET['vendedor']));
$vendedor_cod                   = $exp_vendedor[0];
$vendedor_nom                   = $exp_vendedor[1];
$monto_total_ms                 = 0;
$monto_subtotal_ms              = 0;
$monto_impuesto_subtotal_ms     = 0;
$monto_descuento_ms             = 0;
$montodes_ms                    = 0;
$monto_baseimponible_ms         = 0;
$monto_total_efe_ms             = 0;
$monto_subtotal_ms_imp          = 0;
$monto_impuesto_subtotal_ms_imp = 0;
$monto_cambio                   = $_GET['cambio'] ? (float)$_GET["cambio"] : 0;
$hayclip                        = false;
$tip_tran = "FAC";

$MONTOTAR   = 0;
$MONTOCHE   = 0;
$MONTOINTS1 = 0;
$MONTOINTS2 = 0;
$MONTOEFE   = 0;
$MONTOSALDO = $_GET["saldo"] ? (float)$_GET["saldo"] : 0;

$nro_doc = "";

if($MONTOSALDO > 0){
    $tipo_factura = "CREDITO";
}else{    
    $tipo_factura = "CONTADO";
}


    $max = sizeof($_SESSION['aDatos']);
    for ($i = 0; $i < $max; $i++) {

        $carg_producto = $_SESSION['aDatos'][$i];

        $cod_prod       = $carg_producto['codigo'];
        $nom_prod       = $carg_producto['nombre'];
        $precio         = $carg_producto['precio'];
        $descuento      = $carg_producto['descuento'];
        $cantidad       = $carg_producto['cantidad'];
        $itbm           = $carg_producto['itbm'];
        $costoact       = $carg_producto['costoact'];
        $costopro       = $carg_producto['costopro'];
        $grupoinv       = $carg_producto['grupoinv'];
        $coddep         = $carg_producto['coddep'];
        $lineainv       = $carg_producto['lineainv'];
        $precio_noformt = $carg_producto['precio_noformt'];
        $codalmacen     = $carg_producto['codalmacen'];
        $codvend        = $carg_producto['codvend'];
        $nomvend        = $carg_producto['nomvend'];
        $exen           = $carg_producto['exento'];
        $nota           = $carg_producto['nota'];

        $almacen_cod_ = (int)trim($almacen_cod);
        if ($almacen_cod_ <= 1) {
            $almacen_cod_ = "";
        }

        $sql3 = "SELECT CODPRO, EXISTENCIA$almacen_cod_ AS EXISTENCIA, CANRESERVADA as RESERVADA, PROCOMPUESTO, TIPINV FROM INVENTARIO WHERE CODPRO='$cod_prod';";
        
        $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
        $total_reg = $result->fetchColumn();
        if ($total_reg != '') {
            $sentencia4 = $base_de_datos->prepare($sql3, [
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
            ]);

            $sentencia4->execute();
            $contador = 0;
            $flag_ = true;
            $flag_nom = "";
            while ($data2 = $sentencia4->fetchObject()) {
                $disponible = $data2->EXISTENCIA - $data2->RESERVADA;
                if ($cantidad <= $disponible) {
                    if ($disponible > 0) {
                        //echo "1|Si hay producto disponible";
                        $contador++;
                    } else {
                        if($_SESSION['actfacexi'] == "1" || $data2->TIPINV){
                            $flag_ = true;
                            $contador++;
    
                        }
                        else{
                            $flag_ = false;
                            $flag_nom .= "$nom_prod | ";
                            //echo "0|No existe producto disponible";
                            $contador++;
                        }
                    }
                } else {
                    //Revisar permiso para facturar en negativo
                    if($_SESSION['actfacexi'] == "1"  || $data2->TIPINV){
                        $flag_ = true;

                    }else{
                        $flag_ = false;
                        $flag_nom .= "$nom_prod ***** ";
                        //echo "0|Producto no disponible";

                    }
                }
                if($data2->PROCOMPUESTO == "1"){
                    $flag_ = true;

                }
            }
        }/*fin IF*/
    } //fin for de array productos
    try {
    if ($flag_) {
        /*consultamos los calculos para generar las variables control con los datos correctos*/
        $res_cadena  = cadena_control();
        $exp_control = explode('|', $res_cadena);

        $dias                 = $exp_control[0];
        $hora_actual          = $exp_control[1];
        $aleatorio            = $exp_control[2];
        $fecha_actual_clarion = $exp_control[3];
        $fecha_actual_ymd     = $exp_control[4];
        $hora_actual_clarion  = $exp_control[1];
        $cal_contro_uni = "$dias$hora_actual$aleatorio" . "01";
        //}

        $cadena_pago  = cadena_control_pago();
        $exp_control_pago = explode('|', $cadena_pago);

        $dias_pago                 = $exp_control_pago[0];
        $hora_actual_pago          = $exp_control_pago[1];
        $aleatorio_pago            = $exp_control_pago[2];
        $fecha_actual_clarion_pago = $exp_control_pago[3];
        $fecha_actual_ymd_pago     = $exp_control_pago[4];
        $hora_actual_clarion_pago  = $exp_control_pago[1];
        $pago_contro_uni = "$dias_pago$hora_actual_pago$aleatorio_pago" . "01";
        /*************************************************************************************/
        /* empezamos a generar todo los datos necesarios para grabar en la tabla maestro     */
        /*************************************************************************************/

        $sql3 = "SELECT PREFAC_CONFIN ,IMPPOR, NROFAC_CONFIN,PREFAC_CONTRI , NROFAC_CONTRI,PREFAC_GUBER , NROFAC_GUBER,PREFAC_REGESP , NROFAC_REGESP,NROINIFAC, NROINIPRE, NROPEDCLI, MONEDA, NROINIREC
         FROM BASEEMPRESA WHERE CONTROL='" . $_SESSION['id_control'] . "';";

        $sentencia4 = $base_de_datos->prepare($sql3, [
            PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
        ]);

        $sentencia4->execute();
        $result100 = $sentencia4->fetch(PDO::FETCH_ASSOC);
        if ($result100) {
            $signo_moneda = $result100['MONEDA'];
            $query4 = "";
            if( $tipo_cliente == "Consumidor Final"){

                $nro_correlativo = (int)$result100['NROFAC_CONFIN'];
                $prefijo_factura = $result100['PREFAC_CONFIN'];
                $query4 = "UPDATE BASEEMPRESA SET NROFAC_CONFIN = (NROFAC_CONFIN + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";
            }
            if( $tipo_cliente == "Contribuyente"){
                $nro_correlativo = (int)$result100['NROFAC_CONTRI'];
                $prefijo_factura = $result100['PREFAC_CONTRI'];
                $query4 = "UPDATE BASEEMPRESA SET NROFAC_CONTRI = (NROFAC_CONTRI + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";
                
            }

            if( $tipo_cliente == "Gubernamental" || $tipo_cliente == "Gobierno"){
                $nro_correlativo = (int)$result100['NROFAC_GUBER'];
                $prefijo_factura = $result100['PREFAC_GUBER'];
                $query4 = "UPDATE BASEEMPRESA SET NROFAC_GUBER = (NROFAC_GUBER + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";
                
            }
            if( $tipo_cliente == "Regimen Especial"){
                $nro_correlativo = (int)$result100['NROFAC_REGESP'];
                $prefijo_factura = $result100['PREFAC_REGESP'];
                $query4 = "UPDATE BASEEMPRESA SET NROFAC_REGESP = (NROFAC_REGESP + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";
                
            }
            
            if( $tipo_cliente == "Otros" ){
                $nro_correlativo = (int)$result100['NROFAC_REGESP'];
                $prefijo_factura = $result100['PREFAC_REGESP'];
                $query4 = "UPDATE BASEEMPRESA SET NROFAC_REGESP = (NROFAC_REGESP + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";
                
            }
            if($query4 == ""){
                
                $nro_correlativo = (int)$result100['NROFAC_REGESP'];
                $prefijo_factura = $result100['PREFAC_REGESP'];
                $query4 = "UPDATE BASEEMPRESA SET NROFAC_REGESP = (NROFAC_REGESP + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";
            }
            $nro_pago = (int)$result100['NROINIREC'];
            $nro_pago = str_pad($nro_pago,10,"0",STR_PAD_LEFT);

            if($tipo_factura == "CONTADO" ){
            
    
                $query_comp = "UPDATE BASEEMPRESA SET NROINIREC = (NROINIREC + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";
                $sentencia_comp = $base_de_datos->prepare("$query_comp");
                //$resultado4 = $sentencia_comp->execute();

            }

            $porimp = (float)$result100['IMPPOR'];
            $nro_correlativo = (int)$result100['NROINIFAC'];
            $nro_doc = (int)$result100['NROINIFAC'];
            $nro_doc = str_pad($nro_doc,10,"0",STR_PAD_LEFT);
            $prefijo_factura = "";
            $query4 = "UPDATE BASEEMPRESA SET NROINIFAC = (NROINIFAC + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";

            $sentencia4 = $base_de_datos->prepare("$query4");
            $resultado4 = $sentencia4->execute();
        }

        /*************************************************************************************/
        /* empezamos a generar todo los datos necesarios para grabar en la tabla detalle     */
        /*************************************************************************************/
        $arr_itbm = array();
        $max = sizeof($_SESSION['aDatos']);
        for ($i = 0; $i < $max; $i++) {
            $k = 0;
            foreach ($_SESSION['aDatos'][$i] as $key => $val) {
                $k++;
                if ($k == 6) { // itbm
                    $itbm = $val;
                } else if ($k == 16) { // itbm
                    $exento = $val;
                }
            }

            if ($exento == 0) {
                array_push($arr_itbm, $itbm);
            }
        }
        $coduser = $_SESSION['coduser'];

        $arr_impuestos     = array_unique($arr_itbm);
        $MONTO_COSTO_TOTAL = 0;
          //var_dump($arr_impuestos);
          //$max=sizeof($_SESSION['aDatos']);
        $mack_subtotal     = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $mack_subtotal_imp = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $porc_itbm         = false;
        for ($i = 0; $i < $max; $i++) {
            $monto_subtotal  = 0;
            $monto_impuesto  = 0;
            $pordes          = 0;
            $monto_descuento = 0;
            $cantidad        = 0;
            $precio          = 0.00;
            $itbm            = "0";
            $k               = 0;
            foreach ($_SESSION['aDatos'][$i] as $key => $val) {
                $k++;
                if ($k == 1) { // codigo del producto
                    $cod_prod = $val;
                } else if ($k == 2) { // nombre del producto
                    $nom_prod = $val;
                } else if ($k == 3) { // precio del producto
                    $precio = $val;
                } else if ($k == 4) { // cantidad del producto
                    $descuento = $val;
                } else if ($k == 5) { // itbm
                    $cantidad = $val;
                } else if ($k == 6) { // itbm
                    $itbm = $val;
                } else if ($k == 7) { // itbm
                    $costoact = $val;
                } else if ($k == 8) { // itbm
                    $costopro = $val;
                } else if ($k == 9) { // itbm
                    $grupoinv = $val;
                } else if ($k == 10) { // itbm
                    $coddep = $val;
                } else if ($k == 11) { // itbm
                    $lineainv = $val;
                } else if ($k == 12) { // itbm
                    $precio_noformt = $val;
                } else if ($k == 13) { // itbm
                    $codalmacen = $val;
                } else if ($k == 14) { // itbm
                    $codvend = $val;
                } else if ($k == 15) { // itbm
                    $nomvend = $val;
                } else if ($k == 16) { // itbm
                    $exen = $val;
                } else if ($k == 18) { // itbm
                    $nota = $val;
                }
            } // fin foreach

            $codempaque = $_SESSION['aDatos'][$i]["codempaque"];
            
            $sql_prod               = "SELECT * FROM INVENTARIO  WHERE CODPRO = '".$cod_prod."';";
            $result_prod            = $base_de_datos->query($sql_prod);
            $producto               = $result_prod->fetch(PDO::FETCH_ASSOC);
            $costoempaque           = 0;
            $precio_sel             = $_SESSION["aDatos"][$i]["precio_sel"];
            $montodes_              = 0;
            $pordes_ms              = 0;
            $monto_descuento_global = $montodes_ms;

            $monto_subtotal  = (($precio * $cantidad) - round($monto_descuento_global, 2)) ;
            $monto_impuesto  = ((($precio * $cantidad) - $descuento)) * ($itbm / 100);        ///////////aqui
            $monto_descuento = $descuento;
            $pordes          = ($monto_descuento_global * 100) / ($precio * $cantidad);
              //echo "($descuento*100)/($precio*$cantidad)";
            $monto_desc_global = (($precio * $cantidad) - $descuento) * ($pordes_ms / 100);
            $montocosto        = $cantidad * $costopro;
            if((float)$itbm == 0.07)
                $porc_itbm = true;

            $monto_subtotal_ms          += ($precio * $cantidad) - $descuento;
            $monto_baseimponible_ms     += (($precio * $cantidad) - $descuento);
            $monto_total_prd             = ((($precio * $cantidad) - round($descuento, 2)) ) + (((($precio * $cantidad) - round($descuento, 2)) ) * ($itbm / 100));
            $monto_total_ms             += ((($precio * $cantidad) - round($descuento, 2)) ) + (((($precio * $cantidad) - round($descuento, 2)) ) * ($itbm / 100));
            $monto_total_efe_ms         += (($precio * $cantidad) - $descuento) + ((($precio * $cantidad) - $descuento) * ($itbm / 100));
            $monto_impuesto_subtotal_ms += ((($precio * $cantidad) - $descuento)) * ($itbm / 100);
            $monto_descuento_ms         += $descuento;

              /*extrayendo los datos correctos para generar el control unico de tbl transacdetalle*/
              //$res_cadena=cadena_control();
            $res_cadena  = cadena_control();
            $exp_control = explode('|', $res_cadena);

            $dias        = $exp_control[0];
            $hora_actual = $exp_control[1];
            $aleatorio   = $exp_control[2];
              /*cadena de calculo control unico detalle*/
            $cal_contro_uni_det = "$dias$hora_actual$aleatorio" . "01";

            $parcontrol = $_SESSION['id_control'];
            
            if($codempaque != ""){

                $sql_emp      = "SELECT * FROM INVENTARIOEMPAQUESV  WHERE CONTROLEMP = '".$codempaque."';";
                $result_emp   = $base_de_datos->query($sql_emp);
                $empaque      = $result_emp->fetch(PDO::FETCH_ASSOC);
                $costoempaque = $producto["COSTOPRO"] * $empaque["CANTIDAD_EMP"];
                $precio       = $precio*((100+$itbm)/100)/$empaque["CANTIDAD_EMP"];
                $precio       = round($precio,4);
            }
            
            if($codempaque != ""){
                $MONTO_COSTO_TOTAL += $costoempaque;

            }else{
                $MONTO_COSTO_TOTAL += $montocosto;

            }
            /*generando valores para lo de la tabla transaccimpuesto*/
            if (in_array($itbm, $arr_impuestos)) {
                $itbm_frm = (int)$itbm;
                if ($itbm_frm == 7) {
                } else if ($itbm_frm == 10) {
                } else if ($itbm_frm == 15) {
                }

                //$monto_subtotal_ms_imp+=($precio*$cantidad)-$descuento;
                //$monto_impuesto_subtotal_ms_imp+=((($precio*$cantidad)-$descuento)-$montodes_)*($itbm/100);

                $mack_subtotal[$itbm_frm] += ($precio * $cantidad) - $descuento;
                $mack_subtotal_imp[$itbm_frm] += ((($precio * $cantidad) - $descuento) - $montodes_) * ($itbm / 100);
            }


            $array_detalles = array("CONTROL" =>  "'$cal_contro_uni'", "CODPRO" => "'$cod_prod'", "CANTIDAD" => $cantidad, "PRECOSUNI" => $precio, "COSTOACT" => $costoact, "COSTOPRO" => $costopro,
                "IMPPOR" => $itbm, "MONTOIMP" => round($monto_impuesto, 2), "TOTAL" => $monto_subtotal, "DESCRIP1" => "'$nom_prod'", "TIPTRAN" => "'$tip_tran'",
                "FECEMIS" => $fecha_actual_clarion,"FECEMISS" => "'$fecha_actual_ymd'",
                "PRECIO1" => 0.00, "UTILPRECIO1" => 0.00, "PRECIO2" => 0.00, "UTILPRECIO2" => 0.00, "PRECIO3" => 0.00, "UTILPRECIO3" => 0.00, "MONTOCOS" => $montocosto, "TIPINV" => $producto["TIPINV"],"FACCAM1" => 0.00,"VALFOB1" => 0.00,
                "COSTOFLE1" => 0.00,"COSTOSEG1" => 0.00,"VALORCIF1" => 0.00,"COSTOARA1" => 0.00,"COSTONAC1" => 0.00, "COSTOADU1" =>  $monto_total_prd,"PAGOCOM1" => 0.00,"GASTOADU1" => 0.00,"OTROGAS1" => 0.00,"COSTOFIN1" => 0.00,
                "NUMPLANILLA" => 0, "FACCAM2" => 0.00, "VALFOB2" => 0.00, "COSTOFLE2" => 0.00, "COSTOSEG2" => 0.00, "VALORCIF2" => 0.00, "COSTOARA2" => 0.00, "COSTONAC2" => 0.00, "COSTOADU2" => 0.00, "PAGOCOM2" => 0.00, 
                "GASTOADU2" => 0.00, "OTROGAS2" => 0.00, "COSTOFIN2" => 0.00,
                "PRECIOE1" => 0.00, "PRECIOE2" => 0.00, "PRECIOE3" => 0.00, "TIPODET" => 0, "TIPPRO" => 0,
                "TIPREG" => 1, "CODIGO" => "'$nropedcli'", "COMISVEN" => 0.00, "COMISCOB" => 0.00, "COMISTIP" => 0, "CODALREC" => "''",
                "CODALENT" => "'$codalmacen'", "FECHORA" => "'$cal_contro_uni_det'", "PORDES" => "$pordes",
                "MONTODESCUENTO" => sprintf('%.2f', $monto_descuento), "FHIMPORTAR" => 0, "COMPONENTE" => 0, "FHPRODBASE" => "'$cal_contro_uni_det'",
                "CODSER" => "''", "PORCOMISION" => 0.00, "MONTOCOMISION" => 0.00, "DEVUELTA" => 0, "CANTIDADDEV" => 0.00, "ORIGEN" => 0,
                "PRECIO" => $precio_sel, "FACTORCAMBIO" =>1.00, "SIGNOMONEDA" =>"'$signo_moneda'", "IMPPOR2" =>0.00, "MONTOIMP2" =>0.00, "IMPPOR3" =>0.00, "MONTOIMP3" => 0.00, "PROCESADO" => 0,
                "CODIGODEP" => "'$coddep'", "GRUPOINV" => "'$grupoinv'", "CONLINEA" => 0, "NUMCONTRATO" => "''", "NUMTELEFONO" => "''", "CODVEN" => "'$codvend'",
                "PORRETTAR" => 0.00, "MONTORETTAR" => 0.00, "MESVENC" => 0, "FECHAVENCE" => 0,
                "LINEAOINV" => "'$lineainv'","CODFABRICANTE" => "''","CENTROCOSTO" => "''","NROSNEDET" => "''","NOMBRE" => "'$nomvend'",
                "PORCOMISDETAIL" => 0.00, "MTOCOMISDETAIL" => 0.00, "PORDESGLO" => $pordes_ms, "MONTODESCUENTOGLO" => $monto_desc_global, "CANTIDADFAC" => 0, "PORREC" => 0.00, "MONTORECARGA" => 0.00, "PORRECARGOGLO" => 0.00,
                "MONTORECARGOGLO" => 0.00, "CONSIGA" => 0.00,"CONTROLEMP" =>  "''",
                "PARCONTROL" => $parcontrol,"CANTIDADEMP" => 0.00,"PRECOSUNIEMP" => 0.00,
                "PRECIO4" => 0.00, "UTILPRECIO4" => 0.00, "PRECIO5" => 0.00, "UTILPRECIO5" => 0.00, "TIPOIMPUESTO" => 0, "CANTIDAD_VEN" => 0.00, "CANTIDAD_ALM" => $cantidad,
                "UTILPRECIO4_DOL" => 0.00, "PRECIO5_DOL" => 0.00, "UTILPRECIO5_DOL" => 0.00, "TASACAMBIOCOMPRA_DOL" => 0.00, "PRECOSUNI_BS_TEMP" => 0.00, "MONTOIMPFIJO" => 0.00,
                "COMPRA_DOL" => 0,"PRECOSUNI_DOL" => 0.0000,"COSTOACT_DOL" => 0.0000,"COSTOPRO_DOL" => 0.0000,"PRECIO1_DOL" => 0.0000,"UTILPRECIO1_DOL" => 0.00,
                "PRECIO2_DOL" => 0.0000, "UTILPRECIO2_DOL" => 0.00, "PRECIO3_DOL" => 0.0000, "UTILPRECIO3_DOL" => 0.00, "PRECIO4_DOL" => 0.0000, "UTILPRECIO4_DOL" => 0.00, "PRECIO5_DOL" => 0.0000, "PORCENTAJEFIJO" => 0.00,
                "TIPOIMPUESTO" => 1);
            $campos_detalles = implode(", ", array_keys($array_detalles));

            $valores_detalles = implode(", ", array_map(function($valor) {
                return $valor === null ? "NULL" : $valor;
            }, $array_detalles));

            $query_detalles = "INSERT INTO TRANSACCDETALLES
            ($campos_detalles)
            VALUES ($valores_detalles) ;";

            $sentencia = $base_de_datos->prepare("$query_detalles");
            $resultado = $sentencia->execute();

            $es_producto_compuesto = false;
            $es_servicio           = $producto["TIPINV"] == "1" ? true : false;

            $es_producto_exento  = $producto["EXENTO"] == "1" ? true : false;

            $txt_CodAlmacen = (int)trim($almacen_cod);
            if ($txt_CodAlmacen <= 1) {
                $txt_CodAlmacen = "";
            }

            //Se debe verificar si es un producto compuesto y si es un empaque
            $sql_empaque       = "SELECT * FROM INVENTARIOEMPAQUESV WHERE CODPRO ='$cod_prod';";
            $sentencia_empaque = $base_de_datos->prepare("$sql_empaque");
            $resultado_empaque = $sentencia_empaque->execute();
            $esEmpaque         = $sentencia_empaque->fetch(PDO::FETCH_ASSOC);

            if($producto["PROCOMPUESTO"] == "1" AND $esEmpaque){
                $es_producto_compuesto = true;

                $sql_compuesto       = "SELECT * FROM INVENTARIOCOMPONENTES WHERE CODPRO ='$cod_prod';";
                $sentencia_compuesto = $base_de_datos->prepare("$sql_compuesto");
                $resultado_comp      = $sentencia_compuesto->execute();
                $componentes         = $sentencia_compuesto->fetchAll(PDO::FETCH_ASSOC);
                foreach ($componentes as $componente) {

                    $array_componentes = $array_detalles;

                    $cod_prod_comp       = $componente["CODPROPRO"];
                    $cantidad_componente = $cantidad*$componente["CANTIDAD"];

                    $sql_prod = "SELECT * FROM INVENTARIO  WHERE CODPRO = '".$cod_prod_comp."';";
                    
                    $sentencia_componente = $base_de_datos->prepare("$sql_prod");
                    $resultado_componente = $sentencia_componente->execute();
                    $fila_componente      = $sentencia_componente->fetch(PDO::FETCH_ASSOC);

                    $prodcomp_costoact = $fila_componente["COSTOACT"];
                    $prodcomp_costopro = $fila_componente["COSTOPRO"];
                    $prodcomp_precio1  = $fila_componente["PRECIO1"];
                    $prodcomp_imppor   = round((( $fila_componente["IMPPOR"] * $fila_componente["PRECIO1"])/100),2) ;
                    $prodcomp_total    = $cantidad_componente*$fila_componente["PRECIO1"];

                    $res_cadena  = cadena_control();
                    $exp_control = explode('|', $res_cadena);
        
                    $dias        = $exp_control[0];
                    $hora_actual = $exp_control[1];
                    $aleatorio   = $exp_control[2];
                      /*cadena de calculo control unico detalle*/
                    $control_componente = "$dias$hora_actual$aleatorio" . "01";

                    $array_componentes["FECHORA"]      = "'".$control_componente."'";
                    $array_componentes["CODPRO"]       = "'".$cod_prod_comp."'";
                    $array_componentes["CANTIDAD"]     = $cantidad_componente;
                    $array_componentes["CANTIDAD_ALM"] = $cantidad_componente;
                    $array_componentes["PRECOSUNI"]    = $prodcomp_precio1;
                    $array_componentes["COSTOACT"]     = $prodcomp_costoact;
                    $array_componentes["COSTOPRO"]     = $prodcomp_costopro;
                    $array_componentes["MONTOIMP"]     = $prodcomp_imppor;
                    $array_componentes["TOTAL"]        = $cantidad_componente*$prodcomp_precio1;
                    $array_componentes["DESCRIP1"]     = "'".$fila_componente["DESCRIP1"]."'";
                    $array_componentes["COSTOADU1"]    = 0;
                    $array_componentes["TIPODET"]      = 4;
                    $array_componentes["COMISTIP"]     = 3;
                    $array_componentes["COMPONENTE"]   = 1;

                    
                    $campos_componentes = implode(", ", array_keys($array_componentes));

                    $valores_componente = implode(", ", array_map(function($valor) {
                        return $valor === null ? "NULL" : $valor;
                    }, $array_componentes));

                    $query_componentes = "INSERT INTO TRANSACCDETALLES
                    ($campos_componentes)
                    VALUES ($valores_componente); ";

                    $sentencia_componentes = $base_de_datos->prepare("$query_componentes");
                    $sentencia_componentes->execute();



                    $query_alm_componente = "UPDATE INVENTARIO SET EXISTENCIA$txt_CodAlmacen=isnull(EXISTENCIA$txt_CodAlmacen,0)-$cantidad_componente WHERE CODPRO='$cod_prod_comp';";
                    $sentencia = $base_de_datos->prepare("$query_alm_componente");
                    $resultado = $sentencia->execute();           

                }
            }
            
            if(!$es_producto_compuesto AND !$es_servicio){
                if($codempaque != ""){

                    $query2 = "UPDATE INVENTARIO SET EXISTENCIA$txt_CodAlmacen=isnull(EXISTENCIA$txt_CodAlmacen,0)-".$empaque["CANTIDAD_EMP"]." WHERE CODPRO='$cod_prod';";
                }else{
                    $query2 = "UPDATE INVENTARIO SET EXISTENCIA$txt_CodAlmacen=isnull(EXISTENCIA$txt_CodAlmacen,0)-$cantidad WHERE CODPRO='$cod_prod';";
                }
                $sentencia = $base_de_datos->prepare("$query2");
                $resultado = $sentencia->execute();           

            }
            if ($nota != '') {
                $query = "INSERT INTO TRANSACCAMPLIADA (CONTROL,FECHORA,DESAPLIADA)  
            VALUES  ('$cal_contro_uni','$cal_contro_uni_det','$nota');";
                //echo "$query";
                $sentencia = $base_de_datos->prepare("$query");
                $resultado = $sentencia->execute();
            }
        }

        /** se calcula el valor del descuento */

        $desc_global_frm = str_replace("%", "", $desc_global);
        $pordes_global = 0;
        $montobru = $monto_subtotal_ms;
        if ($desc_global_frm > 0) 
        {
            $posicion_coincidencia = strpos($desc_global, '%');

            if ($posicion_coincidencia === false) 
            {
                $pordes_global  = ($desc_global * 100) / ($monto_subtotal_ms);
                $montodes_ms   += $desc_global;
                $montodes_      = $desc_global;
            }
            else
            {
                $pordes_global     = str_replace("%", "", $desc_global);
                $desc_global = (float)(($pordes_global * $monto_subtotal_ms)/100);
                $montodes_ms   += $desc_global;
                $montodes_      = $desc_global;
            }

            $monto_total_ms    = (float)($monto_subtotal_ms - $montodes_ms)  + $monto_impuesto_subtotal_ms;
            $desc_global_porc  = (float)($monto_subtotal_ms*$pordes_global/100);
            $montodes_ms       = $montodes_ = (float)($monto_subtotal_ms*$pordes_global/100);
            $monto_subtotal_ms = (float)$monto_subtotal_ms-(float)($desc_global_porc);
            $itbm_porc = ($monto_impuesto_subtotal_ms*$pordes_global/100);

            $monto_impuesto_subtotal_ms = (float)round( $monto_impuesto_subtotal_ms - $itbm_porc , 2);
            $monto_total_ms             = (float)($monto_subtotal_ms)  + $monto_impuesto_subtotal_ms;
            $monto_baseimponible_ms     = (float) $monto_subtotal_ms;
        }
        else
        {
            $desc_global  = (float)($monto_subtotal_ms - $montodes_ms)  + $monto_impuesto_subtotal_ms;
        }

        $nro_            = str_pad($nro_correlativo,10,"0",STR_PAD_LEFT);
        $nro_correlativo = $prefijo_factura.$nro_;

        $pagado = 0;
        
        foreach ($formas_pago as $forma_pago ) {
            $control_fp     = cadena_control();
            $exp_control_fp = explode('|', $control_fp);
    
            $dias_fp                 = $exp_control_fp[0];
            $hora_actual_fp          = $exp_control_fp[1];
            $aleatorio_fp            = $exp_control_fp[2];
            $fecha_actual_clarion_fp = $exp_control_fp[3];
            $fecha_actual_ymd_fp     = $exp_control_fp[4];
            $hora_actual_clarion_fp  = $exp_control_fp[1];
            $cal_contro_fp           = "$dias_fp$hora_actual_fp$aleatorio_fp" . "01";
            $sql_fp                  = "SELECT b.CODTAR , b.NOMBRE , b.FUNCION  FROM BASEINSTRUMENTOS b WHERE b.CODTAR = '".$forma_pago['id']."';";
            $result_fp               = $base_de_datos->query($sql_fp);
            $INTRUMENTO              = $result_fp->fetch(PDO::FETCH_ASSOC);
            $pagado                  = $pagado + $forma_pago["value"];
            
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
            $sql_transaccpagos = " INSERT INTO TRANSACCPAGOS (CONTROL, CONTROLPAGO, CODTAR, MONTOPAG, FECEMIS, FUNCION, 
            EXPRESADOEN, PORRET, PORIMP, MONTOPAGREF, DESDEMODULO, PORIGTF, TASABC,  IGTF)
                    VALUES('$cal_contro_uni', '$cal_contro_fp', '".$INTRUMENTO["CODTAR"]."', '".$forma_pago["value"]."','$fecha_actual_clarion_fp', '".$INTRUMENTO["FUNCION"]."', 
                    0, 0, 0, 0, 'RETAILSPOS', 0, 0,  0);";
            $base_de_datos->query($sql_transaccpagos);

        }
        /*********************************************************/
        $parcontrol = $_SESSION['id_control'];
        if ($nom_cliente_tarea != '') {
            $nomcli = $nom_cliente_tarea;
        }

        if($tipo_factura == "CREDITO"){
            $fechaInicial = new DateTime($fecha_actual_ymd);
            $fechaInicial->modify("+" . $dias_cre . " days");
            $diasvenc     = $fechaInicial->format("Y-m-d");
            $fechaVencs   = $fechaInicial->format("Ymd");
            $FechaClarion = calculoFechasClarion($diasvenc);
            $total_exentas = round((float)$monto_baseimponible_ms, 2);
            $base_imponible = 0;

        }else{
            $dias_cre     = 0;
            $fechaVencs   = $fecha_actual_ymd;
            $FechaClarion = $fecha_actual_clarion;
        }
        
        if($CONESPECIAL == "1")
        {
            switch ($PORRETIMP) {
                case 100:
                $ValRetenc = (float)$monto_impuesto_subtotal_ms; 
                break;
                case 50:
                $ValRetenc = (float)$monto_impuesto_subtotal_ms*0.5; 
                break;                
                default:
                $ValRetenc = ((float)$monto_impuesto_subtotal_ms*($PORRETIMP/100)); 
                break;
            }
            
            if($tipo_factura == "CREDITO" ){
                $MONTOSALDO = $MONTOSALDO - $ValRetenc;

            }
        }
        if($es_producto_exento){
            $base_imponible = 0;
            $total_exentas = round((float)$monto_baseimponible_ms, 2);
                
        }else{  
            $base_imponible = round((float)$monto_baseimponible_ms, 2);
            $total_exentas = 0;
        }
        
        $valores = array(
            "CONTROL"                => "'$cal_contro_uni'",
            "TIPREG"                 => 1,
            "CODIGO"                 => "'$nropedcli'",
            "TIPTRAN"                => "'$tip_tran'",
            "NUMREF"                 => "'$nro_correlativo'",
            "DESCRIP1"               => "'Factura $nro_correlativo'",
            "FECEMIS"                => "'$fecha_actual_clarion'",
            "FECEMISS"               => "'$fecha_actual_ymd'",
            "DIASVEN"                => "'{$dias_cre}'",
            "FECVENC"                => "'$FechaClarion'",
            "FECVENCS"               => "'$fechaVencs'",
            "MONTOBRU"               => round((float)$montobru, 2),
            "MONTODES"               => $montodes_ms,
            "PORDES"                 => $pordes_global,
            "PORIMP"                 => $porimp,
            "MONTOSUB"               => round((float)($monto_subtotal_ms), 2),
            "MONTOIMP"               => round((float)$monto_impuesto_subtotal_ms, 2),
            "MONTOTOT"               => round((float)$monto_total_ms, 2),
            "MONTOSAL"               => round((float)$MONTOSALDO, 2),
            "MONTOEFE"               => round((float)$MONTOEFE, 2),
            "MONTOCHE"               => round((float)$MONTOINTS1, 2),
            "MONTOTAR"               => round((float)$MONTOINTS2, 2),
            "NOMBRE"                 => "'$nomcli'",
            "MARCA"                  => 0,
            "CONTADOR"               => 1,
            "TOTCONTADOR"            => 2,
            "CONTROLDOC"             => "'$cal_contro_uni'",
            "MONTOPAGF"              => round((float)$pagado, 2),
            "RIF"                    => "'$rif  '",
            "NIT"                    => "'$nit'",
            "MONTOCOS"               => $MONTO_COSTO_TOTAL,
            "TIPODOC"                => 0,
            "CAMBIO"                 => $monto_cambio,
            "CODVEN"                 => "'$vendedor_cod'",
            "TIPOCLI"                => "'$tipocli'",
            "COMISV"                 => 1,
            "DIRECCION"              => "'$dircli'",
            "CODALENT"               => "'$almacen_cod'",
            "ACTBANCO"               => 0,
            "MONTODESCUENTO"         => sprintf('%.2f',$monto_descuento_ms),
            "HORA"                   => "'$hora_actual_clarion'",
            "DEVUELTA"               => 0,
            "CODUSER"                => "'$coduser'",
            "BASEIMPONIBLE"          => $base_imponible,
            "TOTALEXENTAS"           => $total_exentas,
            "BASEIMPONIBLEIVA"       => $base_imponible,
            "FACTORCAMBIO"           => 1,
            "SIGNOMONEDA"            => "'$signo_moneda'",
            "MONTOINSTRU1"           => round((float)$MONTOCHE, 2),
            "MONTOINSTRU2"           => round((float)$MONTOTAR, 2),
            "NROCONTRATO"            => "''",
            "IDCAJERO"               =>  "'$coduser'",
            "FECULTIMOPAGO"          => "'$fecha_actual_clarion'",
            "DIRECCION2"             => "'$dircli2'",
            "TELEFCLIEV"             => "'$telefono_cliente'",
            "DESDEMODULO"            => "'RETAILSPOS'",
            "TIPOFACTURA"            => "'$tipo_factura'",
            "BASEPARARET_ISLR"       => round((float)$monto_baseimponible_ms, 2),
            "PARCONTROL"             => $parcontrol,
                        );  
            // Nombre de la tabla y el campo a validar
        $tabla = "TRANSACCMAESTRO";
        $campo = "VALIDOREIMPRIMIR";

        // Consulta SQL para verificar si el campo existe
        $sql = "SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$tabla' 
            AND COLUMN_NAME = '$campo'";;

        $stmt = $base_de_datos->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            $valores["VALIDOREIMPRIMIR"] = "''";
        }  
                        
        $valores_pago = $valores;
        
        $campos_insert = implode(", ", array_keys($valores));
        $sql_insert    = implode(", ", array_map(function($valor) {
            return $valor === null ? "NULL" : $valor;
        }, $valores));
        $ValRetenc = 0; 
        try
        {
            $conex = new PDO("sqlsrv:server=$rutaServidor;database=$nombreBaseDeDatos", $usuario, $clave);
            //$conex->beginTransaction();
            $sql = "INSERT INTO TRANSACCMAESTRO
            ($campos_insert)
            VALUES ($sql_insert); ";
            $sql_txt = $sql;
            $conex->exec("$sql;");
            //echo "<br /><br />".$query;
            //$conex->exec("$sql"); 
            if($CONESPECIAL == "1" AND ($PORRETIMP > 0 AND $PORRETIMP <=100) AND $monto_impuesto_subtotal_ms > 0 ){
                

                switch ($PORRETIMP) {
                    case 100:
                    $ValRetenc = (float)$monto_impuesto_subtotal_ms; 
                    break;
                    case 50:
                    $ValRetenc = (float)$monto_impuesto_subtotal_ms*0.5; 
                    break;                
                    default:
                    $ValRetenc = ((float)$monto_impuesto_subtotal_ms*($PORRETIMP/100)); 
                    break;
                }
                $valores_ret  = $valores;
            
                $cadena_retencion  = cadena_control_retencion();
                $exp_control_retencion = explode('|', $cadena_retencion);
        
                $dias_ret                 = $exp_control_retencion[0];
                $hora_actual_ret          = $exp_control_retencion[1];
                $aleatorio_ret            = $exp_control_retencion[2];
                $fecha_actual_clarion_ret = $exp_control_retencion[3];
                $fecha_actual_ymd_ret     = $exp_control_retencion[4];
                $hora_actual_clarion_ret  = $exp_control_retencion[1];
                $cal_contro_ret           = "$dias_ret$hora_actual_ret$aleatorio_ret" . "01";
        
                $hora_actual_clarion_retencion  = $exp_control_retencion[1];
                //Se se usa el mismo array de maestro para grabar el cobro
                $correlativo_ret = (string)$nro_correlativo."RI";
                $valores_ret["CONTROL"]          = "'$cal_contro_ret'";
                $valores_ret["CODIGO"]           = "'$nropedcli'";
                $valores_ret["CONTROLDOC"]       = "'$cal_contro_uni'";
                $valores_ret["TIPTRAN"]          = "'N/CxIMP'";
                $valores_ret["NUMDOC"]           = "'$nro_correlativo'";
                $valores_ret["NUMREF"]           = "'$correlativo_ret'";
                $valores_ret["DESCRIP1"]         = "'RETENCIÓN DE IMPUESTOS - Factura  $nro_correlativo'";
                $valores_ret["DESCRIP2"]         = "'Por ".round($pagado, 2)."'";
                $valores_ret["DIASVEN"]          = 0;
                $valores_ret["MONTOBRU"]         = round($montobru, 2);
                $valores_ret["MONTOEFE"]         = round($montobru, 2);
                $valores_ret["MONTOSUB"]         = round($montobru, 2);
                $valores_ret["MONTOTOT"]         = round($ValRetenc, 2);
                $valores_ret["MONTOIMP"]         = round((float)$ValRetenc, 2);
                $valores_ret["CONTADOR"]         = 1;
                $valores_ret["TOTCONTADOR"]      = 2;
                $valores_ret["MONTOPAGF"]        = round($montobru, 2);
                $valores_ret["MONTOCOS"]         = 0;
                $valores_ret["MONTOSAL"]         = 0;
                $valores_ret["COMISV"]           = 1;
                $valores_ret["HORA"]             = "$hora_actual_clarion_retencion";
                $valores_ret["BASEIMPONIBLEIVA"] = round($montobru, 2);
                $valores_ret["NROCONTRATO"]      = "''";
                
                
                $campos_insert_ret = implode(", ", array_keys($valores_ret));
                $sql_insert_ret = implode(", ", array_map(function($valor) {
                    return $valor === null ? "NULL" : $valor;
                }, $valores_ret));
        
                 $sql_retencion = "INSERT INTO TRANSACCMAESTRO
                ($campos_insert_ret)
                VALUES ($sql_insert_ret); ";

                $sql_txt .= $sql_retencion;
                $conex->exec("$sql_retencion");

            }

            if($tipo_factura == "CONTADO"){
                $cadena_cobro  = cadena_control_pago();
                $exp_control_cobro = explode('|', $cadena_cobro);
                $pagado = $pagado - $ValRetenc;
        
                $hora_actual_clarion_cobro  = $exp_control_cobro[1];
                //Se se usa el mismo array de maestro para grabar el cobro
                $valores_pago["CONTROL"]          = "'$pago_contro_uni'";
                $valores_pago["CONTROLDOC"]       = "'$cal_contro_uni'";
                $valores_pago["TIPTRAN"]          = "'PAGxFAC'";
                $valores_pago["NUMDOC"]           = "'$nro_pago'";
                $valores_pago["CODIGO"]           = "'$nropedcli'";
                $valores_pago["DESCRIP1"]         = "'Cobro factura  $nro_correlativo'";
                $valores_pago["DESCRIP2"]         = "'Por ".round($pagado, 2)."'";
                $valores_pago["DIASVEN"]          = 0;
                $valores_pago["MONTOBRU"]         = round($pagado, 2);
                $valores_pago["MONTOSUB"]         = round($pagado, 2);
                $valores_pago["MONTOTOT"]         = round($pagado, 2);
                $valores_pago["MONTOIMP"]         = 0;
                $valores_pago["CONTADOR"]         = 2;
                $valores_pago["TOTCONTADOR"]      = 0;
                $valores_pago["MONTOPAGF"]        = 0;
                $valores_pago["MONTOCOS"]         = 0;
                $valores_pago["MONTOSAL"]         = 0;
                $valores_pago["COMISV"]           = 0;
                $valores_pago["HORA"]             = "$hora_actual_clarion_cobro";
                $valores_pago["BASEIMPONIBLEIVA"] = 0;
                $valores_pago["NROCONTRATO"]      = "''";
                $valores_pago["TIPOFACTURA"]      = "''";
                
                
                $campos_insert_pago = implode(", ", array_keys($valores_pago));
                $sql_insert_pago = implode(", ", array_map(function($valor) {
                    return $valor === null ? "NULL" : $valor;
                }, $valores_pago));
        
                $sql_pago = "INSERT INTO TRANSACCMAESTRO
                ($campos_insert_pago)
                VALUES ($sql_insert_pago) ;";
                $conex->exec("$sql_pago");

                $sql_txt .= $sql_pago;
                
                $query4 = "UPDATE BASEEMPRESA SET NROINIREC = (NROINIREC + 1) WHERE CONTROL='" . $_SESSION['id_control'] . "';";

                $conex->exec("$query4");

            }
            
            //$conex->exec("$sql_txt;");
            //$conex->commit();
        } catch (PDOException $e) {
            $conex->rollback();
            echo "0| Mensaje: " . $e->getMessage();
        }
        
        foreach ($arr_impuestos as $it) {
            $res_cadena  = cadena_control();
            $exp_control = explode('|', $res_cadena);

            $dias        = $exp_control[0];
            $hora_actual = $exp_control[1];
            $aleatorio   = $exp_control[2];
              /*cadena de calculo control unico impuesto*/
            $cal_contro_uni_imp = "$dias$hora_actual$aleatorio" . "01";
            
            $query10 = $base_de_datos->prepare("SELECT a.CODIGOIMP FROM BASEIMPUESTOS AS a WHERE a.VALORIMP=:valimp;");
            $query10->bindParam("valimp", $it, PDO::PARAM_STR);
            $query10->execute();
            $result10 = $query10->fetch(PDO::FETCH_ASSOC);

            if ($result10) {
                $cod_imp = $result10['CODIGOIMP'];
            }

            $query = "INSERT INTO TRANSACCIMPUESTOS (CONTROL,CONTROLIMP,VALORIMP,BASEIMPUESTO,
            MONTOIMPUESTO,SUMABASE,SUMAIMP,CODIGOIMP, MONTODESCUENTOS, MONTOCOSTOS)  
            VALUES  ('$cal_contro_uni','$cal_contro_uni_imp',$it," . $mack_subtotal[round($it)] . ",
            " . $mack_subtotal_imp[round($it)] . ",1,1,$cod_imp,".round(sprintf('%.2f',$monto_descuento_ms), 2).",".$MONTO_COSTO_TOTAL.");";
            //echo "$query";
            $sentencia = $base_de_datos->prepare("$query");
            $resultado = $sentencia->execute();
        }

        //insertando notas
        $obv = str_replace("%23", "#", $_GET['obv']);
        if ($obv != '') {
            $query = "INSERT INTO TRANSACCOBSERVACIONES (CONTROL,OBS1) VALUES  ('$cal_contro_uni','$obv');";
            //echo "$query";
            $sentencia = $base_de_datos->prepare("$query");
            $resultado = $sentencia->execute();
        }
        //}
        file_put_contents("1sql.txt", $sql_txt);

        $_SESSION['aDatos'] = array();
        unset($_SESSION['tipo_tarea']);



        $tabla = "FELINNOVA";
        $columna = "PARCONTROL";

        $query = "SELECT COUNT(*) as tableExists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?;";
        $stmt  = $base_de_datos->prepare($query);
        $stmt->execute([$tabla]);
        $row         = $stmt->fetch(PDO::FETCH_ASSOC);
        $tableExists = $row['tableExists'];

        $estado = 1;

        if ($tableExists) {
             // Validar si la columna existe
            $queryColumna = "SELECT COUNT(*) as columnExists 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = ? AND COLUMN_NAME = ?;";
            $stmtColumna = $base_de_datos->prepare($queryColumna);
            $stmtColumna->execute([$tabla, $columna]);
            $rowColumna = $stmtColumna->fetch(PDO::FETCH_ASSOC);
            $columnExists = $rowColumna['columnExists'];
            $url = "0";
            $response = json_encode(array("estado"=> 0, "mensaje" => "     "));
            $response_error = json_encode(array("estado"=> 0, "mensaje" => "     "));

            if ($columnExists) {
                // Consulta SQL para obtener los campos de la tabla
                $sql = "SELECT A.*, B.NROINIFAC FROM FELINNOVA as A LEFT JOIN BASEEMPRESA AS B ON B.CONTROL = A.PARCONTROL WHERE  A.PARCONTROL = '".trim($_SESSION["id_control"])."';";;
            
                // Ejecutar la consulta
                $stmt = $base_de_datos->query($sql);
                $campos = $stmt->fetch(PDO::FETCH_ASSOC);

                // Determina el protocolo: HTTP o HTTPS.
                if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
                    $baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
                } else {
                    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
                }

                $proyectoRuta     = $_SERVER['REQUEST_URI'];
                $nombreDirectorio = dirname($proyectoRuta);
                $control = base64_encode($cal_contro_uni);
                $post_fields = "control=" . urlencode($control) . "&parcontrol=" . urlencode($parcontrol);
                $url = "0";
                $response = json_encode(array("estado"=> 0, "mensaje" => "     "));
                $response_error = json_encode(array("estado"=> 0, "mensaje" => "     "));
                if($campos){                

                    // Define la URL en función del valor de $campos["PAC"]
                    if ($campos["PAC"] == 1 || $campos["PAC"] == 2) {
                        $url = $baseUrl . $nombreDirectorio . '/fel/thfkapanama/factura.php';
                    } elseif ($campos["PAC"] == 3) {
                        $url = $baseUrl . $nombreDirectorio . '/fel/digifact/enviarDigifact.php';
                    } else {
                        // Si no es ninguno de los valores esperados, sal del script.
                        $url = null;
                        $response_error = json_encode(array("estado"=> 0, "mensaje" => 'PAC no válido.'));

                    }
                    if($url != NULL){

                        $url = str_replace('\/', '/', $url);
            
                        $curl = curl_init();
            
                        // Desactiva temporalmente la verificación SSL (solo para pruebas).
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            
                        // Sigue las redirecciones si las hay.
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            
                        // Modo verbose para depuración.
                        curl_setopt($curl, CURLOPT_VERBOSE, true);
                        $verbose = fopen('php://temp', 'w+');
                        curl_setopt($curl, CURLOPT_STDERR, $verbose);
            
                        curl_setopt_array($curl, [
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 1,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => $post_fields,
                            CURLOPT_HTTPHEADER => [
                                "Content-Type: application/x-www-form-urlencoded"
                            ],
                        ]);
            
                        $response = curl_exec($curl);
                        if ($response === FALSE) {
                            //printf("cURL error: %s\n", curl_error($curl));
                            //rewind($verbose);
                            //$verboseLog = stream_get_contents($verbose);
                            $estado = 0;
                            $response_error = json_encode(array("estado"=> 0, "mensaje" => "Error al realizar enviar la factura electrónica"));
                            //ob_clean();
                            //print_r($response);exit;
                            //echo "0|Error al realizar enviar la factura electrónica";
                        }    
                        curl_close($curl);
                        $response = str_replace("|", "", $response);
                    }
                    else
                    {
                        $estado = 0;
                        //$response_error = json_encode(array("estado"=> 0, "mensaje" => 'PAC no válido.'));
                    }

                }
            }
        }
        $base_de_datos = null;


        if($estado)
            echo "1|$cal_contro_uni|$nro_correlativo|$tip_tran|$url|".($response);
        else
            echo "1|$cal_contro_uni|$nro_correlativo|$tip_tran|$url|".($response_error);
    } else {
        echo "0|Los siguientes productos sobrepasan la existencia en almacen: $flag_nom";
    }
    } catch (PDOException $e) {
        echo "0| " . $e->getMessage();
    }

/*echo "
        <script>
        window.location='doc_pdf_demo.php.php?idcontrol=$cal_contro_uni&idfac=$nro_correlativo&tiptran=$tip_tran';
        </script>
        ";*/

function cadena_control()
{
    /*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime(date("Y-m-d H:i:s"));

    $diff = $fecha1->diff($fecha2);
    $dias = $diff->days;
    $fecha_actual_clarion = $diff->days;
    $fecha_actual_ymd = date("Ymd");

    /*generando hora actual en codigo clarin*/
    /*$aux =  microtime(true);
    $now = DateTime::createFromFormat('U.u', $aux);        
    if (is_bool($now)){
        $now = DateTime::createFromFormat('U.u', $aux += 0.001);
    }*/
    //$now = DateTime::createFromFormat("U.u", microtime(true));
    //$hora_actual = ($now->format("H")*360000)+($now->format("i")*6000)+($now->format("s")*100)+($now->format("u")*10)+1;
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

    /*generando numero aleatorio entre 10000 y 99999*/
    //$aleatorio = mt_rand(10000, 89999);

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
function cadena_control_pago()
{
    /*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime(date("Y-m-d H:i:s"));

    $diff = $fecha1->diff($fecha2);
    $dias = $diff->days;
    $fecha_actual_clarion = $diff->days;
    $fecha_actual_ymd = date("Ymd");

    /*generando hora actual en codigo clarin*/
    /*$aux =  microtime(true);
    $now = DateTime::createFromFormat('U.u', $aux);        
    if (is_bool($now)){
        $now = DateTime::createFromFormat('U.u', $aux += 0.001);
    }*/
    //$now = DateTime::createFromFormat("U.u", microtime(true));
    //$hora_actual = ($now->format("H")*360000)+($now->format("i")*6000)+($now->format("s")*100)+($now->format("u")*10)+1;
    $hora_actual = (date('H') * 360000) + (date("i") * 6000) + (date("s") * 100) + ((date("v") / 10)+5) + 1;
    if (strlen($hora_actual) == 7) {
    } else if (strlen($hora_actual) > 7) {
        $hora_actual = substr($hora_actual, 0, 7);
    } else if (strlen($hora_actual) < 7) {
        $res = 7 - strlen($hora_actual);
        for ($t = 1; $t <= $res; $t++) {
            $hora_actual = "0" . $hora_actual;
        }
    }

    /*generando numero aleatorio entre 10000 y 99999*/
    //$aleatorio = str_pad(mt_rand(90000, 94999), 5, "0", STR_PAD_LEFT);

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

function cadena_control_retencion()
{
    /*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime(date("Y-m-d H:i:s"));

    $diff = $fecha1->diff($fecha2);
    $dias = $diff->days;
    $fecha_actual_clarion = $diff->days;
    $fecha_actual_ymd = date("Ymd");

    /*generando hora actual en codigo clarin*/
    /*$aux =  microtime(true);
    $now = DateTime::createFromFormat('U.u', $aux);        
    if (is_bool($now)){
        $now = DateTime::createFromFormat('U.u', $aux += 0.001);
    }*/
    //$now = DateTime::createFromFormat("U.u", microtime(true));
    //$hora_actual = ($now->format("H")*360000)+($now->format("i")*6000)+($now->format("s")*100)+($now->format("u")*10)+1;
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

    /*generando numero aleatorio entre 10000 y 99999*/
    //$aleatorio = str_pad(mt_rand(95000, 99999), 5, "0", STR_PAD_LEFT);

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
function calculoFechasClarion( $fecha )
{
    /*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime($fecha);

    $diff = $fecha1->diff($fecha2);

    return $diff->days;
}

function formatear_texto($texto)
{
    $texto = str_replace("%23", "#", $texto);
    $texto = str_replace("%26", "&", $texto);
    $texto = str_replace("%27", "'", $texto);
    return $texto;
}
