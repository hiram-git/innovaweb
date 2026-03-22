<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
/*
if ( ! isset($_GET['pdf']) ) {
	$content = '<html>';
	$content .= '<head>';
	$content .= '<style>';
	$content .= 'body { font-family: DejaVu Sans; }';
	$content .= '</style>';
	$content .= '</head><body>';
	$content .= '<h1>Ejemplo generaci&oacute;n PDF</h1>';
	$content .= '<a href="index.php?pdf=1">Generar documento PDF</a>';
	$content .= '</body></html>';
	echo $content;
	exit;
}*/

/*$content = '<html>';
$content .= '<head>';
$content .= '<style>';
$content .= '</style>';
$content .= '</head><body>';
$content .= "<h1 style='color:red;'>Ejemplo generaci&oacute;n PDF</h1>";
$content .= 'Almacena en una variable todo el contenido que quieras incorporar ';
$content .= 'en el documento <b>formato HTML</b> para generar a partir de &eacute;ste ';
$content .= 'el documento PDF.<br><br>';
$content .= 'Ejemplo lista<br>';
$content .= '<ul><li>Uno</li><li>Dos</li><li>Tres</li></ul>';
$content .= 'Ejemplo imagen<br><br>';
$content .= '<img src="logo-openwebinars.png" alt="" />';
$content .= '</body></html>';*/

$file = "data.txt";
$fp = fopen($file, "r");
$content = fread($fp, filesize($file));
//echo $content; exit;

/*
$options = new Options();
$options->set('isRemoteEnabled',true);      
$dompdf = new Dompdf( $options );*/
//$dompdf = new Dompdf();
$dompdf = new Dompdf(array('enable_remote' => true));
//$html = $dompdf->view('data.txt',[],true);
$dompdf->loadHtml($content);
$dompdf->setPaper('letter', 'portrait'); // (Opcional) Configurar papel y orientación
$dompdf->render(); // Generar el PDF desde contenido HTML
$pdf = $dompdf->output(); // Obtener el PDF generado
$dompdf->stream(); // Enviar el PDF generado al navegador