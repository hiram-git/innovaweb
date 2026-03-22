<?php
session_start();
include_once "config/db.php";
$empresa=$_GET['empresa'];
$exp_empresa=explode("-", $empresa);
$contrl=$exp_empresa[0];
$nom_empresa=$exp_empresa[1];
//$contadorcli=$exp_empresa[2];
$_SESSION['id_control'] = $contrl;
$_SESSION['nom_empresa'] = $nom_empresa;
$_SESSION['title'] = $nom_empresa;
//$_SESSION['contadorcli'] = $contadorcli;
echo "1";
?>