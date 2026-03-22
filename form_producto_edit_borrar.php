<?php
include_once("permiso.php");
include_once "config/db.php";
$iditem=$_GET['iditem'];
$codcliente=$_GET['id'];
$nomcliente=$_GET['nom_cliente'];
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" ></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" ></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" ></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css">
<link href="https://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="autocomplete_prod/mack.css">
<script type="text/javascript" src="autocomplete_prod/autocomplete.js"></script>
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
    $sql3="SELECT a.TIPREG, a.NOMBRE, a.CODIGO, a.NUMTEL, a.DIRCORREO, a.NUMERO_MOVIL, a.PORMAXDESPAR, a.PORMAXDESGLO FROM BASECLIENTESPROVEEDORES  as a WHERE a.CODIGO='$cod_prod_edit' AND (a.TIPREG = 1)";
    //echo $sql3;

    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    while ($data2 = $sentencia4->fetchObject()){
      $pormaxdespar=$data2->PORMAXDESPAR;
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
              <button class="btn" style='background-color:#fff !important;border:none;width:100%;' type="button" onClick="validar_prod_disponible('bton_mas');" id="btn_sum">
              <i class="fa fa-plus-square" aria-hidden="true" style='font-size:20px;color:#000;'></i>
              </button>
            <?php
            }else{
              ?>
              <button class="btn" style='background-color:#fff !important;border:none;width:100%;' type="button" onClick="cantidad_mas();" id="btn_sum">
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
                <input type="number" id='cantidad' onkeyup="cantidad_reset();validar_prod_disponible('textbox');" class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1" value="<?php echo $cantidad_edit;?>">
              <?php
              }else{
              ?>
                <input type="number" id='cantidad' onkeyup="cantidad_reset();calcular();" class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1" value="<?php echo $cantidad_edit;?>">
              <?php
              }
              ?> 
          </td>
          <td>Precio<br />
              <input type="hidden" id='itbm' value="0" class="form-control" placeholder="" aria-label="itbm" aria-describedby="basic-addon1">
              <input type="number" autocomplete="off" id='precio' class="form-control" placeholder="" aria-label="cliente" aria-describedby="basic-addon1" value="<?php echo $precio_edit;?>" disabled>
          </td>
          <td style='padding-left:5px;'>Descuento<br />
              <input type="hidden" id='codalmacen' class="form-control" placeholder="" aria-label="codalmacen" aria-describedby="basic-addon1" value="<?php echo $codalmacen_edit;?>">
              <input type="hidden" id='pormaxdespar' class="form-control" placeholder="" aria-label="pormaxdespar" aria-describedby="basic-addon1" value="<?php echo $pormaxdespar;?>">
              <input type="hidden" id='iditem' class="form-control" placeholder="" aria-label="iditem" aria-describedby="basic-addon1" value="<?php echo $iditem;?>">
              <input type="text" autocomplete="off" id='descuento' class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1" value="<?php echo $descuento_edit;?>">
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
              $arr_precio=array('libre', 1, 2, 3, 4, 5);
              $sql33="SELECT PRECIOVENTAD FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
              $sentencia43 = $base_de_datos->prepare($sql33, [
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

              $sentencia43->execute();
              while ($data23 = $sentencia43->fetchObject()){
                  /*como el precio es libre se procede a buscar en la tabla BASEEMPRESA el precio default*/
                  $precio_default=$data23->PRECIOVENTAD;
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
                            <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked /><b> Precio<?php echo $k_pre;?></b>
                          </div>
                          <?php
                    }else{
                          ?>
                          <div class="hijo">
                           <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' /> Precio<?php echo $k_pre;?>
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
              }else if(is_numeric($_SESSION['usuario_precio'])){
                $k=1;
                echo "<div class='padre' style='border:0px solid #ccc;'>";
                foreach($arr_precio AS $k_pre => $pre){
                  if($k_pre==0){
                    ?>
                    <div class="hijo">
                      <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='libre' disabled/> Libre&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                    <?php
                  }else{
                    if($_SESSION['usuario_precio']==$k_pre){
                      ?>
                      <div class="hijo">
                      <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked  disabled/> <b style='color:#000;'>Precio<?php echo $k_pre;?></b>&nbsp;&nbsp;&nbsp;&nbsp;
                      </div>
                      <?php
                    }else{
                      ?>
                      <div class="hijo">
                      <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>'  disabled/> Precio<?php echo $k_pre;?>&nbsp;&nbsp;&nbsp;&nbsp;
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
              }else if($_SESSION['usuario_precio']=='no_definido'){
                $sql33="SELECT PRECIO FROM BASECLIENTESPROVEEDORES WHERE CODIGO='".$codcliente."'";
                $sentencia43 = $base_de_datos->prepare($sql33, [
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

                $sentencia43->execute();
                $k=1;
                echo "<div class='padre' style='border:0px solid #ccc;'>";
                while ($data23 = $sentencia43->fetchObject()){
                  /*como el precio es libre se procede a buscar en la tabla BASECLIENTEPROVEEDOR el precio default*/
                  $precio_cli_prov=$data23->PRECIO;
                  if($precio_cli_prov==0){
                    $precio_cli_prov=$precio_default;
                  }
                }

                foreach($arr_precio AS $k_pre => $pre){
                  //echo "$k_pre => $pre";
                  if($k_pre==0){
                    ?>
                    <div class="hijo">
                      <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='libre' disabled/> Libre&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                    <?php
                  }else{
                    if($precio_cli_prov==$k_pre){
                      ?>
                      <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked  disabled/> <b style='color:#000;'>Precio<?php echo $k_pre;?></b>&nbsp;&nbsp;&nbsp;&nbsp;
                      </div>
                      <?php
                    }else{
                      ?>
                      <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>'  disabled/> Precio<?php echo $k_pre;?>&nbsp;&nbsp;&nbsp;&nbsp;
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
              }
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
          <span style='float:right;color:#FF5001;' id="total"><b>TOTAL: <?php echo $t_subt_edit;?></b></span>
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
              <button type='button' class="btn" onClick="validar_prod_disponible('boton_transac');" style='border:none !important; width:100%;'><strong>Actualizar</strong></button>
            <?php
            }else{
            ?>
              <button type='button' class="btn" onClick="validar_precio('boton_transac')" style='border:none !important; width:100%;'><strong>Actualizar</strong></button>
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
    <!-- <section class='lista_prod'>
      
    </section> -->
 
  </main>
  <script>
    /*esta funcion hace la carga del items insertado en el input*/
    function buscar_prod(texto_buscar){
      //alert(texto_buscar);
      layer_prod=document.getElementById("layer_prod");
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
              //alert(this.responseText);
              layer_prod.innerHTML=this.responseText;
              
              //$("#Modal_Comensales").modal('hide');
          }
      };
      xhttp.open("GET", "buscar_prod.php?txt_buscar="+texto_buscar, true);
      xhttp.send();
    }

    /*funcion que al darle click al items desde el div procede a insertarlo en todo el formulario los datos relacionado al items*/
    var can=1;
    function cargar_data(codpro, descrip, precio1, precio2, precio3, itbm){
      //alert("click al div items");
      if((document.getElementById("input_buscar").value!==codpro) && (document.getElementById("input_buscar").value!=='')){
        can=1;
      }
      document.getElementById("input_buscar").value=codpro;
      document.getElementById("descripcion").value=descrip;
      document.getElementById("precio").value=precio1;
      document.getElementById("itbm").value=itbm;
      /*document.getElementById("precio1").innerHTML="Precio1: <label style='color:#F15A24;'>"+precio1+"</label><br />";
      document.getElementById("precio2").innerHTML="Precio2: <label style='color:#F15A24;'>"+precio2+"</label><br />";
      document.getElementById("precio3").innerHTML="Precio3: <label style='color:#F15A24;'>"+precio3+"</label><br />";*/

      ///document.getElementById("hits").innerHTML=can;
      document.getElementById("descuento").value=0;
      //can=document.getElementById("cantidad").value;
      calc=can*precio1;
      calc = calc.toFixed(2);
      document.getElementById("cantidad").value=can;
      //document.getElementById("layer_prod").innerHTML="";
      
      /*calc=(cantidad*precio1)-((cantidad*precio1)*(descuento/100));
      calc = Number(calc.toFixed(2));*/
      document.getElementById("total").innerHTML="Total: "+calc;
    }

    /*esta funcion carga en el array con el items seleccionado*/
function guardar_prod(){
      inp=document.getElementById("input_buscar").value;
      if(inp!=""){
        //alert(inp);
        codigo=document.getElementById("input_buscar").value;
        nombre=document.getElementById("descripcion").value;
        precio=document.getElementById("precio").value;
        iditem=document.getElementById("iditem").value;
        //alert(precio);
        //descuento=document.getElementById("descuento").value;
        cantidad=document.getElementById("cantidad").value;
        itbm=document.getElementById("itbm").value;
        nota=document.getElementById("nota").value;
        //layer_prod=document.getElementById("layer_prod");
        if(cantidad>0){
          if(precio>0){
            descuento1=document.getElementById("descuento").value;
            var flag=false;
            if(descuento1.indexOf("%")!=-1){
              flag=true;
              //alert("encontro %");
            }

            descuento=parseFloat(descuento1);
            pormaxdespar=document.getElementById("pormaxdespar").value;
            cantidad=document.getElementById("cantidad").value;
            precio=document.getElementById("precio").value;
            //descuento=document.getElementById("descuento").value;
            ban=false;
            if(flag){
              if(descuento<=pormaxdespar){
                calc=((cantidad*precio)*(descuento/100));
                //calc = Number(calc.toFixed(2));
                //document.getElementById("total").innerHTML="Total: "+calc;
                ban=true;
              }else{
                //alert("% Descuento no puede ser mayor al asignado al cliente o al usuario especial");
                document.getElementById("descuento").focus();
              }
            }else{
              if(descuento<(cantidad*precio)){
                calc=descuento;
                //calc = Number(calc.toFixed(2));
                //document.getElementById("total").innerHTML="Total: "+calc;
                ban=true;
              }else{
                //alert("Descuento no puede ser mayor al monto de la transacción");
                document.getElementById("descuento").focus();
              }
            }


            if(ban){
              var xhttp = new XMLHttpRequest();
              xhttp.onreadystatechange = function() {
                  if (this.readyState == 4 && this.status == 200) {
                      //alert('Data cargada!');
                      //alert(this.responseText);
                      myStr=this.responseText;
                      var strArray = myStr.split("|");
                      document.getElementById("input_buscar").value="";
                      document.getElementById("descripcion").value="";
                      document.getElementById("precio").value="";
                      document.getElementById("descuento").value="";
                      document.getElementById("cantidad").value='';
                      document.getElementById("itbm").value=0;                     
                      //document.getElementById("t_linea").innerHTML=strArray[0];
                      //document.getElementById("t_items").innerHTML=strArray[1];
                      //document.getElementById("t_subt").innerHTML=strArray[2];
                      //layer_prod.innerHTML=this.responseText;
                      
                      //$("#Modal_Comensales").modal('hide');
                      window.history.back();

                  }
              };
              xhttp.open("GET", "guardar_prod.php?codigo="+codigo+"&nombre="+nombre+"&precio="+precio+"&descuento="+calc+"&cantidad="+cantidad+"&itbm="+itbm+"&iditem="+iditem+"&nota="+nota, true);
              xhttp.send();
            }else{
              alert("Existen datos no válidos");
            }

          }else{
            //alert("El precio no es válido");
            //document.getElementById("precio").focus();
          }
        }else{
          //document.getElementById("cantidad").value=0;
          //document.getElementById("sp_cantidad").innerHTML.=" Valor no válido!";
          //document.getElementById("cantidad").focus();
          
        }
      }else{
        //document.getElementById("input_buscar").focus();
      }
}

    /*execute a function presses a key on the keyboard:*/
    /*cantidad.addEventListener("focusout", function(e) {
      calcular();
    });*/

    /*btn_sum.addEventListener("click", function(e) {
      if(document.getElementById("cantidad").value!=''){
        document.getElementById("cantidad").value=can++;
        calcular();
      }
      
    });*/
    
    precio.addEventListener("focusout", function(e) {
      validar_precio("textbox");
    });
    
    descuento.addEventListener("focusout", function(e) {
      calcular();
    });

function cantidad_mas(){
      //alert(can);
      if(document.getElementById("cantidad").value!=''){
        can++;
        document.getElementById("cantidad").value=can;
        //alert(can);
        calcular();
      }
}

function cantidad_reset(){
      //alert(document.getElementById("cantidad").value);
      if(document.getElementById("cantidad").value!=''){
        //document.getElementById("cantidad").value=can++;
        can=document.getElementById("cantidad").value;
        //calcular();
      }
}

function validar_prod_disponible(origen){
        if(origen=='bton_mas'){
            if(document.getElementById("cantidad").value!=''){
                can++;
                document.getElementById("cantidad").value=can;
            }
        }
      //alert('validando prod disponible');
        temp=can;
        //alert(temp);
        codalmacen=document.getElementById("codalmacen").value;
        codpro=document.getElementById("input_buscar").value;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        //alert('Data cargada!');
                        //alert(this.responseText);
                        //document.getElementById("label_alert").innerHTML=this.responseText;
                        myStr=this.responseText;
                        var strArray = myStr.split("|");
                        if(strArray[0]==1){
                          
                          if(origen=='bton_mas'){
                            /*if(document.getElementById("cantidad").value!=''){
                                can++;
                                document.getElementById("cantidad").value=can;
                                //calcular();
                            }*/
                            calcular();
                          }else if(origen=='textbox'){
                            calcular();
                          }else{
                            validar_precio('boton_transac');                     
                            //cargar_prod();
                          }                  
                        }else if(strArray[0]==3){
                          
                        }else{
                          //alert("Este precio no cumple con las reglas del juego");
                          //document.getElementById("cantidad").value=can--;
                          //alert('menos');
                          //document.getElementById("cantidad").value=can--;
                          //can=can--;
                          if(origen=='bton_mas'){
                            if(document.getElementById("cantidad").value!=''){
                                can--;
                                document.getElementById("cantidad").value=can;
                            }
                          }

                          if(document.getElementById("cantidad").value!=''){
                            alert(strArray[1]);
                          }
                          
                          document.getElementById("label_alert").innerHTML=strArray[1];
                          //document.getElementById("label_alert").innerHTML=can;
                        }
                        
                    }
        };
        xhttp.open("GET", "validar_prod_disponible.php?codalmacen="+codalmacen+"&codpro="+codpro+"&cantidad="+temp, true);
        xhttp.send();
      
}

function validar_precio(origen){
      //alert('validando precio');
      precio=document.getElementById("precio").value;
      codpro=document.getElementById("input_buscar").value;
      //alert(codpro);
      /*validamos si el precio cumple con la regla de precio y costo*/
      var xhttp = new XMLHttpRequest();
              xhttp.onreadystatechange = function() {
                  if (this.readyState == 4 && this.status == 200) {
                      //alert('Data cargada!');
                      //alert(this.responseText);
                      myStr=this.responseText;
                      var strArray = myStr.split("|");
                      if(strArray[0]==1){
                        if(origen=='textbox'){
                          calcular();
                        }else{
                          guardar_prod();
                        }                  
                      }else if(strArray[0]==3){
                        
                      }else{
                        //alert("Este precio no cumple con las reglas del juego");
                        alert(strArray[1]);
                        document.getElementById("label_alert").innerHTML=strArray[1];
                      }
                      
                  }
              };
      xhttp.open("GET", "validacion_precio.php?precio="+precio+"&codpro="+codpro, true);
      xhttp.send();
}


    function calcular(){
      inp=document.getElementById("input_buscar").value;
      if(inp!=""){
        cantidad=document.getElementById("cantidad").value;
        if(cantidad>0){
          precio=document.getElementById("precio").value;
          if(precio>0){
            descuento1=document.getElementById("descuento").value;
            var flag=false;
            if(descuento1.includes('%')){
              flag=true;
              //alert("encontro %");
            }

            descuento=parseFloat(descuento1);
            pormaxdespar=document.getElementById("pormaxdespar").value;
            cantidad=document.getElementById("cantidad").value;
            precio=document.getElementById("precio").value;
            //descuento=document.getElementById("descuento").value;
            if(flag){
              //alert(descuento+"<="+pormaxdespar);
              if(descuento<=pormaxdespar){
                calc=(cantidad*precio)-((cantidad*precio)*(descuento/100));
                calc = Number(calc.toFixed(2));
                document.getElementById("total").innerHTML="<b>Total: "+calc+"</b>";
                document.getElementById("label_alert").innerHTML="";
              }else{
                //alert("% Descuento no puede ser mayor al asignado al cliente o al usuario especial");
                document.getElementById("label_alert").innerHTML="% Descuento no puede ser mayor al asignado al cliente o al usuario especial";
                //document.getElementById("descuento").focus();
              }
            }else{
              if(descuento<(cantidad*precio)){
                calc=(cantidad*precio)-descuento;
                calc = Number(calc.toFixed(2));
                document.getElementById("total").innerHTML="<b>Total: "+calc+"</b>";
                document.getElementById("label_alert").innerHTML="";
              }else{
                //alert("Descuento no puede ser mayor al monto de la transacción");
                document.getElementById("label_alert").innerHTML="Descuento no puede ser mayor al monto de la transacción";
                document.getElementById("descuento").focus();
              }
            }
          }else{
            //alert("El precio no es válido");
            document.getElementById("label_alert").innerHTML="El precio no es válido";
            document.getElementById("precio").focus();
          }
        }else{
          if(document.getElementById("descripcion").value!=''){
            document.getElementById("label_alert").innerHTML="La cantidad no es válida";
            document.getElementById("cantidad").focus();
          }
        }
      }
    }


    function countChars(){
      //alert(document.getElementById('coment'+obj).value.length);
      var maxLength = 79;
      var strLength = document.getElementById('nota').value.length;
      var charRemain = (maxLength - strLength);
      
      if(charRemain < 0){
          //document.getElementById("charNum"+obj).innerHTML = '<span style="color: red;">Has excedido el límite de carácteres '+maxLength+'</span>';
          document.getElementById('nota').value = document.getElementById('nota').value.substring(0, maxLength); 
      }else{
          document.getElementById("charNum").innerHTML = charRemain+' carácteres disponible';
      }
    }

    
    function extraer_precio(txt_precio, txt_cod){
      //alert(txt_cod);
      pr=document.getElementById("cantidad").value;
      if(pr!=''){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              //alert(this.responseText);
              document.getElementById("precio").value=Number(parseFloat(this.responseText).toFixed(2));
              calcular();
            }
        };
        xhttp.open("GET", "buscar_prod_precio.php?codigo="+txt_cod+"&t_precio="+txt_precio, true);
        xhttp.send();
      }
    }
  </script>
</body>
</html> 
