<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous"> -->

<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
<script src="jquery/jquery-3.2.1.slim.min.js" ></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
<script src="jquery/sweetalert.min.js"></script>
<style>
  body { 
    border: 0px solid black;
    padding: 0px;
    background: url(imgs/fondo.jpg) no-repeat fixed center;
    background-repeat: no-repeat;
    /*background-size: 100%;*/
    background-size: cover;
  }
</style>
</head>
<body>
<header>
    <!-- <div class="logo"><img src="imgs/logotipo.png" width="100%"></div> 
  
          <div class="container justify-content-left">
            <div class="card mt-5" style='margin-top: 2rem !important; border: 0px solid rgba(0,0,0,.125) !important;'>
                <a href="index.php"><img src="imgs/logo2.png" width='165px'></a>
                <hr/>
            </div>
          </div>
          -->
</header>
<main>
        <br /><br /><br />
<?php
$usuario_=$_POST['usuario'];
$password_=$_POST['password'];
$servidor_=$_POST['servidor'];
$dbname_=$_POST['dbname'];
//echo "$usuario_ $password_ $servidor_ $dbname_<br />";
$clave = $password_;
$usuario = $usuario_;
$nombreBaseDeDatos = $dbname_;//"rock";
$rutaServidor = $servidor_;//"DESKTOP-8FRT3NO";

try {
    //echo "sqlsrv:server=$rutaServidor;database=$nombreBaseDeDatos, ".$usuario.", $clave";
    $base_de_datos = new PDO("sqlsrv:server=$rutaServidor;database=$nombreBaseDeDatos", $usuario, $clave);
    $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div style='color:#fff;'><center><h4>Conexi&oacute;n exitosa!</h4></center></div>";

    $cadena='
    <?php
    $clave = "'.$password_.'";
    $usuario = "'.$usuario_.'";
    $nombreBaseDeDatos = "'.$dbname_.'";
    $rutaServidor = "'.$servidor_.'";
    try {
        $base_de_datos = new PDO("sqlsrv:server=$rutaServidor;database=$nombreBaseDeDatos", $usuario, $clave);
        $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo "Ocurri&oacute; un error con la conexi&oacute;n a Base de Datos: " . $e->getMessage();
    }
    ?>';
    $file_path = "config/db.php";
    
    // Extrae la ruta del directorio del archivo
    $directory_path = dirname($file_path);
    
    // Verifica si el directorio no existe
    if (!file_exists($directory_path)) {
        // Intenta crear el directorio
        if (!mkdir($directory_path, 0777, true)) {
            die("Error al crear el directorio '$directory_path'.\n");
        }
    }
    
    // Luego, puedes continuar con tu operación de apertura de archivo
    $file = fopen($file_path, "w");
    fwrite($file, "$cadena");
    fclose($file);
    
    include_once "config/db.php";

    $timezones = array(
        'America/Panama', 'America/Lima', 'America/Puerto_Rico', 'America/Santo_Domingo',
        'America/Santiago', 'America/Montevideo', 'America/Mexico_City', 'America/La_Paz',   
        'America/Havana', 'America/Guatemala', 'America/El_Salvador', 'America/Costa_Rica',
        'America/Caracas', 'America/Bogota', 'America/Sao_Paulo', 'America/New_York'
    );
    array_multisort($timezones);
    //var_dump($timezones);

    echo "<div><center>
    <form method='post' action='dat_register.php' name='signin-form' onsubmit='return validacion();'>
    <table border=0><tr><td><br /><select class='form-control' searchable='...' id='zonah' name='zonah'>
    <option value='' disabled selected>Zona horaria seg&uacute;n tu ciudad</option>";
    foreach($timezones as $zones){
        echo "<option value='$zones'>$zones</option>";
    }
    echo "<option value=''></option></select></td></tr>
    <tr><td style='text-align:center;'>
    <br />
    <button type='submit' class='btn' style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important; width:50%;'><strong>GUARDAR</strong></button>
    
    </td></tr></table>
    </form></center>
    </div>";
    
    
     
} catch (Exception $e) {
    echo "<div><center><h5>Ocurri&oacute; un error con la conexi&oacute;n a Base de Datos: " . $e->getMessage()."</h5></center></div>";
    /*echo "<script>
      setTimeout(function(){ history.go(-1); }, 3000);
      </script>";*/
}

?>
</main>
<script>
function validacion(){
  if (document.getElementById("zonah").value=='') {
    //alert('[ERROR] El campo ZONA HORARIA debe tener un valor');
    swal("Alerta!", 'El campo ZONA HORARIA debe tener un valor', {buttons:false,});
    return false;
  }

  return true;
}
</script>
</body>
</html> 