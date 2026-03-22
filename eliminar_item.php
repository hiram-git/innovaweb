<?php 
session_start();
$iditem=$_GET['iditem'];
array_splice($_SESSION['aDatos'], $iditem, 1);
echo "<script type='text/javascript'>
        window.history.back();
        </script>";
?>