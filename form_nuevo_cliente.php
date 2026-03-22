<?php
include_once "permiso.php";
include_once "config/db.php";

$sql3="SELECT CONTADORCLI AS maximo FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
$sentencia4 = $base_de_datos->prepare($sql3, [
PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
]);

$sentencia4->execute();
while ($data2 = $sentencia4->fetchObject()){
  $concli=$data2->maximo;
}
$cont_cli=$concli+1;
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>

<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css">
<script src="jquery/jquery-3.2.1.slim.min.js"></script>
<script src="jquery/popper.min.js"></script>
<script src="bootstrap2/js/bootstrap.min.js"></script>
<!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">-->
<link href="fontawesome-free-6.2.1/css/all.css" rel="stylesheet">
<script src="jquery/sweetalert.min.js"></script>
<title>
    <?php echo $_SESSION['titulo_web'];?>
  </title>
<style>

body {
  border: 0px solid black;
  padding: 0px;
  background: url('imgs/fondo2.png') no-repeat fixed center;
  background-repeat: no-repeat;
  /*background-size: 100%;*/
  background-size: cover;
  background-color:#BCBDC0;
}
/*
.swal-modal {
width: 80% !important;
}*/

.form-control {
    border-radius: 2.25rem !important;
}

.custom-select{
  border-radius:2.25rem !important; 
} 

/*Estilos a los que hace referencia el post*/

.contenedor {
    margin: 1rem auto;
    border: 0px solid #aaa;
    /*height: 450px;*/
    width:100%;
    max-width: 540px;
    overflow:auto;
    box-sizing: border-box;
    padding:0rem 0.2rem 0rem 0;
    border-radius:5px 5px 5px 5px;
    scrollbar-width: thin;
    margin-top:0px;
}

/* Estilos para motores Webkit y blink (Chrome, Safari, Opera... )*/

.contenedor::-webkit-scrollbar {
    -webkit-appearance: none;
}

.contenedor::-webkit-scrollbar:vertical {
    width:5px;
}

.contenedor::-webkit-scrollbar-button:increment,.contenedor::-webkit-scrollbar-button {
    display: none;
} 

.contenedor::-webkit-scrollbar:horizontal {
    height: 5px;
}

.contenedor::-webkit-scrollbar-thumb {
    background-color: #373737;
    border-radius: 5px;
    border: 1px solid #5D5D5D;
    box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
}

.contenedor::-webkit-scrollbar-track {
    border-radius: 10px;  
    background-color:#BEBFC1;
    
}


.container {
    padding-right: 5px !important;
    padding-left: 5px !important;
}

.btn{
  background-color:#FF5001 !important;
  color:#fff !important;
  border:none !important; 
  width:210px;
}

.titulo{
  text-align:center;color:#fff;font-size:1.2rem;
}

@media only screen and (min-width:320px) and (max-width:480px){
  .contenedor {
    height: 490px; /*altura de scroll segun altura de panatalla*/
  }
  .titulo{
    font-size:1.42rem;
  }

  .total{
    color:#FD5001;
    width:auto;
  }
}

@media only screen and (min-width:768px){
  .contenedor {
    height: 690px; /*altura de scroll segun altura de panatalla*/
  }

  .btn{
    border:1px solid #fff !important;
    background-color:#BEBFC1 !important;
    color:#000 !important;
  }

  .titulo{
    font-size:1.45rem;
  }
  
  .total{
    color:#000;
    width:540px;
  }

  .container {
    width:550px;
  }

}

table {
  border-collapse: collapse;
  width: 100%;
}

th {
  /*border: 0px solid #dddddd;
  text-align: left;
  padding: 8px 0px 8px 0px;*/
}

td{
  border: 0px solid #dddddd;
  text-align: left;
  padding: 8px 0px 8px 0px;
}

tr:nth-child(even) {
  /*background-color: #dddddd;*/
}

#testDivTable{
    width:100%;
    display:table;
    border: 0px solid #dddddd;
    margin-top:15px;
}
#testDivTable>div{
    display:table-row;
    border: 0px solid #dddddd;
}
#testDivTable>div>div{
    display:table-cell;
    vertical-align:middle;
    border: 0px solid #dddddd;
}
.input-container {
  position: relative;
}

.input-container input {
  padding-right: 30px; /* Espacio para el icono */
}

.input-container i {
  position: absolute;
  top: 50%;
  right: 10px;
  transform: translateY(-50%);
}
.form {
    position: relative
}

.form .fa-search {
    position: absolute;
    color: #fff;
}

.form span {
    position: absolute;
    right: 0px;
    top: 0px;
    padding: 2px;
    /*border-left: 1px solid #d1d5db*/
}


.form-input {
    text-indent: 10px;
    border-radius: 50px
}

.form-input:focus {
    box-shadow: none;
    border: none
}
</style>
</head>
<body>
  <header>
  </header>
  <main>
    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
        <div style="padding:10px 0px 10px 0px;border:0px solid #ccc;width:100%;">
          <div class='titulo'>
          <div style="position:absolute; top:5px; left:0; auto"><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:15px;'></i></a></div>   
                <h5 style='color:#fff;'>CREAR CLIENTE NUEVO</h5><hr/>
          </div>
        </div>
      </div> <!-- fin container -->
    </div> <!-- fin content -->

    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
        <!-- <h5 style='color:#fff;'><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:5px;'></i></a>&nbsp;&nbsp;CREAR CLIENTE NUEVO</h5><hr/><br /> -->
        <!-- <div id='layer_scroll' class='contenedor'> -->
      <form action='grabar_cliente.php' method='POST' onsubmit="return validacion();">
      <table style='color:#fff;' border="0" cellpadding='0'>
        <tr>
          <td colspan="2" style='padding-right:5px;'>Codigo<br />
              <input type="text"  style='padding-left:15px;' onkeyup="document.getElementById('ruc').value=this.value" class="form-control" id='codigo' name='codigo' value="<?php echo $cont_cli;?>" placeholder="" maxlength="25">
              <input type="hidden" class="form-control" name='codigo_true' value="<?php echo $cont_cli;?>" placeholder="" ></td>
              <input type="hidden" id='fact_elect' name='fact_elect'></td>
              <td colspan="2" style='padding-left:5px;'>R.U.C.<br />
              
              <div class="row height d-flex justify-content-center align-items-center">
                <div class="col-md-12">
                    <div class="form">
                    <input type="text" class="form-control form-input" id='ruc' name='ruc' value="<?php echo $cont_cli;?>" placeholder="" maxlength="25" >
                      <a id="buscarRuc"><span class="fa-stack fa-lg">
                        <i class="fa fa-circle fa-stack-2x" style='color:#0CB3F9;font-size: 30px;top: 4px;"'></i>
                        <i class="fa fa-search fa-stack-1x fa-inverse" style='font-size:15px;top: 0px;'></i></span>
                      </a>
                    </div>
                </div>
              </div>
            </td>
        </tr>
        <tr>
          <td colspan="2" style='padding-right:5px;'>Vendedor<br />
          <select class="browser-default custom-select" id="vendedor" name='vendedor'>
              <?php
                    if(!isset($_SESSION['codvendedor_opt'])){
                      if(!isset($_SESSION['codvendedor'])){  
                        $sql3="SELECT CODVEN, NOMBRE FROM BASEVENDEDORES";
                        $opt="<option value='' disabled selected hidden>Vendedor</option>";
                      }else{
                        $sql3="SELECT CODVEN, NOMBRE FROM BASEVENDEDORES WHERE CODVEN='".$_SESSION['codvendedor']."'";
                        $opt="";
                      }
                    }else{
                      $sql3="SELECT CODVEN, NOMBRE FROM BASEVENDEDORES";
                      //$opt="<option value='' disabled selected hidden>Vendedor</option>";
                    } 
                    
                    /*recuperando todo los productos comandados*/
                    
                    //echo $sql3;

                    $sentencia4 = $base_de_datos->prepare($sql3, [
                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                    ]);

                    $sentencia4->execute();
                    echo $opt;
                    while ($data2 = $sentencia4->fetchObject()){
                      //$pormaxdespar=$data2->PORMAXDESPAR;
                      if(($_SESSION['codvendedor_opt']==$data2->CODVEN) OR ($_SESSION['codvendedor']==$data2->CODVEN)){
                        echo "<option value='".$data2->CODVEN."' selected>".$data2->CODVEN."-".$data2->NOMBRE."</option>";
                      }else{
                        echo "<option value='".$data2->CODVEN."'>".$data2->CODVEN."-".$data2->NOMBRE."</option>";
                      }
                      
                    }
                    
                    ?>
              </select>
            </td>
          <td colspan="2"style='padding-left:5px;'>DV<br />
          <input type="text" class="form-control" name='dv' placeholder="" maxlength="25"  oninput="validarInput(this)"></td>
        </tr>
        <tr>
          <td colspan="4">
            <div id="contenedor" style='overflow:hidden;border:1px solid #fff;border-radius: 20px 20px 20px 20px;'>
            <div style='float: left;border:0px solid #000; width:100%;height:30px;font-size:13px;'>
                <div style='float:left;border:0px solid #000;width:15%;height:30px;text-align:center;background-color:#fff;color:#000;border-radius: 20px 0px 0px 20px;padding:5px 0px 0px 0px;'>
                Tipo:
                </div>
                <div style='float:left;border:0px solid #000;border-left:2px solid #fff;padding-left:5px;width:85%;height:30px;text-align:left;'>
                <?php
                $p=0;
                $myarr2 = array("Natural", "Jurídico", "Pasaporte");
                foreach($myarr2 as $key2 => $valor2){
                    if($p==1){
                      echo "$valor2&nbsp; <input class='form-check-input' onClick='caja_nom_apell(this.value);' type='radio' name='tipocom' id='flexRadioDefault$p' value='$key2' checked />&nbsp;&nbsp;&nbsp;";
                    }else{
                      echo "$valor2&nbsp; <input class='form-check-input' onClick='caja_nom_apell(this.value);' type='radio' name='tipocom' id='flexRadioDefault$p' value='$key2' />&nbsp;&nbsp;&nbsp;";
                    }
                    $p++;
                }
                ?>
            </div>
            </div>
            </div>
            <?php
            //$p=0;
            //$myarr2 = array("Jurídico", "Natural", "Pasaporte");

                  ?>
                    <div id="layer_nom_apell" style="display:none;">
                      <div id='testDivTable'>       
                          <div>                   
                              <div style='width:47.5%;padding-right:5px;'>              
                                  <input class="form-control" style="text-transform: uppercase;" id='nom' name='nombre_natural' type='text' placeholder='Nombre' maxlength="25"o ninput="validarInput(this)"/>
                              </div>
                              <div style='width:58%;padding-left:5px;'>
                                  <input class="form-control" style="text-transform: uppercase;" id='nom2' name='nombre2'type='text' placeholder='' maxlength="25" oninput="validarInput(this)"/>
                              </div>
                          </div>
                      </div>
                    
                      <div id='testDivTable'>       
                          <div>                   
                              <div style='width:47.5%;padding-right:5px;'>              
                                <input class="form-control" style="text-transform: uppercase;" id='apell' name='apellido' type='text' placeholder='Apellido' maxlength="25" oninput="validarInput(this)" />
                              </div>
                              <div style='width:58%;padding-left:5px;'>
                                  <input class="form-control" style="text-transform: uppercase;" id='apell2' name='apellido2' type='text' placeholder='' maxlength="25" oninput="validarInput(this)"/>
                              </div>
                          </div>
                      </div>
                    </div>

                    <div id="layer_nom_apell2" style="display:block;margin-top:15px;">
                      Nombre<br />
                      <input class="form-control" style="text-transform: uppercase;" id='nombre' name='nombre' type='text' placeholder='' maxlength="100" oninput="validarInput(this)"/>
                    </div>
          </td>
        </tr>
        
        <tr>
          <td colspan="4">Dirección<br />
              <input type="text" style="text-transform: uppercase;" class="form-control" id='direccion' name='direccion' placeholder="" maxlength="60" oninput="validarInput(this)"></td>
        </tr>  
        <tr>
          <td colspan="4">
            Tipo de Cliente:
          </td>
        </tr>
        <tr>
            
            
            <?php
            $p=0;
            //$content_radio="";
            $myarr = array('Contribuyente', 'Consumidor Final', 'Gobierno','Exento', 'Regimen Especial', 'Contribuyente exento', 'Consumidor Final exento', 'Gobierno exento', 'Otros', 'Otros exentos');
            ?>
                       
            <?php
            $q=1;
            foreach($myarr as $key => $valor){
              if($p==0){
                echo "<td><input class='form-check-input' type='radio' name='tipocli' id='flexRadioDefault$p' value='$valor' checked /></td><td>$valor</td>";
              }else{
                echo "<td><input class='form-check-input' type='radio' name='tipocli' id='flexRadioDefault$p' value='$valor' /></td><td>$valor</td>";
              }

              if($q==2){
                echo "</tr><tr>";
                $q=0;
              }
              $p++;
              $q++;
            }
            //echo "$content_radio";
            ?>
            <td></td><td></td>
        </tr>  
        <tr>
        
        <td colspan="2" style='padding-right:5px;'>Desc. Global<br />
        <?php 
          /*if($_SESSION['desctoglo']==1){
            $habilitar="";
          }else{
            $habilitar="disabled";
          }*/
        ?>
          <input type="text" class="form-control" name='descglobal' placeholder="" ></td> 
        <td colspan="2" style='padding-left:5px;'>Contácto<br />
        <input type="text" class="form-control" name='contact' placeholder="" maxlength="40">
        </td>
        </tr>  
        <tr>
          
          <td colspan="2" style='padding-right:5px;'>Celular<br />
            <input type="number" class="form-control" name='celular' placeholder="" maxlength="40">
          </td>
          <td colspan="2" style='padding-left:5px;'>Telefono<br />
            <input type="number" class="form-control" name='tel' placeholder="" maxlength="40">
              </td>
        </tr>
        <tr>
          <td colspan="2" style='padding-right:5px;'>E-mail<br />
          <input type="email" class="form-control" name='email' placeholder="" maxlength="100">
              </td>
          <td colspan="2" style='padding-left:5px;'>Provincia<br />
          <select class="browser-default custom-select" id="provincia" name='provincia' onChange="cargar_distrito(provincia.value);">
              <?php
              /*recuperando todo los productos comandados*/
              $sql3="SELECT * FROM BASEPROVINCIA";
              //echo $sql3;

              $sentencia4 = $base_de_datos->prepare($sql3, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
              ]);

              $sentencia4->execute();
              //echo "<option selected></option>";
              echo "<option value='' disabled selected hidden></option>";
              while ($data2 = $sentencia4->fetchObject()){
                //$pormaxdespar=$data2->PORMAXDESPAR;
                echo "<option value='".$data2->NOMBREEGEO1."'>".$data2->DESNOMBREEGEO1."</option>";
              }
              ?>
            </select>
              </td>
          
        </tr>
        <tr>
            <td colspan="2" style='padding-right:5px;'>
            <div id='layer_distrito'>
            <select class="browser-default custom-select" id="distrito" name='distrito' onChange="cargar_corregimiento(distrito.value);">
              <option selected></option>
            </select>
            </div>
            </td>
          <td  colspan="2" style='padding-left:5px;'>
          <div id='layer_corregimiento' id='corregimiento' name='corregimiento' >
            <select class="browser-default custom-select">
              <option selected></option>
            </select>
            </div>
          </td>
          
        </tr>
        <tr>
          <td colspan="2" style='padding-right:5px;'>
            <div id='layer_retencion'>
              
            Retención? &nbsp;<input type="checkbox" name="retencion" id="retencion"> </div>
          </td>
          <td colspan="2" style='padding-left:5px;'>
          <div id="layer_porcentaje">
            Porcentaje
            <input type="text" class="form-control" name='porc_retencion'  id = "porc_retencion" value="" placeholder="" aria-label="cliente" aria-describedby="basic-addon1"></div>
          </td>          
        </tr>
        <tr>
          <td  colspan="2" style='padding-right:5px;'>
            
          </td>
          <td  colspan="2" style='padding-left:5px;'>
            
          </td>
        </tr>
        <!-- <tr>
          <th colspan="2">
            <br />
            <button type="button" class="btn btn-default" onClick="window.history.back();"><i class="fa fa-arrow-left" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Volver</button>
            <button type="submit" name='transaccion' class="btn btn-primary" value='guardar'><i class="fa fa-check-circle" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Guardar</button>
          </th>
        </tr> -->
        <tr>
          <td  colspan="2" style='padding-right:5px;'>

            <!-- <button type="button" class="btn btn-default" onClick="window.history.back();"><i class="fa fa-arrow-left" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Volver</button> -->
            <!-- <button type="submit" name='transaccion' class="btn btn-primary" value='actualizar'>Actualizar</button> -->
            
            <div class="content" style='border:0px solid #ccc;text-align:center;'>
            
            <button type="button" class="btn" onClick="window.history.back();" style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important; width:100%;'><i class="fa fa-chevron-left" aria-hidden="true" style='color:#000;'></i>&nbsp;&nbsp;&nbsp;<strong>Volver</strong></button>
            </div>
          </td>
          <td colspan="2" style='padding-left:5px;'>
            <!-- <button type="button" class="btn btn-default" onClick="window.history.back();"><i class="fa fa-arrow-left" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Volver</button> -->
            <!-- <button type="submit" name='transaccion' class="btn btn-primary" value='actualizar'>Actualizar</button> -->
            
            <div class="content" style='border:0px solid #ccc;text-align:center;'>
            
            <button type="submit" name='transaccion' class="btn" style='border:none !important; width:100%;' value='guardar'><strong>Guardar</strong></button>
            </div>
          </td>
        </tr>
      </table>
      </form>
            <!--</div>fin scroll-->
    <br /><br />
            </div>
            </div>
    <!-- loading 
    <div id="loading" style="z-index: 10000; position: fixed; top:0; left:0; background-color: rgba(0,0,0,.7); width: 100vw; height: 100vh;">
			<div style="display: inline-block; position: absolute; top: 50%; left: 50%; margin: -50px 0 0 -50px; transform: translateXY(-50%,-50%);">
				<span class="fas fa-spin fa-spinner fa-5x" style="color:#ff5001"></span>
			</div>
		</div>-->
    <?php include("recursos/loading.php");?>
  </main>
  <script type="text/javascript">
    function validarInput(input) {
        var regex = /[^a-zA-Z\s-_áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ0-9]/; // Esta expresión regular busca caracteres no permitidos
        if (regex.test(input.value)) {
            var caracterNoPermitido = input.value.match(regex)[0];
            alert('El campo ' + input.name + ' no permite el caracter especial: ' + caracterNoPermitido);
            input.value = input.value.replace(regex, ''); // Elimina el caracter especial del valor del input
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
      var checkbox = document.getElementById('retencion');
      var layerPorcentaje = document.getElementById('layer_porcentaje');

      // Función que verifica el estado del checkbox y muestra/oculta el div
      function toggleLayerPorcentaje() {
          if (checkbox.checked) {
              layerPorcentaje.style.display = 'block';
          } else {
              layerPorcentaje.style.display = 'none';
          }
      }

      // Llamada inicial para asegurarse de que el div tenga el estado correcto al cargar la página
      toggleLayerPorcentaje();

      // Agregar el listener al checkbox
      checkbox.addEventListener('change', toggleLayerPorcentaje);
    });
		jQuery(document).ready(function($) {
			$("#loading").hide();
      var verificarFactElect = function () {
        fetch('ajax/obtenerConfigFacElec.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            var campos = data.data;

            // Llenar los campos con los valores obtenidos
            var fac_elec = campos.FACELECT === "false" ? false : true;
            if ( !fac_elec) {
              document.getElementById('buscarRuc').style.display = 'none';
            }
            document.getElementById('fact_elect').value = fac_elec;
          } else {
            swal('¡Error!', 'Error al obtener los campos. Por favor, inténtalo de nuevo.', 'error');
          }
        })
        .catch(error => {
          swal('¡Error!', 'Error en la solicitud fetch(). Por favor, inténtalo de nuevo.', 'error');
        });


      };
      verificarFactElect();
		});
	</script>
  <script>
    function cargar_distrito(txt_provincia){
      //txt_provincia=document.getElementById("provincia");
      //txt_distrito=document.getElementById("distrito");
      //alert(txt_provincia);
      layer_dist=document.getElementById("layer_distrito");
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
              //alert(this.responseText);
              layer_dist.innerHTML=this.responseText;
              
              //$("#Modal_Comensales").modal('hide');
          }
      };
      xhttp.open("GET", "data_dist_corre.php?txt_provincia="+txt_provincia+"&txt_distrito=0&tipo=1", true);
      xhttp.send();
    }

    function cargar_corregimiento(txt_distrito){
      //alert(txt_distrito);
      txt_provincia=document.getElementById("provincia").value;
      layer_corre=document.getElementById("layer_corregimiento");
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
              //alert(this.responseText);
              layer_corre.innerHTML=this.responseText;
              
              //$("#Modal_Comensales").modal('hide');
          }
      };
      xhttp.open("GET", "data_dist_corre.php?txt_provincia="+txt_provincia+"&txt_distrito="+txt_distrito+"&tipo=2", true);
      xhttp.send();
    }

    function caja_nom_apell(tipo){
      //alert(tipo);
      if(tipo==0){
        document.getElementById("layer_nom_apell").style.display="block";
        document.getElementById("layer_nom_apell2").style.display="none";
      }else{
        document.getElementById("layer_nom_apell").style.display="none";
        document.getElementById("layer_nom_apell2").style.display="block";
      }
    }

    function validacion(){
      
      if( document.getElementById('fact_elect').value === true )
        validateRuc();
      //alert();
      if (document.getElementById("codigo").value=='') {
        // Si no se cumple la condicion...
        //alert('[ERROR] El campo CODIGO debe tener un valor');
        swal("Alerta!", "El campo CODIGO debe tener un valor!", {buttons:false,});
        /*swal({
          title: "Error!",
          text: "El campo CODIGO debe tener un valor!",
          icon: "error",
          buttons: false,
        });*/
        return false;
      }else if (document.getElementById("direccion").value=='') {
        // Si no se cumple la condicion...
        swal("Alerta!", "El campo DIRECCION debe tener un valor!", {buttons:false,});
        /*swal({
          title: "Error!",
          text: "El campo DIRECCION debe tener un valor!",
          icon: "error",
          buttons: false,
        });*/

        //alert('[ERROR] El campo DIRECCION debe tener un valor');
        return false;
      }
      else{
        if (document.getElementById("flexRadioDefault0").checked) {
          if (document.getElementById("nom").value=='') {
            // Si no se cumple la condicion...
            //alert('[ERROR] El campo NOMBRE o APELLIDO debe tener un valor');
            swal("Alerta!", "El campo NOMBRE o APELLIDO debe tener un valor!", {buttons:false,});
            /*swal({
              title: "Error!",
              text: "El campo NOMBRE o APELLIDO debe tener un valor!",
              icon: "error",
              buttons: false,
            });*/
            return false;
          }else if (document.getElementById("apell").value=='') {
            // Si no se cumple la condicion...
            //alert('[ERROR] El campo NOMBRE o APELLIDO debe tener un valor');
            swal("Alerta!", "El campo NOMBRE o APELLIDO debe tener un valor!", {buttons:false,});
            /*swal({
              title: "Error!",
              text: "El campo NOMBRE o APELLIDO debe tener un valor!",
              icon: "error",
              buttons: false,
            });*/
            return false;
          }
        }else{
          if (document.getElementById("nombre").value=='') {
            // Si no se cumple la condicion...
            //alert('[ERROR] El campo NOMBRE debe tener un valor');
            swal("Alerta!", "El campo NOMBRE debe tener un valor!", {buttons:false,});
            /*swal({
              title: "Error!",
              text: "El campo NOMBRE debe tener un valor!",
              icon: "error",
              buttons: false,
            });*/
            return false;
          }
        }
      }

      return true;
    }

    var buscarRuc = document.getElementById("buscarRuc");
    var ruc = document.getElementById("ruc");
    // Obtener el radio button seleccionado

    buscarRuc.addEventListener("click", function(event) {
      event.prevenDefault;
      validateRuc();
    });

    function validateRuc() {
      var rucValue = ruc.value;
      // Obtener el valor del radio button seleccionado
      var tipocom = document.querySelector('input[name="tipocom"]:checked');
      var tipoRuc = tipocom.value;
      switch (tipocom.value) {
        case "0":
          tipoRuc = 1;
          break;
        case "1":
          tipoRuc = 2;
          break;
      
        default:
          tipoRuc = 2;
          break;
      }


      if (rucValue.length > 8) {
        // Realizar la llamada al API
        // Aquí puedes utilizar la función fetch() o cualquier otra librería para hacer la petición AJAX
        var datos = new FormData();
        datos.append("ruc", rucValue);
        datos.append("tipoRuc", tipoRuc);

        swal({
          title: 'Consultando',
          text: 'Espere un momento...',
          buttons: false, // Desactiva los botones del modal
          closeOnClickOutside: false,
          content: {
            element: 'i',
            attributes: {
              class: 'fa fa-cog fa-spin fa-3x fa-fw',
              style: 'font-size: 40px; color: #000;'
            }
          },
          onOpen: () => {
            swal.showLoading(); // Mostrar el indicador de carga
          }
        });

        fetch('fel/thfkapanama/consultaClienteRucDv.php', {
          method: 'POST',
          body: datos
        })
        .then(function(response) {
          return response.json();
        })
        .then(function(data) {

          swal.close();
          // Procesar la respuesta del API
          if (data.ConsultaRuc && data.ConsultaRuc.mensaje) {

              document.getElementsByName('dv')[0].value = data.ConsultaRuc.infoRuc.dv;
              console.log( data.ConsultaRuc.infoRuc.razonSocial);
            if(tipoRuc == 1){
              document.getElementById('nombre_natural').value = data.ConsultaRuc.infoRuc.razonSocial;
            }            
            if(tipoRuc == 2){
              document.getElementById('nombre').value = data.ConsultaRuc.infoRuc.razonSocial;
            }
              console.log( data.ConsultaRuc.infoRuc.dv);
            showResult(data.estado, data.ConsultaRuc.mensaje);
          } else {
            showResult(false, "La respuesta del API no contiene la información esperada.");
          
          }
        })
        .catch(function(error) {
          console.log(error);
          // Manejar errores de conexión o del API
          showResult(false, "Ocurrió un error al consultar el API.");

        });
      }
    }

    function showResult(success, message) {
      swal({
        title: success ? "Éxito" : "Error",
        text: message,
        icon: success ? "success" : "error"
      });
    }
  </script>
</body>
</html> 
