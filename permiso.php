<?php
session_start();
if(isset($_SESSION['zonah'])){
    date_default_timezone_set($_SESSION['zonah']);
}

$file ='config/db.php';
$exists = is_file($file);
if($exists){
    
    if(isset($_SESSION['sesion_iniciada'])){
        //echo "sesion iniciada".$_SESSION['tiempo'];
        $inactivo = 6000;
 
        if(isset($_SESSION['tiempo']) ) {
            $vida_session = time() - $_SESSION['tiempo'];
            if($vida_session > $inactivo)
            {
                session_destroy();
                echo "<script type='text/javascript'>
                setTimeout(function(){ window.location='index.php'; }, 0);
                </script>";
                exit();
            }
            //echo $vida_session;
        }
    
        $_SESSION['tiempo'] = time();
    }else{
        
        echo "<script type='text/javascript'>
        setTimeout(function(){ window.location='index.php'; }, 0);
        </script>";
        exit();
    }
}else{
    echo "<script type='text/javascript'>
    setTimeout(function(){ window.location='index.php'; }, 0);
    </script>";
    exit();
}   
?>