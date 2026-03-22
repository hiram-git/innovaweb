<?php
include_once "permiso.php";
include_once "config/db.php";
$codcliente=str_replace("%23", "#", $_GET['id']);
$nomcliente=str_replace("%23", "#", $_GET['nom_cliente']);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>

<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css">
<script src="jquery/jquery-3.2.1.slim.min.js" ></script>
<script src="jquery/popper.min.js" ></script>
<script src="bootstrap2/js/bootstrap.min.js" ></script>
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
    width:550px !important;
  }

}

table {
  border-collapse: collapse;
  width: 100%;
}

th {
  border: 0px solid #dddddd;
  text-align: left;
  padding: 8px 0px 8px 0px;
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
                <h5 style='color:#fff;'>EDITAR CLIENTE</h5><hr/>
          </div>
        </div>
      </div> <!-- fin container -->
    </div> <!-- fin content -->
    <!-- <h5 style='color:#fff;'><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:10px;'></i></a>&nbsp;&nbsp;EDITAR CLIENTE</h5><hr/><br /> -->
      <?php
      /*recuperando todo los productos comandados*/
      
      $codcliente              = str_replace("'", "''", $codcliente);
      $sql3="SELECT
          a.TIPREG,
          a.NOMBRE,
          a.NOMBRE1,
          a.NOMBRE2,
          a.APELLIDO1,
          a.APELLIDO2,
          a.CODIGO,
          a.RIF,
          a.DIRECC1,
          a.CODVEN,
          a.NIT,
          a.TIPOCLI,
          a.NUMTEL,
          a.DIRCORREO,
          a.NUMTELCONTACTO,
          a.NUMERO_MOVIL,
          a.PORMAXDESPAR,
          a.PORMAXDESGLO,
          a.NOMBREEGEO1,
          a.NOMBREEGEO2,
          a.NOMBREEGEO3,
          a.TIPOCOMERCIO,
          a.NOMBREGERENTE, 
          a.CONESPECIAL,
          a.PORRETIMP
        FROM
          BASECLIENTESPROVEEDORES  as a 
        WHERE a.CODIGO='$codcliente' AND (a.TIPREG = 1)";
      //echo $sql3;

      $sentencia4 = $base_de_datos->prepare($sql3, [
      PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
      ]);

      $sentencia4->execute();
      while ($data2 = $sentencia4->fetchObject()){
        $idcod=$data2->CODIGO;
        $retieneitbms=$data2->CONESPECIAL;
        $retencionbase=$data2->PORRETIMP;
        ?>
      <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
      <form action='grabar_cliente.php' method='POST' onsubmit="return validacion();">
      <table style='color:#fff;' border='0'>
        <tr>
          <td colspan="2" style='padding-right:5px;'>Codigo<br />
              <input type="text" class="form-control" id='codigo' name='codigo' value="<?php echo $data2->CODIGO;?>" placeholder="" aria-label="cliente" aria-describedby="basic-addon1" readonly='true'></td>
          <td colspan="2" style='padding-left:5px;'>R.U.C.<br />
              <input type="text" class="form-control" id='ruc' name='ruc' value="<?php echo $data2->RIF;?>" placeholder="" aria-label="ruc" aria-describedby="basic-addon1"></td>
        </tr>
        <tr>
          <td colspan="2" style='padding-right:5px;'>
          Vendedor<br />
            <select class="browser-default custom-select" id="vendedor" name='vendedor'>
              <?php
              /*recuperando todo los productos comandados*/
              $sql="SELECT * FROM BASEVENDEDORES";
              //echo $sql3;

              $sentencia = $base_de_datos->prepare($sql, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
              ]);

              $sentencia->execute();
              echo "<option selected></option>";
              
              while ($data = $sentencia->fetchObject()){
                //$pormaxdespar=$data2->PORMAXDESPAR;
                if($data->CODVEN==$data2->CODVEN){
                    echo "<option value='".$data->CODVEN."' selected>".$data->CODVEN." | ".$data->NOMBRE."</option>";
                }else{
                    echo "<option value='".$data->CODVEN."'>".$data->CODVEN." | ".$data->NOMBRE."</option>";
                }
                
              }
              ?>
            </select>
          <!-- <input type="text" class="form-control" name='nombre' value="<?php echo $data2->NOMBRE;?>" placeholder="" aria-label="nombre" aria-describedby="basic-addon1"></td> -->
          <td colspan="2" style='padding-left:5px;'>DV<br />
          <input type="text" class="form-control" name='dv' value="<?php echo $data2->NIT;?>" placeholder="" aria-label="dv" aria-describedby="basic-addon1"></td>
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
            //echo $data2->TIPOCOMERCIO."jhjshdkjasd";
            $myarr2 = array("Natural", "Jurídico", "Pasaporte");
            foreach($myarr2 as $key2 => $valor2){
                if($key2==$data2->TIPOCOMERCIO) {
                    echo "$valor2&nbsp; <input class='form-check-input' onClick='caja_nom_apell(this.value);' type='radio' name='tipocom' id='flexRadioDefault$p' value='$key2' checked />&nbsp;&nbsp;&nbsp;";
                }
                else{
                    echo "$valor2&nbsp; <input class='form-check-input' onClick='caja_nom_apell(this.value);' type='radio' name='tipocom' id='flexRadioDefault$p' value='$key2' />&nbsp;&nbsp;&nbsp;";
                }
                $p++;
            }
            ?>
            </div>
          </div>
          </div>

          <?php 
          /*if($data2->NOMBRE!=''){
            if($data2->TIPOCOMERCIO==0){
              $mystring = $data2->NOMBRE;
              $findme   = ',';
              $pos = strpos($mystring, $findme);
              if ($pos === false) {
                //echo "La cadena '$findme' no fue encontrada en la cadena '$mystring'";
                $nom_='';
                $nom2_='';
                $apell_='';
                $apell2_='';
              } else {
                  //echo "La cadena '$findme' fue encontrada en la cadena '$mystring'";
                  //echo " y existe en la posición $pos";
                  //$mystring= str_replace(",", "", $mystring);
                  $exp2=explode(",",$data2->NOMBRE);
                  $exp3=explode(" ", trim($exp2[0]));//partiendo los nombres
                  $exp4=explode(" ", trim($exp2[1]));//partiendo los apellidos

                  if(count($exp3)==1){
                    $nom_=$exp3[0];
                    $nom2_='';
                  }else{
                    $nom_=$exp3[0];
                    $nom2_=$exp3[1];
                  }
                  //var_dump($exp2);
                  if(count($exp4)==1){
                    $apell_=$exp4[0];
                    $apell2_='';
                  }else{
                    $apell_=$exp4[0];
                    $apell2_=$exp4[1];
                  }
              }
              $nom_=$data2->NOMBRE;
              $nom1=$data2->NOMBRE1;
              $nom2_=$data2->NOMBRE2;
              $apell_=$data2->APELLIDO1;
              $apell2_=$data2->APELLIDO2;
            }else{
              $nom_='';
              $nom2_='';
              $apell_='';
              $apell2_='';
            }
          }*/
          
          $nom_=$data2->NOMBRE;
          $nom1=$data2->NOMBRE1;
          $nom2_=$data2->NOMBRE2;
          $apell_=$data2->APELLIDO1;
          $apell2_=$data2->APELLIDO2;

          if($data2->TIPOCOMERCIO==0){
            $juridico='none';
            $natural='block';
          }else{
            $juridico='block';
            $natural='none';
          }
          ?>
          <div id="layer_nom_apell" style="display:<?php echo $natural;?>;">
                    <div id='testDivTable'>       
                        <div>                   
                            <div style='width:47.5%;padding-right:5px;'>              
                                <input class="form-control" id='nom' name='nombre_natural' value="<?php echo $nom1;?>" type='text' placeholder='Nombre' maxlength="25"/>
                            </div>
                            <div style='width:58%;padding-left:5px;'>
                                <input class="form-control" id='nom2' name='nombre2' value="<?php echo $nom2_;?>" type='text' placeholder='' maxlength="25"/>
                            </div>
                        </div>
                    </div>
                  
                    <div id='testDivTable'>       
                        <div>                   
                            <div style='width:47.5%;padding-right:5px;'>              
                              <input class="form-control" id='apell' name='apellido' value="<?php echo $apell_;?>" type='text' placeholder='Apellido' maxlength="25"/>
                            </div>
                            <div style='width:58%;padding-left:5px;'>
                                <input class="form-control" id='apell2' name='apellido2' value="<?php echo $apell2_;?>" type='text' placeholder='' maxlength="25"/>
                            </div>
                        </div>
                    </div>
                  </div>

                  <div id="layer_nom_apell2" style="display:<?php echo $juridico;?>;margin-top:15px;">
                    Nombre<br />
                    <input class="form-control" id='nombre' name='nombre' type='text' placeholder='' value="<?php echo $data2->NOMBRE;?>" maxlength="100"/>
                  </div>
          </td>
        </tr>
        
        <tr>
          <td colspan="4">Direccion<br />
              <input type="text" class="form-control" style="text-transform: uppercase;" id='direccion' name='direccion' value="<?php echo $data2->DIRECC1;?>" placeholder="" maxlength="60"></td>
        </tr>  
        <tr>
            <?php
            $p=0;
            $q=1;
            //$myarr = array('Contribuyente', 'Consumidor Final', 'Exento', 'Gubernamental', 'Regimen Especial');
            $myarr = array('Contribuyente', 'Consumidor Final', 'Gubernamental','Exento', 'Regimen Especial', 'Contribuyente exento', 'Consumidor Final exento', 'Gubernamental exento', 'Otros', 'Otros exentos');
            foreach($myarr as $key => $valor){
                if (strtoupper($valor)==strtoupper($data2->TIPOCLI) || strtoupper("Gobierno")==strtoupper($data2->TIPOCLI)) {
                  if(strtoupper("Gobierno")==strtoupper($data2->TIPOCLI))
                    $valor = strtoupper("Gubernamental");
                    $p++;
                    echo "<td><input class='form-check-input' type='radio' name='tipocli' id='flexRadioDefault$p' value='$valor' checked /></td><td>$valor</td>";
                }else{
                    echo "<td><input class='form-check-input' type='radio' name='tipocli' id='flexRadioDefault$p' value='$valor' /></td><td>$valor</td>";
                }

                if($q==2){
                  echo "</tr><tr>";
                  $q=0;
                }
                $q++;
            }

            
            ?>
            <td></td><td></td>
        </tr>  
        <tr>
        <td colspan="2" style='padding-right:5px;'>Desc. Global<br />
        <input type="text" class="form-control" name='descglobal' value="<?php echo $data2->PORMAXDESGLO;?>" placeholder="" aria-label="ruc" aria-describedby="basic-addon1">
        <input type="hidden" class="form-control" name='pormaxdespar' value="<?php echo $data2->PORMAXDESPAR;?>" placeholder="" aria-label="ruc" aria-describedby="basic-addon1">
              </td>
        <td colspan="2" style='padding-left:5px;'>Contacto<br />
              <input type="text" class="form-control" name='contact' value="<?php echo $data2->NOMBREGERENTE;?>" placeholder="" aria-label="cliente" aria-describedby="basic-addon1">
              </td> 
        </tr>  
        
        <tr>
          
          <td colspan="2" style='padding-right:5px;'>Celular<br />
              <input type="number" class="form-control" name='celular' value="<?php echo $data2->NUMTELCONTACTO;?>" placeholder="" aria-label="ruc" aria-describedby="basic-addon1"></td>
          <td colspan="2" style='padding-left:5px;'>Telefono<br />
              <input type="number" class="form-control" name='tel' value="<?php echo $data2->NUMTEL;?>" placeholder="" aria-label="ruc" aria-describedby="basic-addon1"></td>
        </tr>
        <tr>
          <td colspan="2" style='padding-right:5px;'>E-mail<br />
              <input type="email" class="form-control" name='email' value="<?php echo $data2->DIRCORREO;?>" placeholder="" aria-label="cliente" aria-describedby="basic-addon1">
            </td>
          <td colspan="2" style='padding-left:5px;'>
          Provincia<br />
            <select class="browser-default custom-select" id="provincia" name='provincia' onChange="cargar_distrito(provincia.value);">
              <?php
              /*recuperando todo los productos comandados*/
              $sql="SELECT * FROM BASEPROVINCIA";
              //echo $sql3;

              $sentencia = $base_de_datos->prepare($sql, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
              ]);

              $sentencia->execute();
              echo "<option selected></option>";
              while ($data = $sentencia->fetchObject()){
                //$pormaxdespar=$data2->PORMAXDESPAR;
                if($data->NOMBREEGEO1==$data2->NOMBREEGEO1){
                    echo "<option value='".$data->NOMBREEGEO1."' selected>".$data->DESNOMBREEGEO1."</option>";
                }else{
                    echo "<option value='".$data->NOMBREEGEO1."'>".$data->DESNOMBREEGEO1."</option>";
                }
                
              }
              ?>
            </select>
        </td>
          
        </tr>
        <tr>
            <td colspan="2" style='padding-right:5px;'>
            Distrito<br />
          <div id='layer_distrito'>
            <select class="browser-default custom-select" id="distrito" name='distrito' onChange="cargar_corregimiento(distrito.value);">
                <?php
                /*recuperando todo los productos comandados*/
                $sql="SELECT * FROM BASEDISTRITO WHERE NOMBREEGEO1='".$data2->NOMBREEGEO1."'";
                //echo $sql3;

                $sentencia = $base_de_datos->prepare($sql, [
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

                $sentencia->execute();
                echo "<option selected></option>";
                while ($data = $sentencia->fetchObject()){
                    //$pormaxdespar=$data2->PORMAXDESPAR;
                    if($data->NOMBREEGEO2==$data2->NOMBREEGEO2){
                        echo "<option value='".$data->NOMBREEGEO2."' selected>".$data->DESNOMBREEGEO2."</option>";
                    }else{
                        echo "<option value='".$data->NOMBREEGEO2."'>".$data->DESNOMBREEGEO2."</option>";
                    }
                    
                }
                ?>
            </select>
            </div>
            </td>
          <td colspan="2" style='padding-left:5px;'>
          Corregimiento<br />
                <div id='layer_corregimiento'>
                <select class="browser-default custom-select" name='corregimiento' id='corregimiento'>
                    <?php
                    /*recuperando todo los productos comandados*/
                    $sql="SELECT * FROM BASECORREGIMIENTO WHERE NOMBREEGEO1='".$data2->NOMBREEGEO1."' AND NOMBREEGEO2='".$data2->NOMBREEGEO2."'";
                    //echo $sql3;

                    $sentencia = $base_de_datos->prepare($sql, [
                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                    ]);

                    $sentencia->execute();
                    echo "<option selected></option>";
                    while ($data = $sentencia->fetchObject()){
                        //$pormaxdespar=$data2->PORMAXDESPAR;
                        if($data->NOMBREEGEO3==$data2->NOMBREEGEO3){
                            echo "<option value='".$data->NOMBREEGEO3."' selected>".$data->DESNOMBREEGEO3."</option>";
                        }else{
                            echo "<option value='".$data->NOMBREEGEO3."'>".$data->DESNOMBREEGEO3."</option>";
                        }
                        
                    }
                    ?>
                </select>
                </div>
          </td>
          
        </tr>
        <tr>
          <td colspan="2" style='padding-right:5px;'>
            <div id='layer_retencion'>
              <?php $chequed = (bool)$retieneitbms ? "checked='checked'" : ""; ?>
            Retención? &nbsp;<input type="checkbox" name="retencion" id="retencion" <?= $chequed ?>> </div>
          </td>
          <td colspan="2" style='padding-left:5px;'>
          <div id="layer_porcentaje">
            Porcentaje
            <input type="text" class="form-control" name='porc_retencion'  id = "porc_retencion" value="<?php echo (float)$retencionbase ?>" placeholder="" aria-label="cliente" aria-describedby="basic-addon1"></div>
          </td>          
        </tr>

        <tr>
          <td colspan="2" style='padding-right:5px;'>
            <br />
            <!-- <button type="button" class="btn btn-default" onClick="window.history.back();"><i class="fa fa-arrow-left" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Volver</button> -->
            <!-- <button type="submit" name='transaccion' class="btn btn-primary" value='actualizar'>Actualizar</button> -->
            
            <div class="content" style='border:0px solid #ccc;text-align:center;'>
            
            <button type="button" class="btn" onClick="location.href='clientes.php?input_buscar='" style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important; width:100%;'><i class="fa fa-chevron-left" aria-hidden="true" style='color:#000;'></i>&nbsp;&nbsp;&nbsp;<strong>Volver</strong></button>
            </div>
          </td>
          <td colspan="2" style='padding-left:5px;'>
            <br />
            <!-- <button type="button" class="btn btn-default" onClick="window.history.back();"><i class="fa fa-arrow-left" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Volver</button> -->
            <!-- <button type="submit" name='transaccion' class="btn btn-primary" value='actualizar'>Actualizar</button> -->
            
            <div class="content" style='border:0px solid #ccc;text-align:center;'>
            
            <button type="submit" name='transaccion' class="btn" style='border:none !important; width:100%;' value='actualizar'><strong>Actualizar</strong></button>
            </div>
          </td>
        </tr>
      </table>
      </form>
      </div>
      </div>
      <?php
      }
      ?>

    <br /><br />
    <!-- loading 
    <div id="loading" style="z-index: 10000; position: fixed; top:0; left:0; background-color: rgba(0,0,0,.7); width: 100vw; height: 100vh;">
			<div style="display: inline-block; position: absolute; top: 50%; left: 50%; margin: -50px 0 0 -50px; transform: translateXY(-50%,-50%);">
				<span class="fas fa-spin fa-spinner fa-5x" style="color:#ff5001"></span>
			</div>
		</div>-->
    <?php include("recursos/loading.php");?>
  </main>
  <script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#loading").hide();
		});
	</script>
  <script>
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
      //alert();
      if (document.getElementById("vendedor").value=='') {
        // Si no se cumple la condicion...
        //alert('[ERROR] El campo CODIGO debe tener un valor');
        swal("Alerta!", "El campo VENDEDOR ASIGNADO debe tener un valor!", {buttons:false,});
        /*swal({
          title: "!",
          text: "El campo VENDEDOR ASIGNADO debe tener un valor!",
          icon: "error",
          buttons: false,
        });*/
        return false;
      }else if (document.getElementById("ruc").value=='') {
        // Si no se cumple la condicion...
        swal("Alerta!", "El campo R.U.C debe tener un valor!", {buttons:false,});
        /*swal({
          title: "Error!",
          text: "El campo R.U.C debe tener un valor!",
          icon: "error",
          buttons: false,
        });*/

        //alert('[ERROR] El campo DIRECCION debe tener un valor');
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
  </script>
</body>
</html> 
