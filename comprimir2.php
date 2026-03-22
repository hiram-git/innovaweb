<?php
session_start();
date_default_timezone_set($_SESSION['zonah']);

// Initialize archive object
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$cad_mes=$meses[date('n')-1];
$dia = date("d");
$anio = date("Y");
$mis = date("His");

$file = fopen("version.txt", "w");
fwrite($file, "Version: ".$dia."-".$cad_mes."-".$anio." ".$mis);
fclose($file);

$rootPath = realpath('../cotizacion');
$zip = new ZipArchive();
$zip->open("../cotizacion_".$dia."".$cad_mes."".$anio."_".$mis.".zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

// Zip archive will be created only after closing object
$zip->close();

echo "
    <script>
        setTimeout(function(){ history.go(-2); }, 0);
    </script>
    ";
?>