<?php
include_once("permiso.php");
include_once "config/db.php";
$iditem=$_GET['iditem'];
//$codcliente=$_GET['id'];
$codcliente=str_replace("%23", "#", $_GET['id']);
//$nomcliente=$_GET['nom_cliente'];
$nomcliente = str_replace("%23", "#", $_GET['nom_cliente']);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css">
<script src="jquery/jquery-3.2.1.slim.min.js"></script>
<script src="jquery/popper.min.js"></script>
<script src="bootstrap2/js/bootstrap.min.js"></script>
<!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
 <link rel="stylesheet" type="text/css" href="autocomplete_prod/mack.css">
<script type="text/javascript" src="autocomplete_prod/autocomplete.js"></script> -->
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
    /*border:5px solid #ccc !important;*/
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

.padre {
  /* IMPORTANTE */
  text-align: center;
}

.hijo {
  /*background-color: yellow;*/
  border:0px solid #000;
  padding: 5px;
  margin: 2px;
  width:120px;
  color:#000;
  text-align:left;
  /* IMPORTANTE */
  display: inline-block;
}

input[type="radio"] {
    -ms-transform: scale(1.7); /* IE 9 */
    -webkit-transform: scale(1.7); /* Chrome, Safari, Opera */
    transform: scale(1.7);
    margin-right:10px;
}

</style>
</head>
<body>
  <header>
  </header>
  <main>
      <!-- <h5 style='color:#fff;'><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:10px;'></i></a>&nbsp;&nbsp;EDITAR PRODUCTO</h5><hr/><br /> -->
      <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
        <div style="padding:10px 0px 10px 0px;border:0px solid #ccc;width:100%;">
          <div class='titulo'>
          <div style="position:absolute; top:5px; left:0; auto"><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:15px;'></i></a></div>   
                <h5 style='color:#fff;'>EDITAR PRODUCTO</h5><hr/>
          </div>
        </div>
      </div> <!-- fin container -->
      </div> <!-- fin content -->
    <?php
 
    //$t_cant=0;
    $t_subt_edit=0;
    /*$max=sizeof($_SESSION['aDatos']);
    for($i=0; $i<$max; $i++) {*/
    //var_dump($_SESSION['aDatos']);
    $y=0; 
    foreach ($_SESSION['aDatos'][$iditem] as $key=> $val){
          $y++;
          if($y==1){ // codigo del producto
              $cod_prod_edit=$val;
          }else if($y==2){ // nombre del producto
              $nom_prod_edit=$val;
          }else if($y==3){ // precio del producto
              $precio_edit=$val;
          }else if($y==4){ // cantidad del producto
              $descuento_edit=$val;
          }else if($y==5){ // itbm
              $cantidad_edit=$val;
          }else if($y==6){ // itbm
              $itbm_edit=$val;
          }else if($y==13){ // itbm
            $codalmacen_edit=$val;
          }else if($y==17){ // itbm
            $nota_edit=$val;
          }
    } // fin foreach
      //$t_cant+=$cantidad;
    $t_subt_edit+=($cantidad_edit*$precio_edit)-$descuento_edit;
    //}

    /*recuperando todo los productos comandados*/
        $codcliente              = str_replace("'", "''", $codcliente);
    $sql3="SELECT a.TIPREG, a.NOMBRE, a.CODIGO, a.NUMTEL, a.DIRCORREO, a.NUMERO_MOVIL, a.PORMAXDESPAR, a.PORMAXDESGLO, a.PRECIO FROM BASECLIENTESPROVEEDORES  as a WHERE a.CODIGO='$codcliente' AND (a.TIPREG = 1)";
    //echo $sql3;

    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    while ($data2 = $sentencia4->fetchObject()){
      $pormaxdespar=$data2->PORMAXDESPAR;
      $precio_default_cliente=$data2->PRECIO;
    }
    ?>
    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
      <table border=0 style='color:#fff;'>
        <tr>
          <td colspan="3">
            <div>Código Producto<br />
                <input autocomplete="off" type="search" class="form-control rounded" placeholder="Buscar codigo" aria-label="Search"
                aria-describedby="search-addon" id='input_buscar' value="<?php echo $cod_prod_edit;?>" readonly=true/>
            </div>
          </td>
        </tr>
        <tr>
          <td style='text-align:center;padding-right:5px;'>
            <!-- <button class="btn" style='background-color:#fff !important;border:none;width:100%;' type="button" onClick="validar_prod_disponible('bton_mas');" id="btn_sum">
              <i class="fa fa-plus-square" aria-hidden="true" style='font-size:20px;color:#000;'></i>
            </button> -->

            <?php
            if($_SESSION['tipo_tarea']=='pedido'){
            ?>
              <button class="btn" style='background-color:#fff !important;border:none;width:100%;' type="button" onClick="validar_prod_disponible('bton_mas', 'edit');" id="btn_sum">
              <i class="fa fa-plus-square" aria-hidden="true" style='font-size:20px;color:#000;'></i>
              </button>
            <?php
            }else{
              ?>
              <button class="btn" style='background-color:#fff !important;border:none;width:100%;' type="button" onClick="cantidad_mas('edit');" id="btn_sum">
              <i class="fa fa-plus-square" aria-hidden="true" style='font-size:20px;color:#000;'></i>
              </button>
              <?php
            }
            ?>
          </td>
          <td></td>
          <td>
            <!-- <button class="btn btn-outline-secondary" type="button" id="clean" onClick="input_buscar.value='';clean_field();">Borrar</button> -->
            <!-- <button class="btn" style='background-color:#fff !important;border:none;color:#000!important;float:right;width:100%;' type="button" id="clean" onClick="input_buscar.value='';clean_field();"><b>Borrar</b></button></td> -->
        </tr>
        <tr>
          <td style='padding-right:5px;'><span id="sp_cantidad">Cantidad</span><br />
              <!-- <input type="number" autocomplete="off" id='cantidad' onClick="cantidad_reset();validar_prod_disponible('textbox');" class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1" value="<?php echo $cantidad_edit;?>"> -->
              <?php
              if($_SESSION['tipo_tarea']=='pedido'){
              ?>
                <input type="number" id='cantidad' onfocusout="cantidad_reset();validar_prod_disponible('textbox', 'edit');" class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1" value="<?php echo $cantidad_edit;?>">
              <?php
              }else{
              ?>
                <input type="number" id='cantidad' onfocusout="cantidad_reset();calcular('solo_calcular', 'edit');" class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1" value="<?php echo $cantidad_edit;?>">
              <?php
              }
              ?> 
          </td>
          <td>Precio<br />
            <?php
            $sql33="SELECT PRECIOVENTAD, PVPMENOR FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
            $sentencia43 = $base_de_datos->prepare($sql33, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
              ]);

            $sentencia43->execute();
            while ($data23 = $sentencia43->fetchObject()){
              /*como el precio es libre se procede a buscar en la tabla BASEEMPRESA el precio default*/
              $precio_default=$data23->PRECIOVENTAD;
              $precio_menor_baseempresa=$data23->PVPMENOR;
            }
    
            $sql33="SELECT CODUSER, CLAVE, VALVENDEDOR, VALDEPOSITO, ACTPRECIO, VALPRECIO, CREACLIENTE, ACTDESCTOPAR, ACTDESCTOGLOBAL, CAMBIARPRECIO FROM BASEUSUARIOS WHERE UPPER(CODUSER)='".$_SESSION['coduser']."'";
            $sentencia43 = $base_de_datos->prepare($sql33, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
              ]);
        
            $cambiar_precio=0;
            $act_precio_segun_cliente=0;
            $sentencia43->execute();
            while ($data23 = $sentencia43->fetchObject()){
                /*como el precio es libre se procede a buscar en la tabla BASEEMPRESA el precio default*/
                $cambiar_precio=$data23->CAMBIARPRECIO;
                $act_precio_segun_cliente=$data23->ACTPRECIO;
                $val_precio_segun_baseusuario=$data23->VALPRECIO;
                if(($data23->ACTPRECIO==0) AND ($data23->VALPRECIO==0)){ 
                  //si ambos campos son cero indica que el precio es libre
                  $_SESSION['usuario_precio']='libre';
                }else if(($data23->ACTPRECIO==0) AND ($data23->VALPRECIO>0)){ 
                  /*si es cero actprecio y tiene un valor valprecio entonces valprecio es el precio a tomar*/
                  $_SESSION['usuario_precio']=$data23->VALPRECIO;
                }else if($data23->ACTPRECIO==1){ 
                  /*si actprecio es mayor a cero entonces hay que buscar el precio desde la tabla base clientes 
                  proveedores, segun el cliente o proveedor que se le este haciendo la cotizacion o pedido*/
                  $_SESSION['usuario_precio']='no_definido';
                }
            }
            ?>
              <input type="hidden" id='itbm' value="0" class="form-control" placeholder="" aria-label="itbm" aria-describedby="basic-addon1">
              <!--<input type="number" autocomplete="off" onfocusout="validar_precio('textbox', 'edit');" id='precio' class="form-control" placeholder="" aria-label="cliente" aria-describedby="basic-addon1" value="<?php echo $precio_edit;?>" disabled>-->
              <?php
              if($precio_default_cliente==0){
              ?>
                <input type="number" autocomplete="off" onfocusout="validar_precio('textbox', 'edit');" id='precio' class="form-control" placeholder="" aria-label="cliente" aria-describedby="basic-addon1" value="<?php echo $precio_edit;?>">
              <?php
              }else{
              ?>
                <input type="number" autocomplete="off" onfocusout="validar_precio('textbox', 'edit');" id='precio' class="form-control" placeholder="" aria-label="cliente" aria-describedby="basic-addon1" value="<?php echo $precio_edit;?>" disabled>
              <?php 
              }
              ?>
          </td>
          <td style='padding-left:5px;'>Descuento<br />
              <input type="hidden" id='codalmacen' class="form-control" placeholder="" aria-label="codalmacen" aria-describedby="basic-addon1" value="<?php echo $codalmacen_edit;?>">
              <input type="hidden" id='pormaxdespar' class="form-control" placeholder="" aria-label="pormaxdespar" aria-describedby="basic-addon1" value="<?php echo $pormaxdespar;?>">
              <input type="hidden" id='iditem' class="form-control" placeholder="" aria-label="iditem" aria-describedby="basic-addon1" value="<?php echo $iditem;?>">
              <input type="text" autocomplete="off" onfocusout="calcular('solo_calcular', 'edit');" id='descuento' class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1" value="<?php echo $descuento_edit;?>">
          </td>
        </tr>
        <tr>
          <td colspan=3>
            <span id='label_alert' style='color:white;'></span>
          </td>
        </tr>
        <tr>
          <td colspan="3">Descripcion<br />
            <textarea class="form-control" id="descripcion" rows="3" readonly=true><?php echo $nom_prod_edit;?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="3">Precio: <br />
          <?php
              //echo "...dde ".$_SESSION['usuario_precio'];
              $arr_precio_block=array('','', '', '', '', '');
              $arr_precio=array('libre', 1, 2, 3, 4, 5);
              foreach($arr_precio AS $k_pre => $pre){
                if($k_pre>=$precio_menor_baseempresa){
                  if(($act_precio_segun_cliente==0) AND ($cambiar_precio==0)){
                    $arr_precio_block[$k_pre]='disabled';
                  }else if(($act_precio_segun_cliente==1) AND ($cambiar_precio==0)){
                    //$arr_precio_block[$k_pre]='disabled';
                    if($precio_default_cliente==0){
                      $arr_precio_block[$k_pre]='';
                    }else{
                      $arr_precio_block[$k_pre]='disabled';
                    }
                  }else{
                    $arr_precio_block[$k_pre]='';
                  }
                }else{
                  //$arr_precio_block[$k_pre]='disabled';
                  if($precio_default_cliente==0){
                    $arr_precio_block[$k_pre]='';
                  }else{
                    $arr_precio_block[$k_pre]='disabled';
                  }
                }
                
                
              }
              if($_SESSION['usuario_precio']=='libre'){
                $k=1;
                echo "<div class='padre' style='border:0px solid #ccc;'>";
                foreach($arr_precio AS $k_pre => $pre){
                  //echo "$k_pre => $pre";
                  if($k_pre==0){
                    ?>
                      <div class="hijo">
                      <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='libre' /> Libre
                      </div>
                      <?php
                  }else{
                    if($precio_default==$k_pre){
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked <?php echo $arr_precio_block[$k_pre];?>/> <b>Precio<?php echo $k_pre;?></b>
                          </div>
                          <?php
                    }else{
                          ?>
                        <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' <?php echo $arr_precio_block[$k_pre];?>/> Precio<?php echo $k_pre;?>
                        </div>
                          <?php
                    }  
                    
                                   
                  }

                  if($k==3){
                    echo "<br />";
                    $k=0;
                  }
                  $k++;
                }
                echo "</div>";
              }else if(($_SESSION['usuario_precio']=='no_definido') OR (is_numeric($_SESSION['usuario_precio']))){
                //if($cambiar_precio==1){ // validamos cambio de precio, en este caso permite cambiar precio definidos en los checkbox
                  $k=1;
                  echo "<div class='padre' style='border:0px solid #ccc;'>";
                  foreach($arr_precio AS $k_pre => $pre){
                    //echo "$k_pre => $pre";
                    if($k_pre==0){ // este es el chechbox libre
                      if($act_precio_segun_cliente==1){
                        //echo "libre soy...";
                        if($precio_default_cliente==$k_pre){
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='libre' checked /> Libre
                          </div>
                          <?php
                        }else{
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='libre' disabled /> Libre
                          </div>
                          <?php
                        }
                      }else{
                      ?>
                        <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='libre' disabled/> Libre
                        </div>
                      <?php
                      }
                    }else{
                      //echo "hsshj";
                      if($act_precio_segun_cliente==1){ //selected en el checkbox segun el precio config para el cliente
                        if($precio_default_cliente==$k_pre){
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked <?php echo $arr_precio_block[$k_pre];?>/> <b>Precio<?php echo $k_pre;?></b>
                          </div>
                          <?php
                        }else{
                              ?>
                            <div class="hijo">
                              <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' <?php echo $arr_precio_block[$k_pre];?> /> Precio<?php echo $k_pre;?>
                            </div>
                              <?php
                        }
                      }else{
                        if($val_precio_segun_baseusuario==$k_pre){
                              ?>
                              <div class="hijo">
                              <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked <?php echo $arr_precio_block[$k_pre];?>/> <b>Precio<?php echo $k_pre;?></b>
                              </div>
                              <?php
                        }else{
                          
                              ?>
                            <div class="hijo">
                              <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' <?php echo $arr_precio_block[$k_pre];?>/> Precio<?php echo $k_pre;?>
                            </div>
                              <?php
                        } 
                      }                
                    }

                    if($k==3){
                      echo "<br />";
                      $k=0;
                    }
                    $k++;
                  }
                  echo "</div>";
              } // precio no definido
              ?>
          </td>
        </tr>
        <!-- <tr>
          <td colspan="3">Descripción Ampliada<br />
            <textarea class="form-control" id="nota" rows="2"><?php echo $nota_edit;?></textarea>
          </td>
        </tr> -->
        <tr>
          <td colspan="3">Descripción Ampliada
            <textarea class="form-control" id="nota" rows="2" onkeyup="countChars();" placeholder=""><?php echo $nota_edit;?></textarea>
            <p id='charNum'>0 Carácteres</p>
          </td>
        </tr>
        <tr>
          
        </tr>
        <tr>
          <td colspan=2></td>
          <td>
          <span style='float:right;color:#000;' id="total"><b>Total: <?php echo number_format(round($t_subt_edit, 2), 2, '.', '');?></b></span>
          </td>
        </tr>
        <tr>
          <td colspan=2 style='padding-right:5px;' width='50%'>

            <!-- <button type="button" class="btn btn-default" onClick="window.history.back();"><i class="fa fa-arrow-left" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Volver</button> -->
            <!-- <button type="submit" name='transaccion' class="btn btn-primary" value='actualizar'>Actualizar</button> -->
            
            <div class="content" style='border:0px solid #ccc;text-align:center;'>
            
            <button type="button" class="btn" onClick="window.history.back();" style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important; width:100%;'><i class="fa fa-chevron-left" aria-hidden="true" style='color:#000;'></i>&nbsp;&nbsp;&nbsp;<strong>Volver</strong></button>
            </div>
          </td>
          <td style='padding-left:5px;'>
            <!-- <button type="button" class="btn btn-default" onClick="window.history.back();"><i class="fa fa-arrow-left" aria-hidden="true" style='font-size:20px;'></i>&nbsp;Volver</button> -->
            <!-- <button type="submit" name='transaccion' class="btn btn-primary" value='actualizar'>Actualizar</button> -->
            
            <div class="content" style='border:0px solid #ccc;text-align:center;'>
            
            <!-- <button type="submit" class="btn" onClick="cantidad_reset();validar_prod_disponible('boton_transac');" style='border:none !important; width:100%;'><strong>Guardar</strong></button> -->
            <?php
            if($_SESSION['tipo_tarea']=='pedido'){
            ?>
              <button type='button' class="btn" onClick="validar_prod_disponible('boton_transac', 'edit');" style='border:none !important; width:100%;'><strong>Actualizar</strong></button>
            <?php
            }else{
            ?>
              <button type='button' class="btn" onClick="validar_precio('boton_transac', 'edit')" style='border:none !important; width:100%;'><strong>Actualizar</strong></button>
            <?php
            }
            ?>
            </div>
          </td>
        </tr>
      </table>
  </div>
  </div>
      <br />
    <script type="text/javascript" src="funciones.js"></script>
  </main>
</body>
</html> 
