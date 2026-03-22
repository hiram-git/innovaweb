<?php

require_once('../library/vendor/autoload.php');

function formatFecha( $fecha, $tipo ){
    switch ($tipo) 
    {
        case '1':
            $fechaFormatoDateTime = DateTime::createFromFormat('Ymd', $fecha);
            return $fechaFormatoDateTime->format('d-m-Y');
            break;
        
        case '2':
            $fechaFormatoDateTime = DateTime::createFromFormat('Y-m-d H:i:s.u', $fecha);
            return $fechaFormatoDateTime->format('d-m-Y H:i:s');
            break;
        
        default:
            # code...
            break;
    }
}
function formatDecimal($numero){
    return number_format((float)$numero, 2, '.', '');
}

function crearTicket($params)
{
    $CONTROL = $params["CONTROL"];
    $facturacion = $params["Facturacion"];
    class PDF_REPORTE extends TCPDF
    {
        public $num_doc_fiscal = '';
        public $detalles = '';
        public function Header()
        {
        }

        public function Footer()
        {
        }
    }

    function center_txt($str, $ancho, $fill = " ")
    {
        $str = substr($str, 0, $ancho);
        $lenght = strlen($str);
        $pos = ($ancho - $lenght) / 2;
        return str_repeat($fill, $pos) . $str;
    }

    $sql  = "SELECT * FROM BASEEMPRESA;";
    $db   = $facturacion->base_de_datos;
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $DATOS_EMPRESA          = $stmt->fetch(PDO::FETCH_ASSOC);
    $empresa                = $DATOS_EMPRESA["NOMBRE"];
    $empresa_identificacion = $DATOS_EMPRESA["NUMFISCAL"];
    $logo                   = $DATOS_EMPRESA["RUTALOGOEMPRE"];
    $empresa_direccion      = $DATOS_EMPRESA["DIRECC1"]." ". $DATOS_EMPRESA["DIRECC2"];

    $telefonos       = $DATOS_EMPRESA["NUMTEL"];
    $maestro         = $facturacion->getFacturaMaestro($CONTROL);

    $detallesFactura = $facturacion->getDetalleFactura($CONTROL);
    $Documento       = $facturacion->getDocumentos($CONTROL);
    $Pagos           = $facturacion->getPagos($CONTROL);
    $cliente         = $facturacion->getCliente($maestro["CODIGO"]);
    $vendedor        = $facturacion->getVendedor($maestro["CODVEN"]);
    $fecha_creacion   = formatFecha($maestro["FECEMISS"], 1);
    $codigo_qr        = isset($Documento['QR']) && $Documento['QR'] ? $Documento['QR'] : '';
    $num_doc_fiscal        = isset($Documento['NROINIFAC']) && $Documento['NROINIFAC'] ? $Documento['NROINIFAC'] : '';
    $nro_autorizacion = isset($Documento['NROPROTOCOLOAYTORIZACION']) && $Documento['NROPROTOCOLOAYTORIZACION'] ? $Documento['NROPROTOCOLOAYTORIZACION'] : '';
    $fecha_emision = isset($Documento['FECHARECEPCIONDGI']) && $Documento['FECHARECEPCIONDGI'] ? formatFecha($Documento["FECHARECEPCIONDGI"] ,2 ) : '';

    $NOMBRE_CLIENTE = (!empty($cliente['NOMBRE'])) ? $cliente['NOMBRE'] : "-----";
    $DIR_CLIENTE = (!empty($cliente['DIRECC1'])) ? $cliente['DIRECC1'] : "-----";
    $EMAIL_CLIENTE  = (!empty($cliente['DIRCORREO'])) ? $cliente['DIRCORREO'] : "-----";
    $COD_FACTURA    = trim($maestro["NUMREF"]);
    $ancho          = 50;

    // ** 1. INFORMACION DE LA TIENDA
    $salida1 = "";
    $salida1 .= ("Comprobante Auxiliar de Factura")."";
    $salida1 .= (" Electrónica")." \n\n";
    $salida1 .= ($empresa) . "\n";
    $salida1 .= ("RUC: $empresa_identificacion") . "\n";
    $salida1 .= ($empresa_direccion) . "\n";
    $salida1 .= ($telefonos) . "\n";
    $salida1 .= "\n";
    $salida1 .= "\n";

    $salida2 = "";
    //$salida1 .= substr("Tienda: " . trim($factura["serie_sucursal"]) . "    Caja Registradora: " . trim($factura["codigo_caja"]), 0, $ancho) . "\n";
    $salida2 .= substr("RUC: " . trim($cliente["RIF"]), 0, $ancho) . "\n";
    $salida2 .= substr("Nombre: " . trim($NOMBRE_CLIENTE), 0, $ancho) . "\n";
    $salida2 .= substr("Dirección: " . trim($DIR_CLIENTE), 0, $ancho) . "\n";
    $salida2 .= substr("Vendedor: " . trim($vendedor["NOMBRE"]), 0, $ancho) . "\n";

    $salida2 .= substr("Fecha: " . date('j/N/y', strtotime($fecha_creacion) ), 0, $ancho) . "\n";
    $salida2 .= substr("Factura: " . trim($COD_FACTURA), 0, $ancho) . "\n";
    $salida2 .= "\n";

    // ** 2. INFORMACION DEL CIENTE
    $salida2 .= "\n";

    // ** 3. INICIALIZAR TOTALES GLOBALES
    $total_importes   = formatDecimal($maestro['MONTOSUB']);
    $total_descuentos = formatDecimal($maestro['MONTODES']);
    $total_exento     = 0;
    $total_gravado    = formatDecimal($maestro['MONTOTOT']);
    $total_impuesto   = formatDecimal($maestro['MONTOIMP']);
    $total_total      = formatDecimal($maestro['MONTOTOT']);

    // ** 4. INICIO DETALLE DE ARTICULOS  
    $header_articulos = "CANT.       DESCRIPCION.       TOTAL";

    // Ancho + 1 es repetido varias veces, definamos una variable para evitar recalculaciones
    $salida3 = "";
    $salida3 .= substr($header_articulos, 0, $ancho) . "\n";
    $ancho_detalles = ($ancho-2);
    $salida3 .= str_repeat("_", $ancho_detalles) . "\n";

    // ** 4.2 PREPARAR TOTALES DE SUMATORIAS GLOBALES
    $resumen_sumatorias = [
        'Total Importe'  => number_format(trim($total_importes), 2),
        'Descuentos'     => number_format(trim($total_descuentos), 2),
        'Monto Exento'   => number_format(trim($total_exento), 2),
        'Monto Gravado'  => number_format(trim($total_gravado), 2),
        'Total Impuesto' => number_format(trim($total_impuesto), 2),
        'Total'          => number_format(trim($total_total), 2),
    ];

    $sumatorias_totales = [];
    foreach ($resumen_sumatorias as $label => $monto) {
        $cant_left             = 29;
        $total_espacios        = $cant_left - strlen($label) - (strlen($monto));
        $row                   = $label . "" . str_repeat(" ", $total_espacios) . $monto;
        $row                   = str_repeat(" ", 10) . $row;
        $sumatorias_totales [] = $row;
    }

    // ** 4.3 PINTAR TOTALES DE SUMATORIAS GLOBALES
    $salida4 = "";
    $salida4 .= str_repeat("_", ($ancho+1)) . "\n";
    $salida4 .= join("\n", $sumatorias_totales) . "\n";
    $salida4 .= "\n";
    //$salida1 .= "\n";
    //$salida1 .= "\n";
    //$salida1 .= str_repeat("_", $ancho_detalles) . "\n";

    // ** 4.4 PINTAR FORMAS DE PAGO
    $forma_pago_lista = [];
    foreach ($Pagos as $forma_pago) {
        $descripcion = $forma_pago['NOMBRE'];
        $tdc_numero = "";
        $descripcion = $descripcion . "" . $tdc_numero;
        $smonto = number_format(trim($forma_pago['MONTOPAG']), 2);
        $row = $descripcion . str_pad($smonto, $ancho  - strlen($descripcion), " ", STR_PAD_LEFT);
        $forma_pago_lista [] = $row;
    }

    $salida5 = "";
    $salida5 .= str_repeat("_", $ancho+1) . "\n";
    $salida5 .= "\nMÉTODOS DE PAGO:\n";
    $salida5 .= join("\n", $forma_pago_lista) . "\n";
    $salida5 .= str_repeat("_", $ancho+1) . "\n";

    // ** 4.5 PINTAR CAMBIO
    if (trim($maestro['CAMBIO'])) {
        $smonto_cambio = number_format(trim($maestro['CAMBIO']), 2);
        $descripcion_cambio = "CAMBIO: ";
        $salida5 .= $descripcion_cambio . " " . str_pad($smonto_cambio, ($ancho_detalles - strlen($smonto_cambio)-3), " ", STR_PAD_LEFT);
        $salida5 .= "\n".str_repeat("_", $ancho+1) . "\n";
        $salida5 .= "\n";
    }

    // ** 5 CALCULAR NUMERO DE LINEAS NECESARIAS PARA PINTAR PDF DE FORMA VERTICAL DE FORMA CONTINUA
    //      SI EXISTO CODIGO QR EL VALOR DE INCREMENTO SERA 190 DE LO CONTRARIO SERA 60
    //      SI DESEA PUEDE JUGAR CON ESTOS VALORES HASTA ENCONTRAR LOS VALORES
    //      CORRECTOS PARA HACER PINTAR EL PDF CORRECTAMENTE
    $ancho = 80;
    $n = 29 + (int)count($detallesFactura);
    
    $incrementar_lineas = $codigo_qr ? 180 : 120;
    $alto = 3.8 * $n + $incrementar_lineas;
    if ($alto < $ancho) {
        $alto = $ancho;
    }
    ob_clean();
    $ancho = 82;
    //$alto = 200;


    $pdf = new PDF_REPORTE('P', 'mm', [$ancho, $alto]);
    $pdf->SetAuthor('Innova');
    $pdf->SetMargins(0, 0, 0, 0);
    $pdf->SetTitle('Ticket');
    $pdf->SetDisplayMode('fullpage', 'two');
    $pdf->SetAutoPageBreak(true, 0);
    $pdf->AddPage('P');
    $pdf->num_doc_fiscal = $num_doc_fiscal;

    //$logo_file = '../../../../../includes/imagenes/' . $logo;
    //$logo = "";
    $y_inicio = 10;
    if ($logo && file_exists($logo_file)) {

        $pdf->Image($logo_file, 20, -1, 40, '', '', '', '', false, 150, '', false, false, 0, false, false, false);
        $y_inicio = 20;
    }


    $pdf->SetFont('courier', 'B', 8);

    //$salida1 .= $salida2;
    $pdf->Multicell(78, 4, $salida1, 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 1, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(78, 4, $salida2, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 1);
    /* Detalles del producto*/


    $pdf->Multicell(20, 4, "CANT", "B", $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 0);
    $pdf->Multicell(44, 4, "DESCRIPCION", "B", $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '22', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(14, 4, "TOTAL", "B", $alineacion = 'R', $fondo = false, $salto_de_linea = 1, $x = '66', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $referencia = "";
    foreach ($detallesFactura as $detalle) {
        $referencia = trim($detalle["CODPRO"]);
        $producto = $facturacion->getProdDetalle($referencia);
        $ampliada = $facturacion->getAmpliadaDetalle($detalle["FECHORA"]);

        $descripcion = trim($producto["DESCRIP1"]);
        $desc2 = trim($producto["DESCRIP2"]);
        $desc3 = trim($producto["DESCRIP3"]);
        //$descripcion = (strlen($descripcion) >= 30) ? substr($descripcion, 0, 100) . "." : $descripcion;
        
        $linea = $referencia . " " .$descripcion;

        $unidad_o_empaque = trim(isset($detalle["unidad_empaque"]) ? $detalle["unidad_empaque"] : "UN");
        $valor_unidad     = number_format(trim($detalle["PRECOSUNI"]), 2);
        $cantidad         = trim(floatval($detalle["CANTIDAD"]));
        $monto            = number_format($detalle['COSTOADU1'], 2);
        $descuento        = $detalle["PORDES"];
        $monto_descuento  = $detalle["MONTODESCUENTO"];
        //$linea_cant_unit =  $cantidad . " " . $unidad_o_empaque;
        $pdf->Multicell(18, 4, $referencia, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 0);
        $pdf->Multicell(58, 4, $descripcion, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '22', $y = '', $reseth = true, $ajuste_horizontal = 0);
        if($desc2){
            $pdf->Multicell(58, 4, $desc2, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '22', $y = '', $reseth = true, $ajuste_horizontal = 0);
        }
        if($desc3){
            $pdf->Multicell(58, 4, $desc3, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '22', $y = '', $reseth = true, $ajuste_horizontal = 0);
        }
        if($ampliada    ){
            $pdf->SetFont('courier', 'I', 9);
            $pdf->Multicell(58, 4, $ampliada["DESAPLIADA"], 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '22', $y = '', $reseth = true, $ajuste_horizontal = 0);

            $pdf->SetFont('courier', 'B', 9);
        }
        //
        $pdf->SetFont('courier', 'B', 7);
        $pdf->Multicell(20, 4, $cantidad."x". $valor_unidad, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 0);
        $pdf->SetFont('courier', 'B', 7);
        $linea_monto = str_repeat('  .', 15-strlen($monto))."  ".$monto;

        $pdf->Multicell(62, 4, $monto, 0, $alineacion = 'R', $fondo = false, $salto_de_linea = 1, $x = '18', $y = '', $reseth = true, $ajuste_horizontal = 0);
        
        if($monto_descuento > 0) {
            $row .= substr("DCTO: " . $descuento . "% " . $monto_descuento, 0, $ancho_detalles) . "\n";
        }
        $referencia = "";
        //$detalles[] = $row;
    }
    

    $cantidad_articulos_vendidos = count($detallesFactura);
    $salida6 = "\n\nCant. de articulos  = {$cantidad_articulos_vendidos}\n";
    // ** 4.1 PINTAR DETALLE EN SALIDA1
    //$salida3 .= join("\n", $detalles) ;
    //$pdf->Multicell(72, 4, $salida3, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(78, 4, $salida4, 0, $alineacion = 'R', $fondo = false, $salto_de_linea = 1, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(78, 4, $salida5, 0, $alineacion = 'R', $fondo = false, $salto_de_linea = 1, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(78, 4, $salida6, 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 1, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 1);


    $pdf->SetFont('courier', 'B', 9);
    if (trim($codigo_qr)) {

        $fecha_cafe_ingreso = date('d/m/Y H:i:s', strtotime($fecha_emision));
        $salida10 = "";
        $salida10 = "\nCAFE de emisión previa, transmisión de la DIRECCIÓN GENERAL DE INGRESOS hasta {$fecha_cafe_ingreso}\n";
        $salida10 .= "\nPara verificar el CUFE consulte en:\n\n https://fe.dgi.mef.gob.pa/consulta \nusando el codigo:\n";
        $salida10 .= "\n{$nro_autorizacion}\n";
        $salida10 .= "\nó escaneando el código QR:\n";
        $pdf->Multicell(72, 4, $salida10, 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 1, $x = '2', $y = '', $reseth = true, $ajuste_horizontal = 1);
        $pos_y = $pdf->GetY() + 5;

        $style = array();
        $pdf->write2DBarcode(trim($codigo_qr), 'QRCODE', $x = 5, $pos_y, 70, 70, array('border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points), 'C');
        ));
        $pos_y = $pdf->GetY() + 70;
    } 


    $pdf->SetFont('courier', 'B', 9);
    $pdf->Multicell(72, 4, "\n\nCopia de cliente", 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 1, $x = '2', $pos_y, $reseth = true, $ajuste_horizontal = 1, $ishtml = false, $autopadding = true, $maxh = 0, $alineacion_vertical = 'T', $fitcell = false);

    ob_clean();

    return $pdf;
}