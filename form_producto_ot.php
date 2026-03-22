<?php
include_once("permiso.php");
include_once "config/db.php";
$codcliente = str_replace("%23", "#", $_GET['id']);
$nomcliente = str_replace("%23", "#", $_GET['nom_cliente']);
$CodAlmacen = str_replace("%23", "#", $_GET['CodAlmacen']);
$CodVen     = str_replace("%23", "#", $_GET['CodVen']);
$NomVend    = str_replace("%23", "#", $_GET['NomVend']);
//echo $_GET['nom_cliente'];
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="jquery/popper.min.js"></script>
<script src="bootstrap2/js/bootstrap.min.js"></script>
<!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
 <link href='https://fonts.googleapis.com/css?family=Gloria+Hallelujah' rel='stylesheet' type='text/css'> 
<link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">-->

<!-- Incluye los archivos JS de DataTables -->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<link href="fontawesome-free-6.2.1/css/all.css" rel="stylesheet">
<script src="jquery/sweetalert.min.js"></script>
<title>
    <?php echo $_SESSION['titulo_web'];?>
  </title>
<!-- <link rel="stylesheet" type="text/css" href="autocomplete_prod/mack.css">
<script type="text/javascript" src="autocomplete_prod/autocomplete.js"></script> -->
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

.modal-backdrop.show {
  opacity: 0.8 !important;
  filter: alpha(opacity=80); /* Para versiones anteriores de IE */
  } 
/*
  .modal-dialog {
    margin: 1.5rem !important;
  }*/

  .modal-content {
    border-radius: 0.5rem;
    border: 0px solid rgba(0,0,0,.2);

  }
/*
  .modal {
    left: 0% !important;
  }*/
/*  .modal-header {
    padding: 1rem;
  }*/


  .form {
    position: relative;
}

.form .fa-search {
    position: absolute;
    top: 3px;
    left: 1px;
    color: #fff;
}

.form span {
    position: absolute;
    right: 5px;
    top: 1px;
    padding: 2px;
    /*border-left: 1px solid #d1d5db*/
}

.left-pan {
    padding-left: 7px
}

.left-pan i {
    padding-left: 10px
}

.form-input {
    height: 50px;
    text-indent: 10px;
    border-radius: 50px
}

.form-input:focus {
    box-shadow: none;
    border: none
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
  width:auto;
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
    /*border:1px solid #fff !important;*/
    background-color:#fff !important;
    color:#000 !important;
  }

  .titulo{
    font-size:1.45rem;
  }
  
  .total{
    color:#000;
    width:540px;
  }

  .modal {
    margin: 0 auto !important;
    /*margin-right: margin: 0 auto !important;*/
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
  /*color:#000;*/
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

/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
@media (max-width: 767px) {
    .hide-on-mobile {
        display: none;
    }
    
    .hide-on-mobile td,
    .hide-on-mobile th {
        font-size: 11px; /* Cambia el tamaño de la fuente según tus preferencias */
    }
  #miTabla {
    font-size: 10px !important; /* Ajusta el tamaño de la fuente según tus preferencias para dispositivos móviles */
  }
  .dataTables_length{
        display: none;

  }
  #miTabla_filter {
    display: flex;
    flex-direction: row;
  }
}

@media (max-width: 1024px) {
  #miTabla {
    font-size: 11px !important; /* Ajusta el tamaño de la fuente según tus preferencias para tablets */
  }
}
</style>
</head>
<body>
  <header>
  </header>
  <main>
     <!--<h5 style='color:#fff;'><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:10px;'></i></a>&nbsp;&nbsp;ASIGNAR PRODUCTOS</h5><hr/><br />
    <h5><a onClick="window.history.back();"><i class="fa fa-long-arrow-left" aria-hidden="true" style='color:#F15A24;font-size:23px;'></i></a>&nbsp;&nbsp;Asignar Productos - <?php echo $nomcliente;?></h5><hr/><br /> -->
    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
        <div style="padding:10px 0px 10px 0px;border:0px solid #ccc;width:100%;">
          <div class='titulo'>
                <!-- <i class="fas fa-user-friends" style='font-size:50px;float:left;padding-right:10px;'></i> -->
                <!-- <span><strong>Módulo de Emisión<br /> de Pedidos y Presupuestos</strong></span> -->
                <div style="position:absolute; top:5px; left:0; auto"><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style="color:#fff;font-size:16px;margin-left:15px;"></i></a></div>             
                <h5 style='color:#fff;'>ASIGNAR PRODUCTOS<br />ORDEN DE TRABAJO</h5><hr/>
                
          </div>
        </div>
        
      </div> <!-- fin container -->
    </div> <!-- fin content -->
    <!-- Modal -->
    <div id="mymodeltask" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          
          <div id='layer_prod'>
          </div> 
        </div>

      </div>
    </div>
    <!-- Modal -->
    <?php
    //echo $_SESSION['coduser'];
    /*recuperando todo los productos comandados*/
    $sql3="SELECT a.TIPREG, a.NOMBRE, a.CODIGO, a.NUMTEL, a.DIRCORREO, a.NUMERO_MOVIL, a.PORMAXDESPAR, a.PORMAXDESGLO, a.PRECIO, a.TIPOCLI FROM BASECLIENTESPROVEEDORES  as a WHERE a.CODIGO='$codcliente' AND (a.TIPREG = 1)";
    //echo $sql3;
    //echo $_SESSION['tipo_tarea']."shgdhahdsgahad";
    $sentencia4 = $base_de_datos->prepare($sql3, [
    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
    ]);

    $sentencia4->execute();
    //echo "<div class='contenedor-tabla'>";
    while ($data2 = $sentencia4->fetchObject()){
      $pormaxdespar=$data2->PORMAXDESPAR;
      $precio_default_cliente=$data2->PRECIO;
      $tipocli=$data2->TIPOCLI;
    }
    $precio_venta_empresa = $_SESSION["precio_venta_emp"];
    //echo "$precio_default_cliente errewr";
    //echo var_dump($_SESSION['aDatos']);
    ?>
    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
    <div id='layer_scroll' class='contenedor'>
      
      <table border='0' style='color:#fff;' cellpadding='0'>
        <tr>
          <td colspan=3>
            <?php 
            $t_cant=0;
            $t_subt=0;
            $max=sizeof($_SESSION['aDatos']);
            for($i=0; $i<$max; $i++) {
              $k=0; 
              foreach ($_SESSION['aDatos'][$i] as $key=> $val){
                  $k++;
                  if($k==1){ // codigo del producto
                      $cod_prod=$val;
                  }else if($k==2){ // nombre del producto
                      $nom_prod=$val;
                  }else if($k==3){ // precio del producto
                      $precio=$val;
                  }else if($k==4){ // cantidad del producto
                      $descuento=$val;
                  }else if($k==5){ // itbm
                      $cantidad=$val;
                  }else if($k==6){ // itbm
                      $itbm=$val;
                  }
              } // fin foreach
              $t_cant+=$cantidad;
              $t_subt+=$cantidad*$precio;
            }
            ?>
           
            <div style='background-color:#939598;color:#fff;height:auto;border-radius:10px 10px 0px 0px;padding:8px 8px 8px 8px;'> Línea: <b id="t_linea" style="color:#000;"><?php echo $max;?></b> <b style='font-size:20px;color:#ccc'>|</b> Items: <b id="t_items" style="color:#000;"><?php echo $t_cant;?></b> <b style='font-size:20px;color:#ccc'>|</b> Subtotal: <b id="t_subt" style="color:#000;"><?php echo number_format(round($t_subt, 2), 2, '.', '');?></b> <b style='font-size:20px;color:#ccc'>|</b> Desc. parcial: <b style="color:#000;"><?php echo $pormaxdespar;?></b></div>
            <div style='background-color:#fff;color:#000;height:auto;border-radius:0px 0px 10px 10px;padding:10px 8px 8px 8px;'>
              <b>Cliente: <?php echo $nomcliente;?></b>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="3">
              <!-- <div class="row height d-flex justify-content-center align-items-center"> -->
                  <!-- <div class="col-md-6"> -->
                      <div class="form"> 
                      <input type='hidden' id='codalmacen' value="<?php echo $CodAlmacen;?>">
                      <input type='hidden' id='codvend' value="<?php echo $CodVen;?>">
                      <input type='hidden' id='nomvend' value="<?php echo $NomVend;?>">
                      <input type='hidden' id='tipocliente' value="<?php echo $tipocli;?>">
                      <input type='hidden' id='ventamenos' value="<?php echo $_SESSION["ventamenos"];?>">
                      <input type="hidden" id='codempaque' value="" class="form-control" placeholder="" aria-label="codempaque" aria-describedby="basic-addon1">

                      <input type="text" class="form-control form-input" placeholder="Buscar...." autocomplete="off" name="input_buscar" id="input_buscar" size='100%'> <a onClick="buscar_prod(input_buscar.value, codalmacen.value);" id="a_buscar" style='pointer-events: none;'><span class="fa-stack fa-lg" id="stack"><i class="fa fa-circle fa-stack-2x" style='font-size:45px;'></i><i class="fa fa-search fa-stack-1x fa-inverse" style='font-size:20px;'></i></span></a>
                    </div>
                  <!-- </div> -->
              <!-- </div> -->
            <!-- </form> -->
          </td>
        </tr>
        <tr>
          <td style='text-align:center;padding-right:5px;'>
            <?php
            if($_SESSION['tipo_tarea']=='pedido'){
            ?>
              <button class="btn" style='background-color:#2b3644 !important;border:none;width:100%;' type="button" onClick="validar_prod_disponible('bton_mas', 'crear');" id="btn_sum">
              <i class="fa fa-plus-square" aria-hidden="true" style='font-size:20px;color:#fff;'></i>
              </button>
            <?php
            }else{
              ?>
              <button class="btn" style='background-color:#2b3644 !important;border:none;width:100%;' type="button" onClick="cantidad_mas();" id="btn_sum">
              <i class="fa fa-plus-square" aria-hidden="true" style='font-size:20px;color:#fff;'></i>
              </button>
              <?php
            }
            ?>
          </td>
          <?php
          if($_SESSION['tipo_tarea']=='pedido'){
          ?>
            <td><button class="btn" style='background-color:#2b3644 !important;border:none;color:#fff!important;float:right;width:100%;' type="button" id="clean" onClick="validar_prod_disponible('boton_transac', 'crear');"><i class="fa fa-cart-plus" aria-hidden="true" style="font-size:20px;color:#fff;"></i></button></td>
          <?php
          }else{
          ?>
            <td><button class="btn" style='background-color:#2b3644 !important;border:none;color:#fff!important;float:right;width:100%;' type="button" id="clean" onClick="validar_precio('boton_transac', 'crear')"><i class="fa fa-cart-plus" aria-hidden="true" style="font-size:20px;color:#fff;"></i></button></td>
          <?php
          }
          ?>
          <td style='padding-left:5px;'>
            <!-- <button class="btn btn-outline-secondary" type="button" id="clean" onClick="input_buscar.value='';clean_field();">Borrar</button> -->
            <button class="btn" style='background-color:#2b3644 !important;border:none;color:#fff!important;float:right;width:100%;' type="button" id="clean" onClick="input_buscar.value='';clean_field();"><i class="fa-solid fa-broom" style='font-size:20px;color:#fff;'></i><b>Borrar</b></button></td>
        </tr>
        <tr>
          <td style='padding-right:5px;'>Cantidad<br />
            <?php
            if($_SESSION['tipo_tarea']=='pedido'){
            ?>
              <input type="number" id='cantidad' name='cantidad' onfocusout="cantidad_reset();validar_prod_disponible('textbox', 'crear');" autocomplete="off" class="form-control" placeholder="" >
            <?php
            }else{
            ?>
              <input type="number" id='cantidad' name='cantidad' onfocusout="cantidad_reset();calcular('solo_calcular', 'crear');" class="form-control" autocomplete="off" placeholder="" >
            <?php
            }
            ?> 
              <input type="hidden" id='costoact' class="form-control" placeholder="" >
              <input type="hidden" id='costopro' class="form-control" placeholder="" >
              <input type="hidden" id='grupoinv' class="form-control" placeholder="" >
              <input type="hidden" id='coddep' class="form-control" placeholder="" >
              <input type="hidden" id='lineainv' class="form-control" placeholder="" >
              <input type="hidden" id='exento' class="form-control" placeholder="" >
          </td>
          <td>Precio:<br />
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
			
          //echo $_SESSION['usuario_precio'];
          if($_SESSION['usuario_precio']=='libre'){
            if($precio_default>0){
            ?>
              <input type="number" id='precio' onfocusout="validar_precio('textbox', 'crear');" value="" autocomplete="off" class="form-control" disabled>
              <?php
            }else{
              ?>
              <input type="number" id='precio' onfocusout="validar_precio('textbox', 'crear');" value="" autocomplete="off" class="form-control" >
              <?php
            }
            
          }else{
            if(($cambiar_precio==1) AND ($_SESSION['usuario_precio']=='no_definido')){ //validando si permite editar precio
              if($precio_default_cliente==0){
                ?>
                  <input type="number" id='precio' onfocusout="validar_precio('textbox', 'crear');" value="" autocomplete="off" class="form-control">
                <?php
              }else{
                ?>
                  <input type="number" id='precio' onfocusout="validar_precio('textbox', 'crear');" value="" autocomplete="off" class="form-control" disabled>
                <?php 
              }
            }else{
              //echo "entramos aqui $cambiar_precio $precio_default_cliente";
              if($precio_default_cliente==0){
              ?>
                <input type="number" id='precio' onfocusout="validar_precio('textbox', 'crear');" value="" autocomplete="off" class="form-control">
              <?php
              }else{
                $precio_default = $precio_venta_empresa;
              ?>
                <input type="number" id='precio' onfocusout="validar_precio('textbox', 'crear');" value="" autocomplete="off" class="form-control" disabled>
              <?php 
              }
            
            }
          }
          ?>       
            <input type="hidden" id='precio_default' value="<?php echo $precio_default;?>" class="form-control" placeholder="" aria-label="itbm" aria-describedby="basic-addon1">
            <input type="hidden" id='itbm' value="0" class="form-control" placeholder="" aria-label="itbm" aria-describedby="basic-addon1">
            <input type="hidden" id='precio1_noformt' value="" class="form-control" placeholder="" aria-label="precio1_noformt" aria-describedby="basic-addon1">
              
          </td>
          <td style='padding-left:5px;'>Descuento<br />
              <input type="hidden" id='pormaxdespar' class="form-control" placeholder="" aria-label="pormaxdespar" aria-describedby="basic-addon1" value="<?php echo $pormaxdespar;?>">
              <input type="text" id='descuento' onfocusout="calcular('solo_calcular', 'crear');" value="0" autocomplete="off" class="form-control" placeholder="" aria-label="ruc" aria-describedby="basic-addon1">
          </td>
        </tr>
        <tr>
          <td colspan=3>
            <span id='label_alert' style='color:white;'></span>
          </td>
        </tr>
        <tr>
          <td colspan="3">Descripcion<br />
            <textarea class="form-control" id="descripcion" rows="3" placeholder="" readonly=true></textarea>
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
                    if($precio_default_cliente==0){
                      $arr_precio_block[$k_pre]='';
                    }else{
                      $arr_precio_block[$k_pre]='disabled';
                    }
                  }else{
                    $arr_precio_block[$k_pre]='';
                  }
                }else{
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
                      <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='libre' /> Libre
                      </div>
                      <?php
                  }else{
                    if($precio_default==$k_pre){
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked <?php echo $arr_precio_block[$k_pre];?>/> <b>Precio<?php echo $k_pre;?></b>
                          </div>
                          <?php
                    }else{
                          ?>
                        <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' <?php echo $arr_precio_block[$k_pre];?>/> Precio<?php echo $k_pre;?>
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
              /*else if(is_numeric($_SESSION['usuario_precio'])){
                $k=1;
                echo "<div class='padre' style='border:0px solid #ccc;'>";
                foreach($arr_precio AS $k_pre => $pre){
                  if($k_pre==0){
                    ?>
                    <div class="hijo">
                      <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='libre' disabled/> Libre&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                    <?php
                  }else{
                    if($_SESSION['usuario_precio']==$k_pre){
                      ?>
                      <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked  disabled/> <b style='color:#000;'>Precio<?php echo $k_pre;?></b>&nbsp;&nbsp;&nbsp;&nbsp;
                      </div>
                      <?php
                    }else{
                      ?>
                      <div class="hijo">
                      <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>'  disabled/> Precio<?php echo $k_pre;?>&nbsp;&nbsp;&nbsp;&nbsp;
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
              }*/
              else if(($_SESSION['usuario_precio']=='no_definido') OR (is_numeric($_SESSION['usuario_precio']))){
                //if($cambiar_precio==1){ // validamos cambio de precio, en este caso permite cambiar precio definidos en los checkbox
                  $k=1;
                  echo "<div class='padre' style='border:0px solid #ccc;'>";
                  foreach($arr_precio AS $k_pre => $pre){
                    //echo "$precio_default_cliente==$k_pre";
                    if($k_pre==0){ // este es el chechbox libre
                      if($act_precio_segun_cliente==1){
                        //echo "libre soy...";
                        if($precio_default_cliente==$k_pre){
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='libre' checked /> Libre
                          </div>
                          <?php
                        }else{
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='libre' disabled /> Libre
                          </div>
                          <?php
                        }
                      }else{
                      ?>
                        <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = false;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='libre' disabled/> Libre
                        </div>
                      <?php
                      }
                    }else{
                      //echo "hsshj";
                      if($act_precio_segun_cliente==1){ //selected en el checkbox segun el precio config para el cliente
                        //echo "$precio_default_cliente==$k_pre";
                        if($precio_default_cliente==$k_pre){
                          ?>
                          <div class="hijo">
                          <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked <?php echo $arr_precio_block[$k_pre];?>/> <b>Precio<?php echo $k_pre;?></b>
                          </div>
                          <?php
                        }else{
                              ?>
                            <div class="hijo">
                              <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' <?php echo $arr_precio_block[$k_pre];?> /> Precio<?php echo $k_pre;?>
                            </div>
                              <?php
                        }
                      }else{
                        if($val_precio_segun_baseusuario==$k_pre){
                              ?>
                              <div class="hijo">
                              <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked <?php echo $arr_precio_block[$k_pre];?>/> <b>Precio<?php echo $k_pre;?></b>
                              </div>
                              <?php
                        }else{
                              ?>
                            <div class="hijo">
                              <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' <?php echo $arr_precio_block[$k_pre];?>/> Precio<?php echo $k_pre;?>
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
                //}
                /*else{ // no permite cambiar precio definidos en los checkbox
                  $sql33="SELECT PRECIO FROM BASECLIENTESPROVEEDORES WHERE CODIGO='".$codcliente."'";
                  $sentencia43 = $base_de_datos->prepare($sql33, [
                  PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                  ]);

                  $sentencia43->execute();
                  while ($data23 = $sentencia43->fetchObject()){
                    
                    $precio_cli_prov=$data23->PRECIO;
                    if($precio_cli_prov==0){
                      $precio_cli_prov=$precio_default;
                    }
                  }

                  $k=1;
                  echo "<div class='padre' style='border:0px solid #ccc;'>";
                  foreach($arr_precio AS $k_pre => $pre){
                    //echo "$k_pre => $pre";
                    if($k_pre==0){
                      ?>
                      <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='libre' disabled/> Libre&nbsp;&nbsp;&nbsp;&nbsp;
                      </div>
                      <?php
                    }else{
                      if($precio_cli_prov==$k_pre){
                        ?>
                        <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked  disabled/> <b style='color:#000;'>Precio<?php echo $k_pre;?></b>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                        <?php
                      }else{
                        ?>
                        <div class="hijo">
                        <input type="radio" onclick="document.getElementById('precio').disabled = true;" style='width: auto !important;' name="precio_"  data-id="<?php echo $k_pre+1;?>" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>'  disabled/> Precio<?php echo $k_pre;?>&nbsp;&nbsp;&nbsp;&nbsp;
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

                }// if validar cambio de precio */
              } // precio no definido
              ?>
            </td>
        </tr>
        <tr>
          <td colspan="3">Descripción Ampliada
            <textarea class="form-control" style="text-transform: uppercase;" id="nota" rows="2" onkeyup="countChars();" placeholder=""></textarea>
            <p id='charNum'>0 Carácteres</p>
          </td>
        </tr>
      </table>
      
      
      </div> <!--fin scroll-->
          </div>
          </div>

    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
        <div class='total' style='height:auto;padding:8px 15px 8px 8px;border:0px solid #000;margin: 0 auto;'>
          <span style='float:right;' id="total"><strong>Total: 0.00</strong></span><br /><br />
        </div>
        <div class="content" style='border:0px solid #ccc;text-align:center;'>
          <?php
          if($_SESSION['tipo_tarea']=='pedido'){
          ?>
            <button type='button' class="btn" onClick="validar_prod_disponible('boton_transac', 'crear');" ><i class="fa fa-cart-plus" aria-hidden="true" style="font-size:25px;"></i> <strong>AGREGAR PRODUCTO</strong></button>
          <?php
          }else{
          ?>
            <button type='button' class="btn" onClick="validar_precio('boton_transac', 'crear')" ><i class="fa fa-cart-plus" aria-hidden="true" style="font-size:25px;"></i> <strong>AGREGAR PRODUCTO</strong></button>
          <?php
          }
          ?>
          
        </div>
      </div>
    </div>
      <br />
    <br /><br />
    <!-- Modal 2 -->
    <div class="modal fade" id="modal2" tabindex="-1" role="dialog">
      <div class="modal-dialog  modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <b>Empaques de Productos</b>
          </div> 
          <div class="modal-body">
            
              <div id="listadoEmpaque"></div>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal 2 -->
    <?php include("recursos/loading.php");?>
  </main>
  <script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#loading").hide();
		});
	</script>
  <script type="text/javascript" src="funciones.js?<?= date("Ymdhis") ?>"></script>
  <script type="text/javascript" src="producto.js?<?= date("Ymdhis") ?>"></script>
</body>
</html> 
