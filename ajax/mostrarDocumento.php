<?php
session_start();
include_once "../config/db.php";
include_once "../fel/thfkapanama/Facturacion.php";
$accion = isset( $_POST["accion"] ) ? $_POST["accion"] : '' ;
if($accion == "mostrarDocumento"){
    $CONTROL = isset( $_POST["control"] ) ? $_POST["control"] : '' ;
    $caso = isset( $_POST["caso"] ) ? $_POST["caso"] : '' ;
    $tiptran = isset( $_POST["tiptran"] ) ? $_POST["tiptran"] : '' ;
    $parcontrol  = isset($_SESSION['id_control']) ? trim($_SESSION['id_control']) : $_REQUEST['parcontrol'];

    $facturacion = new Facturacion($base_de_datos);

    $empresa = $facturacion->getConfig( $parcontrol );
    if($empresa["TIPO_FACTURA"] == "Ticket"){
        switch ($tiptran) {
            case 'FAC':
                include_once "../ticket/crearTicket.php";
                break;
            case 'PEDxCLI':
                include_once "../ticket/crearPedido.php";
                $params = array(
                    "CONTROL"     => $CONTROL,
                    "Facturacion" => $facturacion);
                $pdf          = crearPedido($params);
                $ticket       = $pdf->Output('Pedido.pdf', 'E');
                $parts        = explode("\r\n\r\n", $ticket);
                $documentoPdf = array("control" => $CONTROL, "PDF"=> $parts["1"]);
                break;
            case 'PRE':
                include_once "../ticket/crearPresupuesto.php";
                $params = array(
                    "CONTROL"     => $CONTROL,
                    "Facturacion" => $facturacion);
                $pdf          = crearPresupuesto($params);
                $ticket       = $pdf->Output('Presupuesto.pdf', 'E');
                $parts        = explode("\r\n\r\n", $ticket);
                $documentoPdf = array("control" => $CONTROL, "PDF"=> $parts["1"]);
                break;
            case 'PAGOS':
                include_once "../ticket/crearPago.php";
                $params = array(
                    "CONTROL"     => $CONTROL,
                    "Facturacion" => $facturacion);
                $pdf          = crearPago($params);
                $ticket       = $pdf->Output('Pago.pdf', 'E');
                $parts        = explode("\r\n\r\n", $ticket);
                $documentoPdf = array("control" => $CONTROL, "PDF"=> $parts["1"]);
                break;
            case 'LISTAPAGOS':
                include_once "../ticket/crearListaPagos.php";
                $params = array(
                    "CONTROL"     => $CONTROL,
                    "Facturacion" => $facturacion);
                $pdf          = crearListaPagos($params);
                $ticket       = $pdf->Output('Pago.pdf', 'E');
                $parts        = explode("\r\n\r\n", $ticket);
                $documentoPdf = array("control" => $CONTROL, "PDF"=> $parts["1"]);
                break;
            
            default:
                include_once "../ticket/crearTicket.php";
                break;
        }
        
    
        ob_clean();
        http_response_code(200);
        header('content-type: application/json');
        echo json_encode( $documentoPdf );

    }else{

        $sql3 = "SELECT * from  Documentos where CONTROL = '{$CONTROL}';";
        
        $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
        $total_reg = $result->fetchColumn();
        $sentencia4 = $base_de_datos->prepare($sql3, [
            PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
        ]);
    
        ob_clean();
        $sentencia4->execute();
        http_response_code(200);
        header('content-type: application/json');
        echo json_encode( $sentencia4->fetch(PDO::FETCH_ASSOC) );

    }
    

}