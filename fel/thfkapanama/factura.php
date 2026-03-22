<?php
session_start();
//include_once "../../permiso.php";
include_once "../../config/db.php";
ob_clean();
$clases = ["Cliente","DatosTransaccion", "DocumentoElectronico", "FormaPago", "Item","Totales", "descargaPdf", "Helper", "Facturacion", "Descuentos", "Retenciones"];
foreach ($clases as $clase) {
  $archivo = $clase.".php";
  require_once($archivo);
}
/** Se preparan los datos de facturación de la empresa */
$parcontrol  = isset($_SESSION['id_control']) ? trim($_SESSION['id_control']) : $_REQUEST['parcontrol'];
$reenvio  = !isset($_REQUEST["parcontrol"]) ? true : false;
$facturacion = new Facturacion($base_de_datos);

$CONFIG = $facturacion->getConfig( $parcontrol );
if($CONFIG["FACELECT"] == "0"){
  echo json_encode(array("estado"=> 0, "mensaje" => array("mensaje" =>  "Facturación Electrónica no está habilitada") ));exit;
}
if($CONFIG["DIRECCIONENVIO"] == "false"){
  
  echo json_encode(array("estado"=> 0, "mensaje" => array("mensaje" =>  "La Dirección de Envío no es correcta") ));exit;
}
$control_encode = $_POST["control"];
$CONTROL = base64_decode($control_encode);
/* Se preparan los datos de la factura maestro */
$maestro = $facturacion->getFacturaMaestro($CONTROL);

$maestro["CODIGO"] = str_replace("'", "''", $maestro["CODIGO"]);
$cliente = $facturacion->getCliente($maestro["CODIGO"]);

$tipoClienteFE = "";
switch ($cliente["TIPOCLI"]) {
  case "Consumidor Final exento":
    $tipoClienteFE = "02";
    break;
  case "Gobierno":
    $tipoClienteFE = "03";
    break;
  case "Gubernamental":
    $tipoClienteFE = "03";
    break;
  case "Consumidor Final":
    $tipoClienteFE = "02";
    break;
  case "Exento":
    $tipoClienteFE = "01";
    break;
  case "Contribuyente":
    $tipoClienteFE = "01";
    break;
  case "Otros exentos":
    $tipoClienteFE = "01";
    break;
  default:
    $tipoClienteFE = "02";
    break;
}
switch ((int)$cliente["TIPOCOMERCIO"]) {
  case 0:
    $tipoContribuyente = "1";
    break;
  case 1:
    $tipoContribuyente = "2";
    break;
  case 2:
    $tipoContribuyente = "";
    $tipoClienteFE = "04";
    break;
  default:
    $tipoContribuyente = "2";
    break;
}
if($cliente["TIPOCLI"] == "Consumidor Final" AND (int)$cliente["TIPOCOMERCIO"] == "1"){
  $tipoContribuyente = "1";
  $tipoClienteFE = "02";

}

$codigoUbicacion = ""; // Valor por defecto
$nombreGeo1 = isset($cliente["NOMBREEGEO1"]) ? (is_numeric($cliente["NOMBREEGEO1"]) ? $cliente["NOMBREEGEO1"] : 8) : 8;

// Validación de $cliente["NOMBREEGEO2"]
$nombreGeo2 = isset($cliente["NOMBREEGEO2"]) ? (is_numeric($cliente["NOMBREEGEO2"]) ? $cliente["NOMBREEGEO2"] : 8) : 8;

// Validación de $cliente["NOMBREEGEO3"]
$nombreGeo3 = isset($cliente["NOMBREEGEO3"]) ? (is_numeric($cliente["NOMBREEGEO3"]) ? $cliente["NOMBREEGEO3"] : 8) : 8;

// Verificar si los valores son números
if (is_numeric($nombreGeo1) && is_numeric($nombreGeo2) && is_numeric($nombreGeo3)) {
    $codigoUbicacion = $nombreGeo1 . "-" . $nombreGeo2 . "-" . $nombreGeo3;
}
$cliente["RIF"] = str_replace("'", "''", $cliente["RIF"]);

if($tipoClienteFE == "02" AND $tipoContribuyente == "1") 
{
  $numeroRUC = str_pad( $cliente["RIF"],5,"0",STR_PAD_RIGHT);
  $dv = "";
}
else
{
    $numeroRUC = $cliente["RIF"];
    $dv = $cliente["NIT"];  
}

$numtel = Helper::validarTelefono( $cliente["NUMTEL"] ) ? $cliente["NUMTEL"] : "9999-9999"; // Formato válido
$correo = Helper::validarCorreo( $cliente["DIRCORREO"] ) ? $cliente["DIRCORREO"] : ""; // Formato válido

$arrayClientes = array
(
  "tipoClienteFE"        => $tipoClienteFE,
  "tipoContribuyente"    => $tipoContribuyente,
  "numeroRUC"            => $numeroRUC,
  "digitoVerificadorRUC" => $dv,
  "razonSocial"          => $cliente["NOMBRE"],
  "direccion"            => $cliente["DIRECC1"],
  "codigoUbicacion"      => $codigoUbicacion,
  "provincia"            => $nombreGeo1,
  "distrito"             => $nombreGeo2,
  "corregimiento"        => $nombreGeo3,
  "telefono1"            => $numtel,
  "correoElectronico1"   => $correo,
  "pais"                 => "PA",
  "paisOtro"             => null
);
$CONESPECIAL = (int)$cliente["CONESPECIAL"] ;
$PORRETIMP   = (int)$cliente["PORRETIMP"] ;
$ValRetenc = 0;

if($CONESPECIAL == 1){

  switch ($PORRETIMP) {
    case 100:
      $codRetencion = 1;
      break;
    case 50:
      $codRetencion = 4;
      break;
    
    default:
      $codRetencion = 8;
      break;
  }
  $retencion = $facturacion->getRetencionMaestro($maestro["NUMREF"]);
  $ValRetenc = (float)$retencion["MONTOTOT"];
}

/* Se preparan los datos de la factura detalle */

$detalles = $facturacion->getDetalleFactura($CONTROL);

$pagos = $facturacion->getPagos($CONTROL);

$adendas = $facturacion->getAdenda($CONTROL);

$numeroDocumentoFiscal = str_pad( $maestro["NUMREF"], 10, "0", STR_PAD_LEFT) ;

if($adendas){
  $adenda = $adendas["OBS1"];
}
else{
  $adenda = "";
}

$data = [
  'tipoEmision'            => $CONFIG["TIPOEMISION"],
  'tipoDocumento'          => '01',
  'tipoSucursal'           => $CONFIG["TIPOSUCURSAL"],
  'numeroDocumentoFiscal'  => $numeroDocumentoFiscal,
  'codigoSucursalEmisor'   => $CONFIG["CODSUC"],
  'puntoFacturacionFiscal' => $CONFIG["CODPFACT"],
  'fechaEmision'           => '2023-06-17',
  'naturalezaOperacion'    => $CONFIG["NATURALEZAOPERACION"],
  'tipoOperacion'          => $CONFIG["TIPOOPERACION"],
  'destinoOperacion'       => $CONFIG["CODPFACT"],
  'formatoCAFE'            => $CONFIG["FORMATOCAFE"],
  'entregaCAFE'            => $CONFIG["ENTREGACAFE"],
  'envioContenedor'        => 1,
  'procesoGeneracion'      => 1,
  'informacionInteres'     => $adenda,
  'cliente'                => null
];


$cliente = new Cliente( $arrayClientes );

$datosTransaccion = new DatosTransaccion();

$datosTransaccion->setCliente($cliente);


//- Debe establecerse el formato solicitado para la fecha de emisión “yyyy-MM-ddTHH:mm:ss-05:00”

$tipoSucursal = "1";
$codigoSucursalEmisor = "0000";

$fechaEmision = new \DateTime('America/Panama');
$datosTransaccion->setFechaEmision( $fechaEmision ->format('Y-m-d\TH:i:s-05:00'));
$datosTransaccion->setNumeroDocumentoFiscal($numeroDocumentoFiscal);
$datosTransaccion->setPuntoFacturacionFiscal($CONFIG["CODPFACT"]);
if($adendas){
  $datosTransaccion->setInformacionInteres($adenda);

}
if($maestro["MONTODES"]>0){
  $arrayDescuento = array("descDescuento"=> "DESC. GLOBAL",
  "montoDescuento" =>number_format($maestro['MONTODES'],2,".","") );
  $descuento = new Descuentos($arrayDescuento);
  $descuento = array("descuentoBonificacion" => $descuento);

}
else{
  $descuento = null;
}
$factura = new DocumentoElectronico();
$factura->setCodigoSucursalEmisor( $codigoSucursalEmisor );
$factura->setTipoSucursal( $tipoSucursal );
$factura->setDatosTransaccion($datosTransaccion);

$arrayListaItems = [];
$sum_precio_item = 0;
$sum_itbms = 0;
//print_r($detalles);exit;
foreach ($detalles as $detalle) {
  $referencia = trim($detalle["CODPRO"]);

  $tasaITBMS = '';

  switch((int)$detalle['IMPPOR']){
    case 7:
        $tasaITBMS = '01';
        break;
    case 10:
        $tasaITBMS = '02';
        break;
    case 15:
        $tasaITBMS = '03';
        break;
    default:
        $tasaITBMS = '00'; // monto exento
  }
  $producto = $facturacion->getProdDetalle($referencia);
  $itbms = (( $detalle['CANTIDAD']*$detalle['PRECOSUNI']) * (int)$detalle['IMPPOR']) / 100;
  $empaque = $facturacion->getProdCompuesto(  $detalle["DESCRIP1"] );

  if($empaque){
    $costoempaque = $producto["COSTOPRO"] * $empaque["CANTIDAD_EMP"];
    $precio = $detalle["PRECOSUNI"]*$empaque["CANTIDAD_EMP"];

    $precioItem     = number_format($precio-$detalle["MONTOIMP"],2,".","");
    $precioUnitario = number_format($detalle['TOTAL'],2,".","");


  }else{
    $precioItem     = number_format($detalle['CANTIDAD']* ($detalle['PRECOSUNI']-$detalle["MONTODESCUENTO"]),2,".","");
    $precioUnitario = number_format($detalle['PRECOSUNI'],2,".","");

  }
  $precioUnitarioDescuento = number_format($detalle['MONTODESCUENTO'],2,".","");
  $valorTotal     = number_format($detalle['COSTOADU1'],2,".","");
  $valorTBMS      = number_format($detalle['MONTOIMP'],2,".","");
  $sum_itbms      += number_format($itbms,2,".",""); 
  $sum_precio_item += $detalle['COSTOADU1'];


  $item = new Item();
  $item->setDescripcion( $detalle["DESCRIP1"] );
  $item->setCodigo( $detalle["CODPRO"] );
  $item->setUnidadMedida( 'und' );
  $item->setCantidad( $detalle["CANTIDAD"] );

  $item->setPrecioUnitario( $precioUnitario );
  $item->setPrecioUnitarioDescuento( $precioUnitarioDescuento );
  $item->setPrecioItem( $precioItem );
  $item->setValorTotal( $valorTotal );
  $item->setTasaITBMS( $tasaITBMS);
  $item->setValorITBMS( $valorTBMS );
  if($maestro["TIPOCLI"]=="Gobierno" OR $maestro["TIPOCLI"]=="Gubernamental") {
    $item->setCodigoCPBSAbrev( $producto["CODCATH"] );
    $item->setCodigoCPBS( $producto["CODCATD"] );
    $item->setUnidadMedidaCPBS( strtolower( $producto["UNIDAD"] ) );

  }else{
    $item->unsetCodigoCPBS();
    $item->unsetCodigoCPBSAbrev();
    $item->unsetUnidadMedidaCPBS();

  }
  $item->unsetFechaFabricacion();
  $item->unsetPrecioAcarreo();
  $item->unsetPrecioSeguro();
  $item->unsetCodigoGTIN();
  $item->unsetCantGTINCom();
  $item->unsetCodigoGTINInv();
  $item->unsetCantGTINComInv();
  $item->unsetTasaISC();
  $item->unsetValorISC();
  $arrayListaItems[] = $item;
}
$sum_precio_item  = number_format($sum_precio_item,2,".","");


$item = new Item();

$factura->setListaItems ($arrayListaItems);
$arrayFormaPago = [];
if($pagos)
{
  $tot_pagos = 0;
  foreach ($pagos as $key => $pago)
  {
    $descFormPago = ''; 
    switch ($pago["FUNCION"]) {
      case '0':
        $formaPagoFact = '03';
        break;

      case '1':
        $formaPagoFact = '01';
        break;

      case '2':
        $formaPagoFact = '08';
        break;

      case '3':
        $formaPagoFact = '04';
        break;

      case '6':
        //Efectivo
        $formaPagoFact = '02';
        break;

      default:
        $formaPagoFact = '99';
        $descFormPago = $pago["DESCRIP_PAGO"];
      break;
    }
    $tot_pagos += transformarADecimales($pago["MONTOPAG"]);
    $arrayFormaPago[$key] = array("formaPagoFact" => $formaPagoFact,
    "valorCuotaPagada" =>  transformarADecimales($pago["MONTOPAG"]),
    "descFormaPago" => $descFormPago);
  }
  if((float)$maestro["MONTODES"] > 0){
    end($pagos);
    $ultimoKey = key($pagos);
    $diferencia = $tot_pagos - ($maestro["MONTOBRU"] + $sum_itbms - $maestro["MONTODES"]);
    $arrayFormaPago[$ultimoKey]["valorCuotaPagada"] = transformarADecimales($arrayFormaPago[$ultimoKey]["valorCuotaPagada"] - $diferencia);
  }

}
else
{
  if($maestro["MONTOSAL"]>0){
    $arrayFormaPago[] = array("formaPagoFact" => "01",
    "valorCuotaPagada" => transformarADecimales($maestro["MONTOSAL"]+$ValRetenc));

  }else{
    $arrayFormaPago[] = array("formaPagoFact" => "01",
    "valorCuotaPagada" => transformarADecimales($maestro["MONTOTOT"]));

  }

}
if($maestro["MONTODES"] <= 0){
  $sum_itbms = $maestro["MONTOIMP"];
}
//$formaPago = new FormaPago( $arrayFormaPago);
$array_totales = array(
  "totalPrecioNeto"       => $maestro["MONTOBRU"],
  //"totalITBMS"            => $maestro["MONTOIMP"],
  "totalITBMS"            => $sum_itbms,
  "totalISC"              => 0,
  "totalMontoGravado"     => $sum_itbms,
  "totalDescuento"        => $maestro["MONTODES"],
  "totalAcarreoCobrado"   => 0,
  "valorSeguroCobrado"    => 0,
  "totalFactura"          => $maestro["MONTOBRU"] + $sum_itbms - $maestro["MONTODES"],
  "totalValorRecibido"    => ($maestro["TIPOFACTURA"] == "CREDITO") ? ($maestro["MONTOSAL"]+$ValRetenc) :  $maestro["MONTOBRU"] + $sum_itbms - $maestro["MONTODES"] + $maestro["CAMBIO"] ,
  "vuelto"                => $maestro["CAMBIO"],
  "tiempoPago"            => $maestro["DIASVEN"] <= 1 ? 1 : 2 ,
  "nroItems"              => count($detalles),
  "totalTodosItems"       => $sum_precio_item,
  "listaFormaPago"        => 0,
  "listaPagoPlazo"        => 0,
  "listaDescBonificacion" => $descuento
);
// Función para transformar los valores a decimales con dos puntos de precisión
function transformarADecimales($valor) {
  return number_format($valor, 2, ".", "");
}

// Transformar los valores del array a decimales con dos puntos de precisión
foreach ($array_totales as $clave => $valor) {
  if ($clave !== "tiempoPago" && $clave !== "nroItems"  && $clave !== "listaDescBonificacion") {
      $array_totales[$clave] = transformarADecimales($valor);
  }
}

$totales = new Totales($array_totales);
$totales->setListaFormaPago( ($arrayFormaPago));
if($maestro["DIASVEN"] > 1){
  $fechaVencs = new \DateTime($maestro["FECVENCS"]);
  
  $arrayPagoPlazo = array(
    "fechaVenceCuota" => $fechaVencs ->format('Y-m-d\TH:i:s-05:00'),
    "valorCuota"      =>  transformarADecimales($maestro["MONTOSAL"]+$ValRetenc)  ,
    "infoPagoCuota"   => "PAGO POR CUOTA ".  transformarADecimales($maestro["MONTOSAL"]+$ValRetenc)
  );
  $arrayListaPagoPlazo = array( "pagoPlazo" => $arrayPagoPlazo);
  $totales->setListaPagoPlazo( $arrayListaPagoPlazo );

}else{
  $totales->unsetListaPagoPlazo();

}
if($CONESPECIAL == "1" AND (float)$ValRetenc > 0){
  $array_retencion = array(
    "codigoRetencion" => $codRetencion,    
    "montoRetencion" => transformarADecimales( $ValRetenc )
  );
  

  $retencion = new Retencion($array_retencion);
  
  $totales->setRetencion( $retencion );

}else{
  
  $totales->unsetRetencion( );
}
$factura->setTotalesSubTotales( $totales );

if($maestro["CAMBIO"] == 0){
  $totales->unsetVuelto();
}
$totales->unsetTotalISC();
if($maestro["MONTODES"]< 0){
  $totales->unsetDescuento();

}else{
  $totales->setListaDescBonificacion( ($descuento));

}
$direccion_envio = $CONFIG["DIRECCIONENVIO"];
/*if (!filter_var($direccion_envio, FILTER_VALIDATE_URL)) {
  echo json_encode(array("estado"=> 0, "mensaje" => "Verifique la dirección de envío"));exit;
}*/
$tokenEmpresa    = $CONFIG["USUARIO_RUC"];
$tokenPassword   = $CONFIG["CONTRASEÑA"];
function check_internet_connection($host, $port) {
  $connected = @fsockopen($host, $port);
  if ($connected){
      fclose($connected);
      return true;
  }
  return false;
}

// Verificar la conexión a Internet utilizando google.com en el puerto 80 (HTTP)
$host = 'www.google.com';
$port = 80;
  if (check_internet_connection($host, $port)) {
    $options = array('trace' => true,  'exceptions' => true);
    try{
      if (!extension_loaded('soap')) {  
        $datos["mensaje"] = "HABILITAR EXTENSIÓN SOAP";
        echo json_encode(array("estado"=> 0, "mensaje" => $datos));exit;
        return $respuesta;
      }
      $wsPa = new SoapClient($direccion_envio, $options);
    }
    catch(Excepcion $e){
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "ERROR AL ENVÍO DE LA FACTURA ELECTRÓNICA - EXT SOAP";
      return $respuesta;

    }
  } else {
      // No hay conexión a Internet
        $datos["mensaje"] = "ERROR AL ENVÍO DE LA FACTURA ELECTRÓNICA - NO HAY CONEXIÓN";
        echo json_encode(array("estado"=> 0, "mensaje" => $datos));exit;
  }
  $parametros = array(
    'tokenEmpresa'  => $tokenEmpresa,
    'tokenPassword' => $tokenPassword,
    'documento'     => $factura
  );

  $estado = 0;
  //- A continuación, enviamos el documento al método "Enviar" del Servicio Web de Integración de TFHKA
    $respWsPa = $wsPa->__soapCall('Enviar', array($parametros));

  $pathLog = "../request/".$numeroDocumentoFiscal;
  // Formatear el XML
  $dom = new DOMDocument();
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $dom->loadXML($wsPa->__getLastRequest());
  $formattedXml = $dom->saveXML();
  file_put_contents($pathLog, $formattedXml);
  

  // Preparar la consulta SQL
  $sql_documentos = "INSERT INTO Documentos (CODIGO, CONTROL, RESULTADO, MENSAJE, CUFE, QR, FECHARECEPCIONDGI, NROPROTOCOLOAYTORIZACION)
          VALUES (:codigo, :control, :resultado, :mensaje, :cufe, :qr, :fechaRecepcionDGI, :nroProtocoloAutorizacion)";
  $datos = array(
    'codigo' => $respWsPa->EnviarResult->codigo,
    'resultado' => $respWsPa->EnviarResult->resultado,
    'mensaje' => $respWsPa->EnviarResult->mensaje,
    'cufe' => $respWsPa->EnviarResult->cufe,
    'qr' => $respWsPa->EnviarResult->qr,
    'fechaRecepcionDGI' => $respWsPa->EnviarResult->fechaRecepcionDGI,
    'nroProtocoloAutorizacion' => $respWsPa->EnviarResult->nroProtocoloAutorizacion,
    'fechaLimite' => $respWsPa->EnviarResult->fechaLimite
  );
  if($respWsPa->EnviarResult->codigo == 200){
    
    $estado = 1;
    $sql = "MERGE
    INTO
      Documentos AS target
        USING (
      SELECT
        :codigo AS CODIGO,
        :control AS CONTROL,
        :resultado AS RESULTADO,
        :mensaje_1 AS MENSAJE,
        :cufe AS CUFE,
        :qr AS QR,
        :fechaRecepcionDGI AS FECHARECEPCIONDGI,
        :nroProtocoloAutorizacion AS NROPROTOCOLOAYTORIZACION,
        :fechaLimite AS FECHALIMITE) AS source
            ON
      (target.CONTROL = source.CONTROL)
      WHEN MATCHED THEN
          UPDATE
            SET
              target.CODIGO = source.CODIGO,
              target.RESULTADO = source.RESULTADO,
              target.MENSAJE = source.MENSAJE,
              target.CUFE = source.CUFE,
              target.QR = source.QR,
              target.FECHARECEPCIONDGI = source.FECHARECEPCIONDGI,
              target.NROPROTOCOLOAYTORIZACION = source.NROPROTOCOLOAYTORIZACION,
              target.FECHALIMITE = source.FECHALIMITE
      WHEN NOT MATCHED THEN
        INSERT
            (CODIGO,
            CONTROL,
            RESULTADO,
            MENSAJE,
            CUFE,
            QR,
            FECHARECEPCIONDGI,
            NROPROTOCOLOAYTORIZACION,
            FECHALIMITE)
          VALUES 
            (source.CODIGO,
            source.CONTROL,
            source.RESULTADO,
            source.MENSAJE,
            source.CUFE,
            source.QR,
            source.FECHARECEPCIONDGI,
            source.NROPROTOCOLOAYTORIZACION,
            source.FECHALIMITE);
    ";

    $stmt = $base_de_datos->prepare($sql);
    
    $fechaRecepcionDGI = !empty($datos['fechaRecepcionDGI']) ? date('Y-m-d', strtotime($datos['fechaRecepcionDGI'])) : null;
    $stmt->bindParam(':fechaRecepcionDGI', $fechaRecepcionDGI, PDO::PARAM_STR);
    
    $fechaLimite = !empty($datos['fechaLimite']) ? date('Y-m-d', strtotime($datos['fechaLimite'])) : null;
    $stmt->bindParam(':fechaLimite', $fechaLimite, PDO::PARAM_STR);
    $stmt->bindParam(':codigo', $datos['codigo'], PDO::PARAM_INT);
    $stmt->bindParam(':control', $CONTROL, PDO::PARAM_STR);
    $stmt->bindParam(':resultado', $datos['resultado'], PDO::PARAM_STR);
    $stmt->bindParam(':mensaje_1', $datos['mensaje'], PDO::PARAM_STR);
    $stmt->bindParam(':cufe', $datos['cufe'], PDO::PARAM_STR);
    $stmt->bindParam(':qr', $datos['qr'], PDO::PARAM_STR);
    $stmt->bindParam(':nroProtocoloAutorizacion', $datos['nroProtocoloAutorizacion'], PDO::PARAM_STR);

    // Validar y formatear las fechas antes de la inserción
    $fechaRecepcionDGI = !empty($datos['fechaRecepcionDGI']) ? date('Y-m-d', strtotime($datos['fechaRecepcionDGI'])) : null;
    $stmt->bindParam(':fechaRecepcionDGI', $fechaRecepcionDGI, PDO::PARAM_STR);
    
    $fechaLimite = !empty($datos['fechaLimite']) ? date('Y-m-d', strtotime($datos['fechaLimite'])) : null;
    $stmt->bindParam(':fechaLimite', $fechaLimite, PDO::PARAM_STR);
    
    $stmt->execute();
    $descargaPDF = new descargaPDF();
    $respDescargaPDF = $descargaPDF->descargarPDF( $parametros, $direccion_envio, $base_de_datos );
    if($respDescargaPDF->DescargaPDFResult->codigo == "200")
    {
      $pdf_documento = (string)$respDescargaPDF->DescargaPDFResult->documento;
      $sql_update = "UPDATE Documentos SET PDF = :pdf, NUMDOCFISCAL = :nrodocfiscal WHERE CONTROL = :control;";
      $stmt = $base_de_datos->prepare($sql_update);
      $stmt->bindParam(':pdf', $pdf_documento, PDO::PARAM_STR);
      $stmt->bindParam(':nrodocfiscal', $numeroDocumentoFiscal, PDO::PARAM_STR);
      $stmt->bindParam(':control', $CONTROL, PDO::PARAM_STR);
      $stmt->execute();
      $sql_update = "UPDATE TRANSACCMAESTRO SET COM_FISCAL = :COM_FISCAL, URLCONSULTAFEL = :CUFE WHERE CONTROL = :control_;";
      $stmt = $base_de_datos->prepare($sql_update);
      $stmt->bindParam(':CUFE', $datos['qr'], PDO::PARAM_STR);
      $stmt->bindParam(':COM_FISCAL', $datos['cufe'], PDO::PARAM_STR);
      $stmt->bindParam(':control_', $CONTROL, PDO::PARAM_STR);
      $stmt->execute();
      $estado = 1;

    }
  }
  else{
    $estado = 0;
    $respDescargaPDF = "";
  }

  $stmt=null;
  $base_de_datos = null;
  echo json_encode(array("estado"=> $estado, "mensaje" => $datos, "respWS"=> $respWsPa, "PDF"=> $respDescargaPDF));