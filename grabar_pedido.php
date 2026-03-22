<?php 
include_once "permiso.php";
//session_start();
include_once "config/db.php";
$nropedcli=$_GET['nropedcli'];
$personas=$_GET['personas'];
$mesa=$_GET['mesa'];

$sum_tot_items_exento=0;
$sum_tot_items_noexento=0;
$monto_subtotal=0;
$monto_total=0;
$monto_impuesto_subtotal=0;
$monto_total_ms=0;
$monto_subtotal_ms=0;
$monto_impuesto_subtotal_ms=0;
$monto_pagf=0;
$monto_efectivo_ms=0;
$monto_descuento_ms=0;

if($nropedcli!==''){
    /*en caso de que el pedido cliente si existe con items*/
}else{
    /*si el numero de pedido cliente no existe hay que generarlo a partir de la tabla base empresa*/
    $sql3="SELECT NROPEDCLI FROM BASEEMPRESA";
    
    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    $result100 = $sentencia4->fetch(PDO::FETCH_ASSOC);
    if($result100){
        $nropedcli=$result100['NROPEDCLI'];

        $query4="UPDATE BASEEMPRESA SET NROPEDCLI = (NROPEDCLI + 1)";
        $sentencia4 = $base_de_datos->prepare("$query4");
        $resultado4 = $sentencia4->execute();
    }
}

if(strlen($nropedcli)<6){
    $res=6-strlen($nropedcli);
    for($t=1; $t<=$res; $t++) {
        $nropedcli="0".$nropedcli;
    }
}

 
$hayclip=false;
$tip_tran="PEDxCLI";
$query10 = $base_de_datos->prepare("SELECT a.CONTROL FROM TRANSACCMAESTRO AS a WHERE a.NUMREF=:nropedcli AND a.TIPTRAN=:tip_tran");
$query10->bindParam("nropedcli", $nropedcli, PDO::PARAM_STR);
$query10->bindParam("tip_tran", $tip_tran, PDO::PARAM_STR);
$query10->execute();
$result10 = $query10->fetch(PDO::FETCH_ASSOC);      

/*consultamos los calculos para generar las variables control con los datos correctos*/
$res_cadena=cadena_control();
$exp_control=explode('|', $res_cadena);

$dias=$exp_control[0];
$hora_actual=$exp_control[1];
$aleatorio=$exp_control[2];
$fecha_actual_clarion=$exp_control[3];
$fecha_actual_ymd=$exp_control[4];
$hora_actual_clarion=$exp_control[1];

if ($result10){
    $cal_contro_uni=$result10['CONTROL'];
    $hayclip=true;
}else{
    /*cadena de calculo control unico*/
    $cal_contro_uni="$dias$hora_actual$aleatorio"."01";            
}


/*************************************************************************************/
/* empezamos a generar todo los datos necesarios para grabar en la tabla detalle     */
/*************************************************************************************/

$max=sizeof($_SESSION['aDatos']);
for($i=0; $i<$max; $i++) {
    $monto_subtotal=0;
    $monto_impuesto=0;
    $pordes=0;
    $monto_descuento=0;
    $cantidad=0;
    $precio=0.00;
    $itbm="0";


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
    
    /*variable utilizadas en la tabla transacmaestro*/
    if($itbm>0){
        $sum_tot_items_exento+=1;
    }else{
        $sum_tot_items_noexento+=1;
    }

    if(($tipo_items=='CB') OR ($tipo_items=='AD')){ // aplica para contornos y combos adicionales
        $estatus="ST_SLA";
        $cantidad=1; // se coloca uno para reconfirmar que debe ser una solo items
        if($adicion_costo==''){
            $monto_subtotal='0';
            $monto_impuesto='0';
            $precosuni='0.00';

            /*parametros para uso de tabla maestro*/
            $precio='0.00';
            $monto_subtotal_ms+=$precio*$cantidad;
            $monto_total_ms+=($precio*$cantidad)+(($precio*$cantidad)*($itbm/100));
            $monto_impuesto_subtotal_ms+=($precio*$cantidad)*($itbm/100);
        }else{
            $monto_subtotal=$precio*$cantidad;
            $monto_impuesto=($precio*$cantidad)*($itbm/100);
            /*parametros para uso de tabla maestro*/
            $monto_subtotal_ms+=$precio*$cantidad;
            $monto_total_ms+=($precio*$cantidad)+(($precio*$cantidad)*($itbm/100));
            $monto_impuesto_subtotal_ms+=($precio*$cantidad)*($itbm/100);
        }
    }else if(($tipo_items=='HH') OR ($tipo_items=='PR') OR ($tipo_items=='DS')){ // aplica para promociones (happy hours)
        //echo "el registro es una promocion<br />";
        $estatus="ST_NEW";
        /*$monto_subtotal=$precio*$cantidad;
        $monto_impuesto=($precio*$cantidad)*($itbm/100);

        $monto_descuento=$precio*$cant_cobrada_;
        $monto_impuesto=($precio*$cant_cobrada_)*($itbm/100);
        $pordes=($precio*$cantidad)-($precio*$cant_cobrada_);*/

        $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
        $dia_actual=$dias[date("w")];
        $exp_dias_valido=explode(";", $diassemana);

        //$h_inicio=str_replace ( ":", '', $h_inicio);
        //$h_final=str_replace ( ":", '', $h_final);
        $hora_actual=date("Hi");
        //validando si la promocion esta disponible para el dia de hoy
        if ((in_array($dia_actual, $exp_dias_valido, true)) AND (($hora_actual>=$h_inicio) AND ($hora_actual<=$h_final))){
            //echo "esta dentro del rango de dia y hora<br />";
            //validamos si cumple con el horario de la promocion
            //if(($hora_actual>=$h_inicio) AND ($hora_actual<=$h_final)){
                if($tipo_items=='HH'){// para promocion happy hours
                    /*parametros para uso de tabla detalles*/
                    $monto_subtotal=$precio*$cant_cobrada_;
                    $monto_impuesto=($precio*$cant_cobrada_)*($itbm/100);
                    $monto_descuento=$precio*$cant_cobrada_;
                    $monto_impuesto=($precio*$cant_cobrada_)*($itbm/100);
                    $pordes=50; // descuento del 50%
                    /*parametros para uso de tabla maestro*/
                    $sum_tot_items_noexento=$precio*$cant_cobrada_;
                    $sum_tot_items_exento+=0.00;
                    $monto_descuento_ms+=$precio*$cant_cobrada_;
                    $monto_subtotal_ms+=$precio*$cant_cobrada_;
                    $monto_total_ms+=($precio*$cant_cobrada_)+(($precio*$cant_cobrada_)*($itbm/100));
                    $monto_impuesto_subtotal_ms+=($precio*$cant_cobrada_)*($itbm/100);
                }else if($tipo_items=='PR'){// para promocion de descuento de precio
                    /*parametros para uso de tabla detalles*/
                    $monto_subtotal=$cant_cobrada_;
                    $monto_impuesto=0.00;
                    $precosuni=$cant_cobrada_;

                    /*parametros para uso de tabla maestro*/
                    $sum_tot_items_noexento=0;
                    $sum_tot_items_exento+=0;
                    $monto_subtotal_ms+=$cant_cobrada_;
                    $monto_total_ms+=$cant_cobrada_;
                    $monto_pagf+=0.00;
                    $monto_efectivo_ms+=0.00;
                    $monto_impuesto_subtotal_ms+=0.00;
                }else if($tipo_items=='DS'){// promocion para descuento por porcentaje
                    /*parametros para uso de tabla detalles*/
                    $monto_subtotal=$precio-($precio*($cant_cobrada_/100));
                    $monto_descuento=$precio*($cant_cobrada_/100);
                    $monto_impuesto=($precio*($cant_cobrada_/100))*($itbm/100);
                    $pordes=$cant_cobrada_;

                    /*parametros para uso de tabla maestro*/
                    $sum_tot_items_noexento=0;
                    $sum_tot_items_exento+=0.00;
                    $monto_descuento_ms+=$precio*($cant_cobrada_/100);
                    $monto_subtotal_ms+=$precio-($precio*($cant_cobrada_/100));
                    $monto_total_ms+=($precio-($precio*($cant_cobrada_/100)))+(($precio*($cant_cobrada_/100))*($itbm/100));
                    $monto_pagf+=0.00;
                    $monto_efectivo_ms+=0.00;
                    $monto_impuesto_subtotal_ms+=($precio*($cant_cobrada_/100))*($itbm/100);
                }
            //}
        }else{
            //$estatus="ST_NEW";
            $cantidad=1;// se coloca uno para reconfirmar que debe ser una solo items
            $monto_subtotal=$precio*$cantidad;
            $monto_impuesto=($precio*$cantidad)*($itbm/100);
            /*parametros para uso de tabla maestro*/
            $monto_subtotal_ms+=$precio*$cantidad;
            $monto_total_ms+=($precio*$cantidad)+(($precio*$cantidad)*($itbm/100));
            $monto_impuesto_subtotal_ms+=($precio*$cantidad)*($itbm/100);
        }
    }else{
        $cantidad=1;// se coloca uno para reconfirmar que debe ser una solo items
        $estatus="ST_NEW";
        $monto_subtotal=str_replace(",","",$precio)  *$cantidad;
        $monto_impuesto=(str_replace(",","",$precio)*$cantidad)*($itbm/100);
        /*parametros para uso de tabla maestro*/
        $monto_subtotal_ms+=str_replace(",","",$precio)*$cantidad;
        $monto_total_ms+=(str_replace(",","",$precio)*$cantidad)+((str_replace(",","",$precio)*$cantidad)*($itbm/100));
        $monto_impuesto_subtotal_ms+=(str_replace(",","",$precio)*$cantidad)*($itbm/100);
    }

    

    /*extrayendo los datos correctos para generar el control unico de tbl transacdetalle*/        
    $res_cadena=cadena_control();
    $exp_control=explode('|', $res_cadena);

    $dias=$exp_control[0];
    $hora_actual=$exp_control[1];
    $aleatorio=$exp_control[2];
    /*cadena de calculo control unico*/
    $cal_contro_uni_det="$dias$hora_actual$aleatorio"."01";
            
    /*obteniendo el registro con el numero mas alto en el campo lineaRPOS */
    $tip_tran="PEDxCLI";
    $m="Mesa $mesa";
    $query11 = $base_de_datos->prepare("SELECT MAX(t.LineaRPOS) max_linearpos FROM TRANSACCDETALLES AS t WHERE t.CONTROL=:con AND t.TIPTRAN=:tip_tran");
    $query11->bindParam("con", $cal_contro_uni, PDO::PARAM_STR);
    $query11->bindParam("tip_tran", $tip_tran, PDO::PARAM_STR);
    $query11->execute();
    $result11 = $query11->fetch(PDO::FETCH_ASSOC);

    if ($result11) {
        $linearpos=$result11['max_linearpos']+1;

        if($cod_padre==''){
            $_SESSION['iditems_padre2']=$linearpos;
            $valor_constante='-1';
        }else{
            $valor_constante=$_SESSION['iditems_padre2'];
        }
        /*
        else if($cod_padre==$_SESSION['iditems_padre2']){
            $valor_constante=$_SESSION['iditems_padre2']+1;
        }*/
    }else{
        $linearpos=1;
        if($cod_padre==''){
            $_SESSION['iditems_padre2']=$linearpos;
            $valor_constante='-1';
        }else{
            $valor_constante=$_SESSION['iditems_padre2'];
        }
    }

    /*
    if($cod_padre==''){
        $_SESSION['iditems_padre2']=$i;
        $valor_constante='-1';
    }else if($cod_padre==$_SESSION['iditems_padre2']){
        $valor_constante=$_SESSION['iditems_padre2']+1;
    }*/
            
    $tipocliente_nom=$_SESSION['tipocliente_nom'];
    $tipocliente_cod=$_SESSION['tipocliente_cod'];
    $parcontrol=$_SESSION['parcontrol'];
    $query2="INSERT INTO TRANSACCDETALLES (TaliaDocID,FECEMIS,FECEMISS,DEVUELTA,CANTIDADDEV,CONTROL,TIPREG,CODIGO,TIPTRAN,FECHORA,PRECIO,FHPRODBASE,CODPRO,COMPONENTE,CODIGODEP,GRUPOINV,CONLINEA,NROSNEDET,NOMBRE,DESCRIP1,TaliaSeat,TaliaItemState,TaliaItemCState,TaliaCPrinter,TaliaUUID,TaliaComment,TaliaItemLealtad,TaliaItemSpecialPrice,TaliaMesa,CODSER,CANTIDAD,CODVEN,TOTAL,COSTOACT,COSTOPRO,MONTOCOS,PRECOSUNI,PORDES,MONTODESCUENTO,IMPPOR,MONTOIMP,PRECIO1,UTILPRECIO1,PRECIO2,UTILPRECIO2,PRECIO3,UTILPRECIO3,TIPINV,FACCAM1,VALFOB1,COSTOFLE1,COSTOSEG1,VALORCIF1,COSTOARA1,COSTONAC1,COSTOADU1,PAGOCOM1,GASTOADU1,OTROGAS1,COSTOFIN1,NUMPLANILLA,FechaRPOS,FACCAM2,VALFOB2,COSTOFLE2,COSTOSEG2,VALORCIF2,COSTOARA2,COSTONAC2,COSTOADU2,PAGOCOM2,GASTOADU2,OTROGAS2,COSTOFIN2,PRECIOE1,PRECIOE2,PRECIOE3,TIPODET,TIPPRO,COMISVEN,COMISCOB,COMISTIP,CODALREC,CODALENT,FHIMPORTAR,ORIGEN,PORCOMISION,MONTOCOMISION,FACTORCAMBIO,SIGNOMONEDA,IMPPOR2,MONTOIMP2,IMPPOR3,MONTOIMP3,PROCESADO,NUMCONTRATO,NUMTELEFONO,PORRETTAR,MONTORETTAR,MESVENC,FECHAVENCE,LINEAOINV,CODFABRICANTE,CENTROCOSTO,PORCOMISDETAIL,MTOCOMISDETAIL,PORDESGLO,MONTODESCUENTOGLO,TOTAL_INO,LineaRPOS,CANTIDADFAC,PORREC,MONTORECARGA,PORRECARGOGLO,MONTORECARGOGLO,LineaPadreRPOS, PARCONTROL) 
                VALUES  ('','$fecha_actual_clarion','$fecha_actual_ymd',0,0,'$cal_contro_uni',1,'$tipocliente_cod',
                'PEDxCLI','$cal_contro_uni_det',1,'$cal_contro_uni_det','$cod_prod',0,'01','',0,'','$tipocliente_nom',
                '$nom_prod','1','$estatus','NOREAD','','','$comments','',1,'Mesa $mesa','',$cantidad,'01',".round($monto_subtotal, 2).",0.0000,0.0000,0.0000,
                ".str_replace(",","",$precosuni).",".round($pordes, 2).",".sprintf('%.2f', $monto_descuento).",$itbm,".round($monto_impuesto, 2).", ".str_replace(",","",$precio).",0,
                 ".str_replace(",","",$precio).",0, ".str_replace(",","",$precio).",0,0,0.00,0.00,0.00,0.00,0.00,0,0.00,0.00,0.00,0.00,0.00,0.00,'',GETDATE(),
                0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,0,0.00,0.00,0,'','01','',0,0,0,1.00,'',
                0,0,0,0,0,'','',0.00,0.00,0,0,'','','',0.00,0.00,0,0.0000,".round($monto_subtotal, 2).",$linearpos,0.00,0.00,0.00,0.00,0.00,$valor_constante, $parcontrol)";
    //echo "<br /><br />$query2";
    $sentencia = $base_de_datos->prepare("$query2");
    $resultado = $sentencia->execute(); 
            
}// fin for externo
        

/*************************************************************************************/
/* empezamos a generar todo los datos necesarios para grabar en la tabla maestro     */
/*************************************************************************************/
//echo $monto_impuesto_subtotal_ms;
if ($hayclip) {
    /*actualizando la sumatoria de todo los items comandados + los items por comandar*/
    $query18 = $base_de_datos->prepare("SELECT t.MONTOBRU, t.MONTOSUB, t.MONTOIMP, t.MONTOTOT, t.MONTOEFE, t.MONTOPAGF, t.BASEIMPONIBLE, t.BASEIMPONIBLEIVA FROM TRANSACCMAESTRO AS t WHERE t.CONTROL=:con AND t.TIPTRAN=:tip_tran");
    $query18->bindParam("con", $cal_contro_uni, PDO::PARAM_STR);
    $query18->bindParam("tip_tran", $tip_tran, PDO::PARAM_STR);
    $query18->execute();
    $result18 = $query18->fetch(PDO::FETCH_ASSOC);

    if ($result18) {
        $res_monto_subtotal=$monto_subtotal_ms+$result18['MONTOSUB'];
        $res_monto_impuesto_subtotal=$monto_impuesto_subtotal_ms+$result18['MONTOIMP'];
        $res_monto_total=$monto_total_ms+$result18['MONTOTOT'];
        $query="UPDATE TRANSACCMAESTRO SET MONTOBRU=$res_monto_subtotal, MONTOSUB=$res_monto_subtotal, MONTOIMP=$res_monto_impuesto_subtotal, MONTOTOT=$res_monto_total, MONTOEFE=$res_monto_total, MONTOPAGF=$res_monto_total, BASEIMPONIBLE=$res_monto_subtotal, BASEIMPONIBLEIVA=$res_monto_subtotal WHERE CONTROL='$cal_contro_uni' AND TIPTRAN='$tip_tran'";
        $sentencia = $base_de_datos->prepare("$query");
        $resultado = $sentencia->execute();
    }else{
        /*esta variable se usara en la tabla saordenes_talia*/
        $res_monto_total=$monto_total_ms;
    }
}else{
    $res_monto_total=$monto_total_ms;
    $tipocliente_cod=$_SESSION['tipocliente_cod'];
    $tipocliente_nom=$_SESSION['tipocliente_nom'];
    $parcontrol=$_SESSION['parcontrol'];
    $query="INSERT INTO TRANSACCMAESTRO (CodEsta,FechaRPOS,VencRPOS,TaliaDocID,TaliaUsaCarnet,TaliaDocName,TaliaLealtad,TaliaAbono,MONTODESCUENTO,CONTROL,CONTROLDOC,NUMREF,NUMPRE,NUMDOC,NUMREFDEV,TIPTRAN,TIPREG,SIGNOMONEDA,CODIGO,RIF,NIT,FECEMIS,FECEMISS,FECVENC,FECVENCS,DIASVEN,HORA,CODVEN,TIPOCLI,CONTADOR,TOTCONTADOR,FECULTIMOPAGO,MONTOPAGF,TIPOFACTURA,DESDEMODULO,DESCRIP1,DESCRIP2,CODTAR,ODC,TIPOPRO,NUMCHE,TIPODOC,NOMBRE,DIRECCION,DIRECCION2,COMISV,COMISC,TELEFCLIEV,MONTOBRU,MONTOSUB,MONTOIMP,BASEIMPONIBLE,BASEIMPONIBLEIVA,BASEPARARET_ISLR,TOTALEXENTAS,MONTOCOS,ComoID,CoronaID,CoronaPago,MONTOTOT,MONTOEFE,MONTOCHE,MONTOSAL,MONTOTAR,CAMBIO,MONTODES,Cortesia,Notas1,Notas2,Notas3,Notas4,NUMINSTRU1,CODBANINSTRU1,CODTARINSTRU1,MARCA,MONTOINSTRU2,NUMINSTRU2,CODBANINSTRU2,CODTARINSTRU2,FUNCION1,FUNCION2,MONTOIMP2,PORIMP2,MONTOIMP3,PORIMP3,CODCOB,NROCONTRATO,NOMTIT,MONTOAPAGARPAC,IDCAJERO,NOMMED,NROCLAVE,TEMP_BASE_IMP,TEMP_PORIMP,MODELOIMP,SERIALIMP,COM_FISCAL,COM_FISCAL_Z,NROCONTROLCOMPRA,MONTOFLETE,NROPTOEMISION,NROFACTURARD,PORDES,MONTORECARGO,NUMEROSRI,NROSERIE,CORTEX,PORFLETE,PORIMPR,MONTOIMPR,PORIMPL,MONTOIMPL,BASEIMPONIBLER,BASEIMPONIBLEL,SUSTRAENDOPARARET_ISLR,CODRET_OTR1,BASEPARARET_OTR1,MONTORET_OTR1,PORCENTAJERET_OTR1,SUSTRAENDOPARARET_OTR1,TOTALMONTORET_OTR1,CODRET_OTR2,BASEPARARET_OTR2,MONTORET_OTR2,PORCENTAJERET_OTR2,SUSTRAENDOPARARET_OTR2,TOTALMONTORET_OTR2,OPERACIONES,FUNCIONTAR,FUNCIONINSTRU1,FUNCIONINSTRU2,IDTABLAADICIONAL,KILOMETROS,DIASPROXREVIS,DIASFIN1,PORFIN1,DIASFIN2,PORFIN2,DIASFIN3,PORFIN3,MONTONCTRANSITO,CODRESPCOMP,NOMRESPCOMP,NROSINIESTRO,TIPOREGIMEN,CODCLI,NROAUTORIZA,CENTROCOSTO,NOMPACIENTE,CEDPACIENTE,DIRPACIENTE,AFECTALIBRO,COMORETIMP,CEDTIT,CODALENT,CODALREC,CODUSER,DEVUELTA,ACTBANCO,MARCARE,MONTOMANEJO,MONTOINTERES,GIROS,MONTOGIROS,CONTROLDEV,CODBANCO,CONTROLCH,CODDEV,CONTROLGIR,FACTORCAMBIO,PORRETIMP,MONTORETIMP,NROCONTROLDOC,NROCOMRET,MONTOINSTRU1,CODRET,OTRAPLAZA,MONTORET,PORRET,MONTOPA,COMISVEN,COMISCOB,FECHAENTREGA,FECHAAUTORIZA,FECHASRI,FECHAVENCESRI,FECPROXREVIS,FECDOCORIG,FECVENCDOCORIG,FECHAEMISIONCOMPRA,Comensales,Credito,TaliaPagoAbono, PARCONTROL) 
                VALUES  ('00',GETDATE(),GETDATE(),'',0,'','',0,".sprintf('%.2f', $monto_descuento_ms).",'$cal_contro_uni','$cal_contro_uni',
                '$nropedcli','','','','PEDxCLI',1,'Balboa','$tipocliente_cod','$tipocliente_cod','',
                $fecha_actual_clarion, '$fecha_actual_ymd',$fecha_actual_clarion,'$fecha_actual_ymd',0,
                $hora_actual_clarion,'01','Contribuyente',1,0,0,".round($monto_pagf, 2).",'','RETAILSPOS',
                'PEDIDO CLIENTE $nropedcli','','','','','',0,'$tipocliente_nom','PANAMA','',0,0,'',
                ".round($monto_subtotal_ms, 2).",".round($monto_subtotal_ms, 2).",".round($monto_impuesto_subtotal_ms, 2).",$sum_tot_items_noexento,
                $sum_tot_items_noexento,0.00,$sum_tot_items_exento,0,'','',0,".round($monto_total_ms, 2).",".round($monto_efectivo_ms, 2).",0,0,0,0,0.00,0,'',
                'Mesa $mesa',NULL,'','','','',0,0.00,'','','',0,0,0.00,0.00,0.00,0.00,'','','',0.00,'002','','',0.00,0.00,'','','',0,'',
                0.00,'','',0,0.00,'','','',0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'',0.00,0.00,0.00,0.00,0.00,'',0.00,0.00,0.00,0.00,0.00,'',0,0,0,
                '',0,0,0,0.00,0,0.00,0,0.00,0.00,'','','',0,0,'','','','','',0,0,'','','','002',0,0,0,0.00,0.00,0,0.00,'','','','','',1.00,0.00,0.00,
                '','',0.00,'',0,0.00,0.00,0,0.00,0.00,0,0,0,0,0,0,0,0,$personas,0,0, $parcontrol)";
    //echo $query;
    $sentencia = $base_de_datos->prepare("$query");
    $resultado = $sentencia->execute();
}
/*************************************************************************************/
/* empezamos a generar todo los datos necesarios para actualizar en la tabla         */  
/* SAORDENES_TALIA                                                                   */
/*************************************************************************************/

/*generar los cseats*/
$cseats="";
for($i=0; $i<$personas; $i++) {
    $cseats.="SSO:";
}
        
$cseats=substr(trim($cseats), 0, -1);

/*generar formula CDI*/
$fecha_actual_ymd=date("mdHis");
$DesdeLetra = "A";
$HastaLetra = "Z";

$letra1 = chr(rand(ord($DesdeLetra), ord($HastaLetra)));
$letra2 = chr(rand(ord($DesdeLetra), ord($HastaLetra)));

$cdi="$letra1$letra2$fecha_actual_ymd$mesa";

/*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
$fechaActual = date("Y-m-d H:i:s");
$fechaRegistro = "2000-01-01 00:00:00";
$segundosFechaActual = strtotime($fechaActual);
$segundosFechaRegistro = strtotime($fechaRegistro);
$segundosTranscurridos = $segundosFechaActual - $segundosFechaRegistro;
//echo $fechaActual.'    '.$segundosTranscurridos;
$tipocliente_cod=$_SESSION['tipocliente_cod'];

//cuando en en la configuarion se escogio el modo un solo salonero
if($_SESSION['modo_saloneros']==0){
	$query3="UPDATE SAORDENES_TALIA SET NumeroD = '$nropedcli-$cal_contro_uni', Estado = 'ORD_OPENABLE', 
	Master = $mesa, Total = ".round($res_monto_total, 2).", NumItems = $max, CSeats = '$cseats', 
	CodeCliente = '$tipocliente_cod', CID = '$cdi', CodeMesero = '".$_SESSION['CodVend']."', NameMesero = '".$_SESSION['NomVend']."', CreationTime=$segundosTranscurridos, 
	LastComandaTime=$segundosTranscurridos WHERE Pedido = $mesa";
}else{
//cuando en en la configuarion se escogio el modo multiple salonero
	$sql2="SELECT * FROM SAORDENES_TALIA WHERE Pedido = $mesa";
	$sentencia3 = $base_de_datos->prepare($sql2, [
      PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

	$sentencia3->execute();
	$result = $sentencia3->fetch(PDO::FETCH_ASSOC);

	if ($result){
		if(($result['NameMesero']!='') AND ($result['CodeMesero']!='')){
			$query3="UPDATE SAORDENES_TALIA SET NumeroD = '$nropedcli-$cal_contro_uni', Estado = 'ORD_OPENABLE', 
			Master = $mesa, Total = ".round($res_monto_total, 2).", NumItems = $max, CSeats = '$cseats', 
			CodeCliente = '$tipocliente_cod', CID = '$cdi', CreationTime=$segundosTranscurridos, 
			LastComandaTime=$segundosTranscurridos WHERE Pedido = $mesa";
		}else{
			$query3="UPDATE SAORDENES_TALIA SET NumeroD = '$nropedcli-$cal_contro_uni', Estado = 'ORD_OPENABLE', 
			Master = $mesa, Total = ".round($res_monto_total, 2).", NumItems = $max, CSeats = '$cseats', 
			CodeCliente = '$tipocliente_cod', CID = '$cdi', CodeMesero = '".$_SESSION['CodVend']."', NameMesero = '".$_SESSION['NomVend']."', CreationTime=$segundosTranscurridos, 
			LastComandaTime=$segundosTranscurridos WHERE Pedido = $mesa";
		}
	}
}

$sentencia = $base_de_datos->prepare("$query3");
$resultado = $sentencia->execute();

$query3="DELETE FROM TRAZA_USER_VENDEDORES WHERE Pedido = $mesa";
$sentencia = $base_de_datos->prepare("$query3");
$resultado = $sentencia->execute();

$logFile = fopen("C:/INNOVARP/SalidaWeb/Comanda/$mesa", 'a') or die("Error creando archivo");
//fwrite($logFile, " ") or die("Error escribiendo en el archivo");fclose($logFile);

//include("log.php");
echo "<script type='text/javascript'>
        window.location='index.php';
        </script>";

function cadena_control(){
    /*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
    usleep(100000);
    $fecha1= new DateTime("1800-12-28 00:00:00");
    $fecha2= new DateTime(date("Y-m-d H:i:s"));

    $diff = $fecha1->diff($fecha2);
    $dias=$diff->days;
    $fecha_actual_clarion=$diff->days;
    $fecha_actual_ymd=date("Ymd");

    /*generando hora actual en codigo clarin*/
    /*$aux =  microtime(true);
    $now = DateTime::createFromFormat('U.u', $aux);        
    if (is_bool($now)){
        $now = DateTime::createFromFormat('U.u', $aux += 0.001);
    }*/
    //$now = DateTime::createFromFormat("U.u", microtime(true));
	$hora_actual = (date('H')*360000)+(date("i")*6000)+(date("s")*100)+(date("v")/10)+1;
    //$hora_actual = ($now->format("H")*360000)+($now->format("i")*6000)+($now->format("s")*100)+($now->format("u")*10)+1;
    if(strlen($hora_actual)==7){
                
    }else if(strlen($hora_actual)>7){
        $hora_actual =substr($hora_actual, 0,7);
    }else if(strlen($hora_actual)<7){
        $res=7-strlen($hora_actual);
        for($t=1; $t<=$res; $t++) {
            $hora_actual="0".$hora_actual;
        }
    }

    /*generando numero aleatorio entre 10000 y 99999*/
    $aleatorio=mt_rand(10000,99999);

    return "$dias|$hora_actual|$aleatorio|$fecha_actual_clarion|$fecha_actual_ymd";
}
?>