<?php 
session_start();
$_SESSION['aDatos'] = array();
unset($_SESSION['tipo_tarea']);
echo "<script type='text/javascript'>
        window.history.back();
        </script>";
?>