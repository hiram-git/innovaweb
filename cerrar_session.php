<?php
    session_start();
    session_destroy(); // destruyo la sesión
    echo "<script type='text/javascript'>
    setTimeout(function(){ window.location='index.php'; }, 0);
    </script>";

?>