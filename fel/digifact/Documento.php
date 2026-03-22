<?php

Class Documento {
  public $base_de_datos;

  public function __construct(PDO $base_de_datos) {
    $this->base_de_datos = $base_de_datos;
  }
  public function transformarADecimales($valor) {
    return number_format($valor, 2, ".", "");
  }
  public function cadenaNullOrEmpty( $cadena ){
    return ($cadena == null OR $cadena == "") ? true : false;
  }

  public function enviarDocDigifact($control, $parcontrol){
    $clases = ["Cliente","DatosTransaccion", "DocumentoElectronico", "FormaPago", "Item","Totales", "descargaPdf", "Helper", "Facturacion", "Descuentos"];
    foreach ($clases as $clase) {
      $archivo = "../thfkapanama/".$clase.".php";
      require_once($archivo);
    }
    require_once("digifact.class.php");

    $randomNumbers = [];
    for ($i = 0; $i < 9; $i++) {
        $randomNumbers[] = mt_rand(0, 9);
    }
    $securityCode = implode("", $randomNumbers);
    
    /** Se preparan los datos de facturación  de la empresa */
    
    $facturacion = new Facturacion($this->base_de_datos);
    $CONFIG      = $facturacion->getConfig( $parcontrol );
    $EMPRESA     = $facturacion->getEmpresaCod( $parcontrol );
    $digifact    = new Digifact( $this->base_de_datos );
    
    if((int)$CONFIG["FACELECT"] == 0){
      
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "Facturación Electrónica no está habilitada";
      return $respuesta;
    }
    if($digifact->validarToken( $CONFIG["FEXPIRA_DIGI"] ) OR $digifact->validarAuthToken( $CONFIG )){
      $dataDocumento = array("url" => $CONFIG["DIRECCIONENVIO"]."/login/get_token", "user" => "PA.".$CONFIG["RUC_DIGI"].".".$CONFIG["USUARIO_DIGI"], "psw" => $CONFIG["PASSWORD_DIGI"], "parcontrol" => $parcontrol );
      $token = $digifact->obtenerToken( $dataDocumento );
      if($token == "Credenciales incorrectas"){
        
          $respuesta["codigo"]  = 0;
          $respuesta["mensaje"] = $token;
          return $respuesta;
      }
    }else{
      $token = $CONFIG["TOKEN_DIGI"];
    }
    // Variables con los valores
    $Version      = '1.00';
    $CountryCode  = 'PA';
    $DocType      = 'tipoDocumento'; // Reemplaza con el valor deseado
    $fechaEmision = '2023-07-31';    // Reemplaza con el valor deseado
    $DocType      = "01";

    $maestro           = $facturacion->getFacturaMaestro($control);
    $maestro["CODIGO"] = str_replace("'", "''", $maestro["CODIGO"]);

    $cliente        = $facturacion->getCliente($maestro["CODIGO"]);
    $cliente["RIF"] = str_replace("'", "''", $cliente["RIF"]);

    $pagos   = $facturacion->getPagos($maestro["CODIGO"]);
    $adendas = $facturacion->getAdenda($control);
    
    switch ( $cliente["TIPOCLI"] ) {
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
    
    if( $this->cadenaNullOrEmpty((string)$cliente["NIT"]) AND (string)$tipoClienteFE == "01" AND (string)$tipoContribuyente == "1" )
    {
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "CLIENTE JURÍDICO SIN DIGITO VERIFICADOR";
      return $respuesta;

    }
    
    if( $this->cadenaNullOrEmpty((string)$CONFIG["CODIGOSUCURSALEMISOR"]) )
    {
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "VERIFIQUE EL CÓDIGO DE SUCURSAL";
      return $respuesta;

    }
    
    if( $this->cadenaNullOrEmpty((string)$CONFIG["PUNTOFACTURACIONFISCAL"]))
    {
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "VERIFIQUE EL PUNTO DE FACTURACIÓN FISCAL";
      return $respuesta;

    }
    
    if( $this->cadenaNullOrEmpty((string)$CONFIG["COORDENADAS_DIGI"]))
    {
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "VERIFIQUE LAS COORDENADAS";
      return $respuesta;

    }
    
    if( $this->cadenaNullOrEmpty((string)$CONFIG["RUC_DIGI"]))
    {
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "VERIFIQUE EL RUC";
      return $respuesta;

    }
    
    if( $this->cadenaNullOrEmpty((string)$CONFIG["UBICACION_DIGI"]))
    {
      $respuesta["codigo"]  = 0;
      $respuesta["mensaje"] = "VERIFIQUE EL CODIGO DE UBICACIÓN";
      return $respuesta;

    }
    $CONESPECIAL = (int)$cliente["CONESPECIAL"] ;
    $PORRETIMP   = (int)$cliente["PORRETIMP"] ;
    $AMBIENTE   = ($CONFIG["AMBIENTE"] == 2) ? 1 : 2;

    //$date = new \DateTime($factura['fecha']);
    $fechaEmision   = new \DateTime('America/Panama');
    $IssuedDateTime = $fechaEmision ->format('Y-m-d\TH:i:s-05:00');
    
    // Crear un objeto SimpleXMLElement
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Root></Root>');
    
    // Añadir elementos al XML
    $xml->addChild('Version', $Version);
    $xml->addChild('CountryCode', $CountryCode);    
    
    $header = $xml->addChild('Header');
    $header->addChild('DocType', $DocType);
    $header->addChild('IssuedDateTime', (string)$IssuedDateTime);
    $header->addChild('AdditionalIssueType', (string)$AMBIENTE);
    
    $additionalIssueDocInfo = $header->addChild('AdditionalIssueDocInfo');
    
    // Info TipoEmision
    $infoTipoEmision = $additionalIssueDocInfo->addChild('Info', null);
    $infoTipoEmision->addAttribute('Name', 'TipoEmision');
    $infoTipoEmision->addAttribute('Value', $CONFIG["TIPOEMISION"]);
    
    // Info NumeroDF
    $infoNumeroDF = $additionalIssueDocInfo->addChild('Info', null);
    $infoNumeroDF->addAttribute('Name', 'NumeroDF');
    $infoNumeroDF->addAttribute('Value', $maestro["NUMREF"]);
    
    // Info PtoFactDF
    $infoPtoFactDF = $additionalIssueDocInfo->addChild('Info', null);
    $infoPtoFactDF->addAttribute('Name', 'PtoFactDF');
    $infoPtoFactDF->addAttribute('Value', $CONFIG["PUNTOFACTURACIONFISCAL"]);
    $numeroDocumentoFiscal = $maestro["NUMREF"];
    // Info CodigoSeguridad
    $infoCodigoSeguridad = $additionalIssueDocInfo->addChild('Info', null);
    $infoCodigoSeguridad->addAttribute('Name', 'CodigoSeguridad');
    $infoCodigoSeguridad->addAttribute('Value', $securityCode );
    // Info NaturalezaOperacion
    $infoNaturalezaOperacion = $additionalIssueDocInfo->addChild('Info', null);
    $infoNaturalezaOperacion->addAttribute('Name', 'NaturalezaOperacion');
    $infoNaturalezaOperacion->addAttribute('Value', '01');
    
    // Info TipoOperacion
    $infoTipoOperacion = $additionalIssueDocInfo->addChild('Info', null);
    $infoTipoOperacion->addAttribute('Name', 'TipoOperacion');
    $infoTipoOperacion->addAttribute('Value', '1');
    
    // Info DestinoOperacion
    $infoDestinoOperacion = $additionalIssueDocInfo->addChild('Info', null);
    $infoDestinoOperacion->addAttribute('Name', 'DestinoOperacion');
    $infoDestinoOperacion->addAttribute('Value', '1');
    
    // Info FormatoGeneracion
    $infoFormatoGeneracion = $additionalIssueDocInfo->addChild('Info', null);
    $infoFormatoGeneracion->addAttribute('Name', 'FormatoGeneracion');
    $infoFormatoGeneracion->addAttribute('Value', '1');
    
    // Info ManeraEntrega
    $infoManeraEntrega = $additionalIssueDocInfo->addChild('Info', null);
    $infoManeraEntrega->addAttribute('Name', 'ManeraEntrega');
    $infoManeraEntrega->addAttribute('Value', '1');
    
    // Info EnvioContenedor
    $infoEnvioContenedor = $additionalIssueDocInfo->addChild('Info', null);
    $infoEnvioContenedor->addAttribute('Name', 'EnvioContenedor');
    $infoEnvioContenedor->addAttribute('Value', '1');
    
    // Info ProcesoGeneracion
    $infoProcesoGeneracion = $additionalIssueDocInfo->addChild('Info', null);
    $infoProcesoGeneracion->addAttribute('Name', 'ProcesoGeneracion');
    $infoProcesoGeneracion->addAttribute('Value', '1');
    
    // Info TipoTransaccion
    $infoTipoTransaccion = $additionalIssueDocInfo->addChild('Info', null);
    $infoTipoTransaccion->addAttribute('Name', 'TipoTransaccion');
    $infoTipoTransaccion->addAttribute('Value', '1');
    
    // Info TipoSucursal
    $infoTipoSucursal = $additionalIssueDocInfo->addChild('Info', null);
    $infoTipoSucursal->addAttribute('Name', 'TipoSucursal');
    $infoTipoSucursal->addAttribute('Value', '2');
    
    // Añadir el resto de elementos Info con sus atributos Name y Value correspondientes.
    
    $seller = $xml->addChild('Seller');
    $seller->addChild('TaxID', $CONFIG["RUC_DIGI"]);
    $esjuridico = $CONFIG["JURIDICO_DIGI"] == '1' ? '2' : '1';
    $seller->addChild('TaxIDType', $esjuridico);
    
    $sellerTaxIDAdditionalInfo = $seller->addChild('TaxIDAdditionalInfo');
    
    // Info DigitoVerificador
    $infoDigitoVerificador = $sellerTaxIDAdditionalInfo->addChild('Info', null);
    $infoDigitoVerificador->addAttribute('Name', 'DigitoVerificador');
    $infoDigitoVerificador->addAttribute('Value', $CONFIG["DV_DIGI"]);
    $NEMPRESA = $CONFIG["AMBIENTE"] == 2 ? $CONFIG["NEMPRESA_DIGI"] : 'FE generada en ambiente de pruebas - sin valor comercial ni fiscal';
    $seller->addChild('Name', $NEMPRESA );
    
    $sellerContact = $seller->addChild('Contact');
    $phoneList = $sellerContact->addChild('PhoneList');
    $numTel = ($CONFIG["TEL_DIGI"] != "" OR CONFIG != NULL) ? $CONFIG["TEL_DIGI"] : '9999-9999';
    $phoneList->addChild('Phone', $numTel);
    
    $branchInfo = $seller->addChild('BranchInfo');
    $branchInfo->addChild('Code', $CONFIG["CODIGOSUCURSALEMISOR"]);
    $COORD = $CONFIG["UBICACION_DIGI"] ?$CONFIG["UBICACION_DIGI"] : 'Panama-Panama-Panama';
    $explo = explode("-",$CONFIG["UBICACION_DIGI"]);

    $addressInfo = $branchInfo->addChild('AddressInfo');
    
    $geo1 = isset($cliente["geo1"]) ? (is_numeric($cliente["geo1"]) ? $cliente["geo1"] : "PANAMA") : "PANAMA";
    $geo2 = isset($cliente["geo2"]) ? (is_numeric($cliente["geo2"]) ? $cliente["geo2"] : "PANAMA") : "PANAMA";
    $geo3 = isset($cliente["geo3"]) ? (is_numeric($cliente["geo3"]) ? $cliente["geo3"] : "PANAMA") : "PANAMA";

    $direccion = $cliente["DIRECC1"] != "" ?  $cliente["DIRECC1"] : "----------";
    $addressInfo->addChild('Address',  $CONFIG["DIRECCION_DIGI"]);
    $addressInfo->addChild('City', $geo1 );
    $addressInfo->addChild('District', $geo2 );
    $addressInfo->addChild('State', $geo3 );
    $addressInfo->addChild('Country', 'PA');
    
    $additionalBranchInfo = $branchInfo->addChild('AdditionalBranchInfo');
    $infoCoordEm = $additionalBranchInfo->addChild('Info', null);
    $infoCoordEm->addAttribute('Name', 'CoordEm');
    $infoCoordEm->addAttribute('Value', $CONFIG["COORDENADAS_DIGI"]);
    
    $geoloc1 = "8-8-8";

    $infoCodUbi = $additionalBranchInfo->addChild('Info', null);
    $infoCodUbi->addAttribute('Name', 'CodUbi');
    $infoCodUbi->addAttribute('Value', $geoloc1);
    
    $buyer = $xml->addChild('Buyer');
    if($tipoClienteFE == "01" OR $tipoClienteFE == "03"){
      $buyer->addChild('TaxID', $cliente["RIF"]);
      $buyer->addChild('TaxIDType', $tipoContribuyente);
    }
    else
    {
      if($tipoClienteFE == "02" AND $tipoContribuyente ==2 ){
        $buyer->addChild('TaxID', "CF");
      }
      elseif($tipoClienteFE == "04" AND $tipoContribuyente =="" ){
        $buyer->addChild('TaxID', "EXTRANJERO");
      }else{
        $buyer->addChild('TaxID', "CF");
        $buyer->addChild('TaxIDType', 1);
      }
    }
    
    $buyerTaxIDAdditionalInfo = $buyer->addChild('TaxIDAdditionalInfo');
    
    $infoTipoReceptor = $buyerTaxIDAdditionalInfo->addChild('Info', null);
    $infoTipoReceptor->addAttribute('Name', 'TipoReceptor');
    $infoTipoReceptor->addAttribute('Value', (string)$tipoClienteFE);
    if($tipoClienteFE == "01" OR $tipoClienteFE == "03")
    {
      $infoDigitoVerificador = $buyerTaxIDAdditionalInfo->addChild('Info', null);
      $infoDigitoVerificador->addAttribute('Name', 'DigitoVerificador');
      $infoDigitoVerificador->addAttribute('Value', $cliente["NIT"]);
    }
    elseif($tipoClienteFE == "04" AND $tipoContribuyente =="" ){
      $INFO =$buyerTaxIDAdditionalInfo->addChild('Info', null);
      $INFO->addAttribute('Name', 'NumPasaporte');
      $INFO->addAttribute('Value',  $cliente["RIF"]);
      $INFO =$buyerTaxIDAdditionalInfo->addChild('Info', null);
      $INFO->addAttribute('Name', 'PaisExt');
      $INFO->addAttribute('Value', "GT");
    }
    
    $codigoUbicacion = ""; 
    $nombreGeo1 = isset($cliente["NOMBREEGEO1"]) ? $cliente["NOMBREEGEO1"] : '8';
    $nombreGeo2 = isset($cliente["NOMBREEGEO2"]) ? $cliente["NOMBREEGEO2"] : '8';
    $nombreGeo3 = isset($cliente["NOMBREEGEO3"]) ? $cliente["NOMBREEGEO3"] : '8';
    if (is_numeric($cliente["NOMBREEGEO1"]) && is_numeric($cliente["NOMBREEGEO2"]) && is_numeric($cliente["NOMBREEGEO3"])) {
        $codigoUbicacion = $nombreGeo1 . "-" . $nombreGeo2 . "-" . $nombreGeo3;
    }else{
      $codigoUbicacion = "8-8-8";
    }
    $infoCodUbi = $buyerTaxIDAdditionalInfo->addChild('Info', null);
    $infoCodUbi->addAttribute('Name', 'CodUbi');
    $infoCodUbi->addAttribute('Value', $codigoUbicacion); 
    if($tipoClienteFE == "01" OR $tipoClienteFE == "03"  OR ($tipoClienteFE == "04" AND $tipoContribuyente =="") )
    {
      $buyer->addChild('Name', $cliente["NOMBRE"]);
    }

    $buyerAdditionalInfo = $buyer->addChild('AdditionlInfo');
    $buyerPais = $buyerAdditionalInfo->addChild('Info', null);
    $buyerPais->addAttribute('Name', 'PaisReceptorFE');
    $buyerPais->addAttribute('Value', 'PA');
    
    $addressInfoBuyer = $buyer->addChild('AddressInfo');
    $addressInfoBuyer->addChild('Address', $direccion );
    $addressInfoBuyer->addChild('City', $cliente["geo3"] );
    $addressInfoBuyer->addChild('District', $cliente["geo2"] );
    $addressInfoBuyer->addChild('State', $cliente["geo1"] );
    $addressInfoBuyer->addChild('Country', 'PA');

    $items = $xml->addChild('Items');
    $detalles = $facturacion->getDetalleFactura($control);

    $montoTotalItems_sin_impuesto = 0;
    $montoTotalItems = 0;
    $cantidadItems = 0;

    foreach($detalles as $detalle){
      $cantidadItems++;
      $item = $items->addChild('Item');      
      $codes = $item->addChild('Codes');
      
      // Primer Code
      $code1 = $codes->addChild('Code');
      $code1->addAttribute('Name', 'CodigoProd');
      $code1->addAttribute('Value', $detalle["CODIGO"]);
      $producto = $facturacion->getProdDetalle( $detalle["CODIGO"] );
      $CodCPBSabr = $producto["CODCATH"] ?? '13';
      $CodCPBScmp = $producto["CODCATD"] ?? '1310';
      $UnidadCPBS = $producto["UNIDAD"] ?? 'und';
      // Segundo Code
      $code2 = $codes->addChild('Code');
      $code2->addAttribute('Name', 'CodCPBSabr');
      $code2->addAttribute('Value', $CodCPBSabr);
      // Segundo Code
      $code2 = $codes->addChild('Code');
      $code2->addAttribute('Name', 'CodCPBScmp');
      $code2->addAttribute('Value', $CodCPBScmp);
      // Tercer Code
      $code2 = $codes->addChild('Code');
      $code2->addAttribute('Name', 'UnidadCPBS');
      $code2->addAttribute('Value',strtolower(  $UnidadCPBS) );    
      $empaque = $facturacion->getProdCompuesto(  $detalle["DESCRIP1"] );    
      if($empaque)
      {
        $costoempaque       = $producto["COSTOPRO"] * $empaque["CANTIDAD_EMP"];
        $precio             = $detalle["PRECOSUNI"]*$empaque["CANTIDAD_EMP"];
        $precioItem         = number_format($precio-$detalle["MONTOIMP"],2,".","");
        $total_sin_impuesto = number_format($detalle["CANTIDAD"] *$precioItem,6,".","");
        $precioUnitario     = number_format($detalle['TOTAL'],2,".","");
      }
      else
      {
        $precioItem         = number_format($detalle['CANTIDAD']* ($detalle['PRECOSUNI']-$detalle["MONTODESCUENTO"]),2,".","");
        $precioUnitario     = number_format($detalle['PRECOSUNI'],2,".","");
        $total_sin_impuesto = number_format($detalle["CANTIDAD"] *$detalle["PRECOSUNI"],6,".","");
      } 
      
      if($detalle["MONTODESCUENTO"]>0){
        
        $TotalWDiscount      = number_format($total_sin_impuesto  - $detalle["MONTODESCUENTO"],6,".","");
        $total_item_impuesto = number_format( (($TotalWDiscount) * $detalle['IMPPOR'])/100 , 6,".","");
        $descuento           = strpos($detalle["MONTODESCUENTO"], "%") ? ($detalle["PRECOSUNI"] * $detalle["PRECOSUNI"]) / 100 :  ($detalle["PRECOSUNI"] * $detalle["PRECOSUNI"]);
      }
      else
      {
        $total_item_impuesto = number_format( (($total_sin_impuesto) * $detalle['IMPPOR'])/100 , 6,".","");
      }
      $total_item = number_format($total_sin_impuesto + $total_item_impuesto, 6,".","");
      
      $descrip_item = $detalle["DESCRIP1"] ?? 'ITEM'; 
      $item->addChild('Description', $descrip_item);
      $item->addChild('Qty', $detalle["CANTIDAD"]);
      $item->addChild('UnitOfMeasure', 'm');
      $item->addChild('Price', $precioUnitario);

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
      
      if($detalle["MONTODESCUENTO"]>0){
        $TotalDiscounts = $item->addChild('Discounts');
        $Discount = $TotalDiscounts->addChild('Discount');
    
        $Discount->addChild('Description', 'DESCUENTO PARCIAL');
        $Discount->addChild('Amount', number_format($detalle["MONTODESCUENTO"], 6,".",""));
  
      }
      $taxes = $item->addChild('Taxes');
      $tax = $taxes->addChild('Tax');
      $tax->addChild('Code', $tasaITBMS);
      $tax->addChild('Description', 'ITBMS');
      $tax->addChild('Amount', $detalle["MONTOIMP"]);
      
      $montoTotalItems_sin_impuesto += $total_sin_impuesto;

      $totals = $item->addChild('Totals');
      if($detalle["MONTODESCUENTO"]>0){
        $totals->addChild('TotalBDiscount', number_format( $total_sin_impuesto , 6,".",""));
        $totals->addChild('TotalWDiscount', number_format( $total_sin_impuesto- $detalle["MONTODESCUENTO"] , 6,".",""));

      }
      $totals->addChild('TotalBTaxes', number_format( $total_sin_impuesto - $detalle["MONTODESCUENTO"], 6,".",""));
      $totals->addChild('TotalWTaxes', number_format( $total_item- $detalle["MONTODESCUENTO"] , 6,".","") );
      if($detalle["MONTODESCUENTO"]>0)
      {
        $totals->addChild('SpecificTotal', number_format( $total_item- $detalle["MONTODESCUENTO"], 6,".",""));
        $totals->addChild('TotalItem', number_format( $total_item- $detalle["MONTODESCUENTO"], 6,".",""));
        $montoTotalItems += $total_item- $detalle["MONTODESCUENTO"];
      }
      else
      {
        $totals->addChild('SpecificTotal', number_format( $total_item, 6,".",""));
        $totals->addChild('TotalItem', number_format( $total_item, 6,".",""));
        $montoTotalItems += $total_item;
      }
    }
    $totalsElement = $xml->addChild('Totals');
    $totalsElement->addChild('QtyItems', $cantidadItems);
    if($maestro["MONTODES"]>0){
      $TotalDiscounts = $totalsElement->addChild('TotalDiscounts');
      $Discount = $TotalDiscounts->addChild('Discount');
      $Discount->addChild('Description', 'DESCUENTO GLOBAL');
      $Discount->addChild('Amount', number_format( $maestro["MONTODES"], 6,".",""));
    }

    $grandTotal = $totalsElement->addChild('GrandTotal');
    $TotalBTaxes = $maestro["MONTOBRU"] ;
    $TotalWTaxes = $maestro["MONTOBRU"] + $maestro["MONTOIMP"]- $maestro["MONTODES"] ;
    $InvoiceTotal = $maestro["MONTOBRU"] + $maestro["MONTOIMP"] - $maestro["MONTODES"];
    $grandTotal->addChild('TotalBTaxes', number_format( $TotalBTaxes , 6,".",""));
    $grandTotal->addChild('TotalWTaxes', number_format( $TotalWTaxes, 6,".",""));
    if($maestro["MONTODES"]>0){
      $grandTotal->addChild('TotalBDiscounts', number_format( $montoTotalItems, 6,".",""));
      $grandTotal->addChild('TotalWDiscounts', number_format( $montoTotalItems - $maestro["MONTODES"], 6,".",""));

    }
    $grandTotal->addChild('InvoiceTotal', number_format( $montoTotalItems- $maestro["MONTODES"], 6,".",""));
    
    $payments = $xml->addChild('Payments');
    
    if($pagos)
    {
      foreach ($pagos as $pago) 
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

        if($formaPagoFact == '99')
        {
          $descripcionFormaPago = $formapago['descFormaPago'];
          $valorCuotaPagada = $this->transformarADecimales($pago["MONTOPAG"]);
          if (strlen($descripcionFormaPago) < 10) {
            $descFormPago = "***{$descFormPago}***";
          }
          $payment = $payments->addChild('Payment');
          $payment->addChild('Type', $formaPagoFact);
          $payment->addChild('Description', $descripcionFormaPago);
          $payment->addChild('Amount', $valorCuotaPagada);

        }
        else
        {
          $payment = $payments->addChild('Payment');
          $payment->addChild('Type', $formaPagoFact);
          $payment->addChild('Amount', $valorCuotaPagada);
        }
      }
    }
    else
    {
      if($maestro["MONTOSAL"]>0){
        $payment = $payments->addChild('Payment');
        $payment->addChild('Type', '01');
        $payment->addChild('Amount', $this->transformarADecimales($montoTotalItems - $maestro["MONTODES"]));
      }
      else{
        $payment = $payments->addChild('Payment');
        $payment->addChild('Type', '01');
        $payment->addChild('Amount', $this->transformarADecimales($montoTotalItems - $maestro["MONTODES"]));
      }

    }
    
    $additionalDocumentInfo = $xml->addChild('AdditionalDocumentInfo', null);
    $additionalInfo = $additionalDocumentInfo->addChild('AdditionalInfo', null);
    $aditionalInfo = $additionalInfo->addChild('AditionalInfo', null);
    $addInfo = $aditionalInfo->addChild('Info', null);
    $addInfo->addAttribute('Name', 'TiempoPago');
    $addInfo->addAttribute('Value', '1');
    if($CONESPECIAL == "1"){

      switch ($PORRETIMP) {
        case 100:
          $ValRetenc = $total_item_impuesto; 
          $codRetencion = 1;
          break;
        case 50:
          $ValRetenc = $total_item_impuesto*0.5; 
          $codRetencion = 4;
          break;
        
        default:
          $ValRetenc = ($total_item_impuesto*($PORRETIMP/100)); 
          $codRetencion = 8;
          break;
      }
      
      $retencion = $facturacion->getRetencionMaestro($maestro["NUMREF"]);
      $ValRetenc = (float)$retencion["MONTOTOT"];
      $addInfo = $aditionalInfo->addChild('Info', null);
      $addInfo->addAttribute('Name', 'CodRetenc');
      $addInfo->addAttribute('Value', $codRetencion);
      $addInfo = $aditionalInfo->addChild('Info', null);
      $addInfo->addAttribute('Name', 'ValRetenc');
      $addInfo->addAttribute('Value', number_format( $ValRetenc, 6,".",""));
    }
    
    if($adendas){
      // Rellenar el XML con la estructura deseada
      $AdditionalInfo = $additionalDocumentInfo->addChild('AdditionalInfo');

      $AdditionalInfo->addChild('Code', $maestro["NUMREF"]);
      $AdditionalInfo->addChild('Type', 'ADENDA');

      $additionalData = $AdditionalInfo->addChild('AditionalData');

      $data = $additionalData->addChild('Data');
      $data->addAttribute('Name', 'INFORMACION_ADICIONAL');
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'OBSERVACIONES');
      $info->addAttribute('Value', $adendas["OBS1"]);

      $data = $additionalData->addChild('Data');
      $data->addAttribute('Name', 'DetallesAux_Detalle');
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'NumeroLinea');
      $info->addAttribute('Value', '1');
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'Descripcion_Adicional');
      $info->addAttribute('Value', $adendas["OBS1"]);
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'CodigoEAN');
      $info->addAttribute('Value', '00001');
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'CategoriaAdicional');
      $info->addAttribute('Value', 'CATEGORIA');
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'Textos');
      $info->addAttribute('Value', 'TEXTOS1');
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'Textos');
      $info->addAttribute('Value', 'TEXTOS2');
      $info = $data->addChild('Info');
      $info->addAttribute('Name', 'Textos');
      $info->addAttribute('Value', 'TEXTOS3');

      $additionalInfo = $AdditionalInfo->addChild('AditionalInfo');
      $info           = $additionalInfo->addChild('Info');
      $info->addAttribute('Name', 'VALIDAR_REFERENCIA_INTERNA');
      $info->addAttribute('Value', 'NO_VALIDAR');
      $info = $additionalInfo->addChild('Info');
      $info->addAttribute('Name', 'VERSION');
      $info->addAttribute('Value', '1.00000000');

    }
    else{
      $adenda = "";
    }

    // Obtener el XML como string
    $formattedXml = $xml->asXML();
    $pathLog = 'request/'.$maestro["NUMREF"].'.xml';
    $xml->asXML($pathLog);

    // Cargar el contenido del archivo y formatear XML
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->load($pathLog); // Aquí se carga el contenido del archivo XML
    $formattedXml = $dom->saveXML();

    // Guardar el XML formateado en un archivo o imprimirlo en pantalla
    $pathLog = 'request/'.$maestro["NUMREF"].'.xml';
    file_put_contents($pathLog, $formattedXml);

    $ruta  =$CONFIG["DIRECCIONENVIO"]."/transform/nuc/?TAXID=".$CONFIG["RUC_DIGI"]."&FORMAT=PDF&USERNAME=".$CONFIG["USUARIO_DIGI"];
    $token = "Authorization: ".$token;
    $header = array(
      "Content-Type: application/xml",
      (string)$token
    );
    //print_r($formattedXml);exit;
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => $ruta,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "$formattedXml",
      CURLOPT_HTTPHEADER => $header,
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      
      $response    = json_decode($response, true);
      $respuesta["codigo"]  = $response["codigo"];
      $respuesta["response"] = $response["mensaje"];
      if((int)$response["codigo"] == 1)
      {
        $resultado = "procesado";
        $respuesta["mensaje"] = $response["mensaje"];
    
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
            :pdf AS PDF,
            :nrodocfiscal AS NUMDOCFISCAL,
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
                  target.PDF = source.PDF,
                  target.NUMDOCFISCAL = source.NUMDOCFISCAL,
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
                PDF,
                NUMDOCFISCAL,
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
                source.PDF,
                source.NUMDOCFISCAL,
                source.FECHARECEPCIONDGI,
                source.NROPROTOCOLOAYTORIZACION,
                source.FECHALIMITE);
        ";
    
        $stmt = $this->base_de_datos->prepare($sql);
        
        $fechaRecepcionDGI = !empty($datos['fecha_fe']) ? date('Y-m-d H:i:s', strtotime($datos['fecha_fe'])) : null;
        $stmt->bindParam(':fechaRecepcionDGI', $fechaRecepcionDGI, PDO::PARAM_STR);
        
        $fechaLimite = !empty($datos['fechaLimite']) ? date('Y-m-d', strtotime($datos['fechaLimite'])) : null;
        $stmt->bindParam(':fechaLimite', $response["fecha_fe"], PDO::PARAM_STR);
        $stmt->bindParam(':codigo', $response['codigo'], PDO::PARAM_INT);
        $stmt->bindParam(':control', $control, PDO::PARAM_STR);
        $stmt->bindParam(':resultado', $resultado, PDO::PARAM_STR);
        $stmt->bindParam(':mensaje_1', $response['mensaje'], PDO::PARAM_STR);
        $stmt->bindParam(':cufe', $response['CUFE'], PDO::PARAM_STR);
        $stmt->bindParam(':qr', $response['linkQR'], PDO::PARAM_STR);
        $stmt->bindParam(':pdf', $response['responseData3'], PDO::PARAM_STR);
        $stmt->bindParam(':nrodocfiscal', $numeroDocumentoFiscal, PDO::PARAM_STR);
        $stmt->bindParam(':nroProtocoloAutorizacion', $response['suggestedFileName'], PDO::PARAM_STR);
    
        // Validar y formatear las fechas antes de la inserción
        $fechaRecepcionDGI = !empty($response['fecha_fe']) ? date('Y-m-d H:i:s', strtotime($response['fecha_fe'])) : null;
        $stmt->bindParam(':fechaRecepcionDGI', $fechaRecepcionDGI, PDO::PARAM_STR);
        
        $fechaLimite = !empty($response['fechaLimite']) ? date('Y-m-d H:i:s', strtotime($response['fechaLimite'])) : null;
        $stmt->bindParam(':fechaLimite', $fechaLimite, PDO::PARAM_STR);
        
        $stmt->execute();
        $sql_update = "UPDATE TRANSACCMAESTRO SET COM_FISCAL = :COM_FISCAL, URLCONSULTAFEL = :CUFE WHERE CONTROL = :control_;";
        $stmt = $this->base_de_datos->prepare($sql_update);
        $stmt->bindParam(':CUFE', $response['linkQR'], PDO::PARAM_STR);
        $stmt->bindParam(':COM_FISCAL', $response['CUFE'], PDO::PARAM_STR);
        $stmt->bindParam(':control_', $control, PDO::PARAM_STR);
        $stmt->execute();
      
      }else{
        
        $respuesta["mensaje"] = $response["codigosDGI"]." ".$response["descripcion"];
      }
      return($respuesta);
    }
    

  }
};
?>
