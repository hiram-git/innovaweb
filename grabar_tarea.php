<?php
include_once "permiso.php";
include_once "config/db.php";
$tarea=$_GET['tarea'];
$_SESSION['tipo_tarea'] = $tarea;
echo "1";
?>