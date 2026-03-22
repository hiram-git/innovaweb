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

function crearListaPagos($params)
{
    $REFERENCIA = $params["CONTROL"];
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

    $telefonos      = $DATOS_EMPRESA["NUMTEL"];
    $maestro        = $facturacion->getFacturaMaestroRef($REFERENCIA);
    $pagoscobros    = $facturacion->getCobrosMaestro($REFERENCIA);
    $cliente        = $facturacion->getCliente($maestro["CODIGO"]);
    $vendedor       = $facturacion->getVendedor($maestro["CODVEN"]);
    $fecha_creacion = formatFecha($maestro["FECEMISS"], 1);

    $NOMBRE_CLIENTE = (!empty($cliente['NOMBRE'])) ? $cliente['NOMBRE'] : "-----";
    $DIR_CLIENTE    = (!empty($cliente['DIRECC1'])) ? $cliente['DIRECC1'] : "-----";
    $EMAIL_CLIENTE  = (!empty($cliente['DIRCORREO'])) ? $cliente['DIRCORREO'] : "-----";
    $COD_FACTURA    = trim($maestro["NUMREF"]);
    $ancho          = 38;

    // ** 1. INFORMACION DE LA TIENDA
    $salida1 = "";
    $titulo  = ("\n\nRECIBO DE PAGO")."\n\n";
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
    $salida2 .= substr("Nro. Pago: " . trim($COD_FACTURA), 0, $ancho) . "\n";
    $salida2 .= "\n";

    // ** 2. INFORMACION DEL CIENTE
    $salida2 .= "\n";

    // ** 4. INICIO DETALLE DE ARTICULOS  
    $header_articulos = "FECHA.       DESCRIPCION.       TOTAL";

    // Ancho + 1 es repetido varias veces, definamos una variable para evitar recalculaciones
    $salida3 = "";
    $salida3 .= substr($header_articulos, 0, $ancho) . "\n";
    $ancho_detalles = ($ancho-2);
    $salida3 .= str_repeat("_", $ancho_detalles) . "\n";

    // ** 4.3 PINTAR TOTALES DE SUMATORIAS GLOBALES
    $salida4 = "";
    //$salida1 .= "\n";
    //$salida1 .= "\n";
    //$salida1 .= str_repeat("_", $ancho_detalles) . "\n";

    // ** 4.4 PINTAR FORMAS DE PAGO
    // ** 5 CALCULAR NUMERO DE LINEAS NECESARIAS PARA PINTAR PDF DE FORMA VERTICAL DE FORMA CONTINUA
    //      SI EXISTO CODIGO QR EL VALOR DE INCREMENTO SERA 190 DE LO CONTRARIO SERA 60
    //      SI DESEA PUEDE JUGAR CON ESTOS VALORES HASTA ENCONTRAR LOS VALORES
    //      CORRECTOS PARA HACER PINTAR EL PDF CORRECTAMENTE
    $ancho = 80;
    $n = 25;
    $incrementar_lineas = $codigo_qr ? 160 : 120;
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
    $pdf->SetTitle('Cobros'.$REFERENCIA);
    $pdf->SetDisplayMode('fullpage', 'two');
    $pdf->SetAutoPageBreak(true, 0);
    $pdf->AddPage('P');

    //$logo_file = '../../../../../includes/imagenes/' . $logo;
    //$logo = "";
    $y_inicio = 10;
    if ($logo && file_exists($logo_file)) {

        $pdf->Image($logo_file, 20, -1, 40, '', '', '', '', false, 150, '', false, false, 0, false, false, false);
        $y_inicio = 20;
    }


    $pdf->SetFont('courier', 'B', 12);

    //$salida1 .= $salida2;
    $pdf->Multicell(72, 4, $titulo, 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);

    $pdf->SetFont('courier', 'B', 9);
    $pdf->Multicell(72, 4, $salida1, 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(72, 4, $salida2, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    /* Detalles del producto*/


    $pdf->Multicell(19, 4, "FECHA", "B", $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(41, 4, "DESCRIPCION", "B", $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '22', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(16, 4, "TOTAL", "B", $alineacion = 'R', $fondo = false, $salto_de_linea = 1, $x = '63', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $MONTO_TOTAL = 0;
    $pdf->SetFont('courier', 'B', 8);

    foreach ($pagoscobros as $detalle) {
        
        $linea = $REFERENCIA;
        $fecha        = $fechaConvertida = date("Y-m-d", strtotime($detalle["FECEMISS"]));
        $monto  = formatDecimal($detalle["MONTOTOT"]);

        $descripcion = trim($detalle["DESCRIP1"]);
        $desc2 = trim($detalle["DESCRIP2"]);

        //$linea_cant_unit =  $cantidad . " " . $unidad_o_empaque;
        //
        $filaY = $pdf->GetY();
        if($detalle["MONTOTOT"] > 0 ){
            if($desc2){
                $descripcion .= " ".$desc2;
            }
            
            $tamano_fuente = 9; // Tamaño de la fuente inicial
            $ancho_celda = 40; // Ancho de la celda
            $alto_texto = $pdf->getStringHeight(($ancho_celda-10), $descripcion); // Obtener la altura exacta del texto           

            $pdf->Multicell(19, $alto_texto, $fecha, $borde = 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
            
            $pdf->Multicell($ancho_celda, $alto_texto, $descripcion, $borde = 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 0, $x = '23', $y = '', $reseth = true, $ajuste_horizontal = 1);
            $pdf->Multicell(16, $alto_texto, $monto, $borde = 0, $alineacion = 'R', $fondo = false, $salto_de_linea = 1, $x = '63', $y = '', $reseth = true, $ajuste_horizontal = 1);

            $MONTO_TOTAL += $monto;

        }
    }

    // ** 4.1 PINTAR DETALLE EN SALIDA1
    //$salida3 .= join("\n", $detalles) ;
    //$pdf->Multicell(72, 4, $salida3, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->SetFont('courier', 'B', 10);
    $pdf->Multicell(26, 4, "", 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);

    $pdf->Multicell(57, 4, "PAGADO:", 0, $alineacion = 'R', $fondo = false, $salto_de_linea = 0, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(19, 4, formatDecimal($MONTO_TOTAL), 0, $alineacion = 'R', $fondo = false, $salto_de_linea = 1, $x = '60', $y = '', $reseth = true, $ajuste_horizontal = 1);
    //$pdf->Multicell(78, 4, $salida5, 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(26, 4, "", 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);

    $pdf->SetFont('courier', 'B', 12);
    $pdf->Multicell(76, 4, "Recibido por: ", "T", $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(76, 4, "Cedula: ", 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    
    $pdf->SetFont('courier', 'B', 9);
    $pdf->Multicell(26, 4, "", 0, $alineacion = 'L', $fondo = false, $salto_de_linea = 1, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);
    $pdf->Multicell(76, 4, "SALDO: ".formatDecimal($maestro["MONTOSAL"]), 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 0, $x = '4', $y = '', $reseth = true, $ajuste_horizontal = 1);

    $pdf->Multicell(78, 4, "\n\nCopia a cliente", 0, $alineacion = 'C', $fondo = false, $salto_de_linea = 1, $x = '4', $pos_y, $reseth = true, $ajuste_horizontal = 1, $ishtml = false, $autopadding = true, $maxh = 0, $alineacion_vertical = 'T', $fitcell = false);

    ob_clean();

    return $pdf;
}