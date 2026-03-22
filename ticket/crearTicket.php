<?php
include_once("ticket.php");
$params = array(
    "CONTROL"     => $CONTROL,
    "Facturacion" => $facturacion);
    
if($caso == "Reenvio"){

    $pdf          = crearTicket($params);
    $ticket       = $pdf->Output('FAC_'.$CONTROL.'.pdf', 'E');
    $parts        = explode("\r\n\r\n", $ticket);
    $documentoPdf = array("control" => $CONTROL, "PDF"=> $parts["1"]);
}
if($caso == "Envio"){

    $pdf          = crearTicket($params);
    $ticket       = $pdf->Output('FAC_'.$CONTROL.'.pdf', 'E');
    $parts        = explode("\r\n\r\n", $ticket);
    $documentoPdf = array("control" => $CONTROL, "PDF"=> $parts["1"]);
}