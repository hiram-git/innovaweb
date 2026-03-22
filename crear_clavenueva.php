<?php
include_once "pin.php"; 
session_start();
$_SESSION['aDatos'] = array();
unset($_SESSION['tipo_tarea']);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="imgs/logo.ico">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    INNOVA SOFT
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="fontawesome-free-6.2.1/css/all.css" rel="stylesheet">
  <link href="assets/css/now-ui-dashboard.css?v=1.6.0" rel="stylesheet" />
  <link href="assets/demo/demo.css" rel="stylesheet" />
  <style>
      .login-page .card-login .logo-container img {
        width: 140% !important;
      }

      .login-page .card-login .input-group:last-child {
        margin-bottom: 0px !important;
      }

      .full-page>.content {
        padding-top: 30px !important;
      }

      .password-strength {
        height: 5px;
        margin-top: 5px;
        border-radius: 3px;
      }

      .strength-weak { background-color: #ff4d4d; }
      .strength-medium { background-color: #ffd700; }
      .strength-strong { background-color: #4caf50; }
  </style>
<script>
  sessionStorage.clear();
</script>
</head>
<body class="login-page sidebar-mini ">
  <header>
    
  </header>
  <main>
  <?php
  if(!isset($_SESSION['sesion_iniciada'])){
  ?>
  <div class="wrapper wrapper-full-page ">
    <div class="full-page login-page section-image" filter-color="black" data-image="assets/img/bg14.jpg">
      <div class="content">
        <div class="container">
          <div class="col-md-4 ml-auto mr-auto">
            <form action='validar.php' method='POST'>
              <div class="card card-login card-plain">
                <div class="card-header ">
                  <div class="logo-container">
                    <img src="assets/img/now-logo.png" alt="">
                  </div>
                </div>
                <div class="card-body ">
                  <div class="input-group no-border form-control-lg">
                    <span class="input-group-prepend">
                      <div class="input-group-text">
                        <i class="now-ui-icons users_circle-08"></i>
                      </div>
                    </span>
                    <input id="input_datlog" name="input_datlog" type="text" class="form-control" value="<?php echo $_GET['namelog']; ?>" readonly placeholder="Usuario...">
                  </div>
                  <div class="input-group no-border form-control-lg">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <i class="now-ui-icons text_caps-small"></i>
                      </div>
                    </div>
                    <input id="input_datlog2" name="input_datlog2" type="password" placeholder="Contraseña..." class="form-control">
                  </div>
                  <div class="input-group no-border form-control-lg">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <i class="now-ui-icons text_caps-small"></i>
                      </div>
                    </div>
                    <input id="input_datlog3" name="input_datlog3" type="password" placeholder="Confirmar Contraseña..." class="form-control">
                  </div>
                  <div id="password-strength" class="password-strength"></div>
                </div>
                <center><div id="mensaje" style='color:#ffffff;'></div></center>
                <div class="card-footer ">
                  <button type="button" class="btn btn-primary btn-round btn-lg btn-block mb-3" onClick="grabar_clave();">Ingresar</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
  }else{
    echo "
      <script type='text/javascript'>
        window.location='clientes.php?input_buscar=';
      </script>";
  }
  ?>
    <?php include("recursos/loading.php");?>
  </main>
  <script src="assets/js/core/jquery.min.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/moment.min.js"></script>
  <script src="assets/js/plugins/bootstrap-switch.js"></script>
  <script src="assets/js/plugins/sweetalert2.min.js"></script>
  <script src="assets/js/plugins/jquery.validate.min.js"></script>
  <script src="assets/js/plugins/jquery.bootstrap-wizard.js"></script>
  <script src="assets/js/plugins/bootstrap-selectpicker.js"></script>
  <script src="assets/js/plugins/bootstrap-datetimepicker.js"></script>
  <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
  <script src="assets/js/plugins/bootstrap-tagsinput.js"></script>
  <script src="assets/js/plugins/jasny-bootstrap.min.js"></script>
  <script src="assets/js/plugins/fullcalendar.min.js"></script>
  <script src="assets/js/plugins/jquery-jvectormap.js"></script>
  <script src="assets/js/plugins/nouislider.min.js"></script>
  <script src="assets/js/plugins/chartjs.min.js"></script>
  <script src="assets/js/plugins/bootstrap-notify.js"></script>
  <script src="assets/js/now-ui-dashboard.min.js?v=1.6.0" type="text/javascript"></script>
  <script src="assets/demo/demo.js"></script>
  <script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#loading").hide();
		});
	</script>
  <script>
  $(document).ready(function() {
    demo.checkFullPageBackgroundImage();
  });

  $(function(){
    $("#myModal").modal();
  });

  function grabar_clave() {
    txt_log_name = document.getElementById("input_datlog").value;
    txt_log_pass = document.getElementById("input_datlog2").value;
    txt_log_confirm = document.getElementById("input_datlog3").value;
    txt_log_name = txt_log_name.trim();
    txt_log_pass = txt_log_pass.trim();
    txt_log_confirm = txt_log_confirm.trim();
    const mensaje = document.getElementById("mensaje");
    
    if (txt_log_pass !== txt_log_confirm) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Las contraseñas no coinciden!',
      });
      mensaje.innerHTML = "<label style='color:red;'>Las contraseñas no coinciden!</label>";
      return;
    }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        if (this.responseText == 1) {
          $("#myModal").modal('hide');
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Generación exitosa!',
            timer: 700,
            showConfirmButton: false
        }).then(() => {
            window.location = 'clientes.php?input_buscar=';
        });
        } else {
          mensaje.innerHTML = "<label style='color:red;'>Datos incorrectos!</label>";
        } 
      }
    };
    xhttp.open("GET", "grabar_clave.php?namelog=" + txt_log_name + "&passlog=" + txt_log_pass, true);
    xhttp.send();
  }

  function checkPasswordStrength(password) {
    const strengthBar = document.getElementById("password-strength");
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    strengthBar.className = "password-strength";
    switch (strength) {
      case 1:
      case 2:
        strengthBar.classList.add("strength-weak");
        break;
      case 3:
        strengthBar.classList.add("strength-medium");
        break;
      case 4:
        strengthBar.classList.add("strength-strong");
        break;
      default:
        strengthBar.classList.add("strength-weak");
    }
  }

  var input_datlog2 = document.getElementById("input_datlog2");
  var input_datlog3 = document.getElementById("input_datlog3");
  var input_datlog = document.getElementById("input_datlog");

  input_datlog2.addEventListener("keyup", function(event) {
    checkPasswordStrength(this.value);
    if (event.keyCode === 13) {
      event.preventDefault();
      grabar_clave();
    }
  });

  input_datlog3.addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      grabar_clave();
    }
  });

  input_datlog.addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      grabar_clave();
    }
  });
  </script>
</body>
</html>