<?php
/*
$pin=$_POST['repin'];
$file = fopen("config/token.txt", "w");
fwrite($file, "$pin");
fclose($file);*/
session_start();
session_destroy(); // destruyo la sesión
?>
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
    <section>
      <br /><br />
      <div class="container d-flex justify-content-center">
        <form id='form_con' method='POST' action='con_register.php' onsubmit="return validacion()">
          <table border=0 cellpadding=7>
          <tr>
            <td style='text-align:center;color:#fff;'><h5>CREAR CONEXION A BASE DE DATOS</h5><br />
            </td>
          </tr>
          <tr>
            <td><i class="fa fa-user" aria-hidden="true" style="font-size:30px;"></i> <label style='color:#fff;'>USUARIO</label><br />
                <input type="text" class="form-control" id='usuario' name='usuario' placeholder="" aria-label="cliente" aria-describedby="basic-addon1"></td>
          </tr>
          <tr>
            <td><i class="fa fa-key" aria-hidden="true" style="font-size:30px;"></i> <label style='color:#fff;'>CONTRASE&Ntilde;A</label><br />
                <input type="text" class="form-control" id='password' name='password' placeholder="" aria-label="ruc" aria-describedby="basic-addon1"></td>
          </tr>
          <tr>
            <td><i class="fa fa-database" aria-hidden="true" style="font-size:30px;"></i> <label style='color:#fff;'>SERVIDOR DE BASE DE DATOS</label><br />
            <input type="text" class="form-control" id='servidor' name='servidor' placeholder="" aria-label="nombre" aria-describedby="basic-addon1"></td>
          </tr>
          <tr>  
            <td><i class="fa fa-table" aria-hidden="true" style="font-size:30px;"></i> <label style='color:#fff;'>NOMBRE DE BASE DE DATOS</label><br />
            <input type="text" class="form-control" id='dbname' name='dbname' placeholder="" aria-label="nombre" aria-describedby="basic-addon1"></td>
          </tr>
          <tr>
            <td style='text-align:center;'>
            <!-- Submit button -->
            <br />
            <!-- <button type="submit" class="btn btn-primary">Conectar</button> -->
            <button type="submit" class="btn" style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important; width:50%;'><strong>CONECTAR</strong></button>
            </td>
          </tr>
          </table>
        </form>
      </div>
    </section>
  </main>
<script>
  function validacion() {
  if (document.getElementById("usuario").value=='') {
    // Si no se cumple la condicion...
    //alert('[ERROR] El campo USUARIO debe tener un valor');
    swal("Alerta!", 'El campo USUARIO debe tener un valor', {buttons:false,});
    return false;
  }
  else if (document.getElementById("password").value=='') {
    // Si no se cumple la condicion...
    //alert('[ERROR] El campo CONTRASE\361A debe tener un valor');
    swal("Alerta!", 'El campo CONTRASE\361A debe tener un valor', {buttons:false,});
    return false;
  }
  else if (document.getElementById("servidor").value=='') {
    // Si no se cumple la condicion...
    //alert('[ERROR] El campo NOMBRE DE SERVIDOR debe tener un valor');
    swal("Alerta!", 'El campo NOMBRE DE SERVIDOR debe tener un valor', {buttons:false,});
    return false;
  }else if (document.getElementById("dbname").value=='') {
    // Si no se cumple la condicion...
    //alert('[ERROR] El campo NOMBRE DE BASE DE DATOS debe tener un valor');
    swal("Alerta!", 'El campo NOMBRE DE BASE DE DATOS debe tener un valor', {buttons:false,});
    return false;
  }

  // Si el script ha llegado a este punto, todas las condiciones
  // se han cumplido, por lo que se devuelve el valor true
  return true;
}
</script>
</body>
</html>