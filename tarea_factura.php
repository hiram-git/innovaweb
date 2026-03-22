<?php
include_once "permiso.php";
include_once "config/db.php";

$codcliente=str_replace("%23", "#", $_GET['id']);
$nomcliente=str_replace("%23", "#", $_GET['nom_cliente']);

$codcliente              = str_replace("'", "''", $codcliente);
$format_nomcliente=str_replace("#", "%23", $nomcliente);
$format_codcliente=str_replace("#", "%23", $codcliente);

//echo $format_codcliente." aqui ";
//$_SESSION['conteo']=$_SESSION['conteo']+1;
//echo "visitas: ".$_SESSION['conteo'];

?>
<!DOCTYPE html>
<html>
<head>
<!-- <meta http-equiv="Expires" content="0">
<meta http-equiv="Last-Modified" content="0">
<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
<meta http-equiv="Pragma" content="no-cache"> -->
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">

<?php
//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
?>
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css">
<script src="jquery/jquery-3.2.1.slim.min.js"></script>
<script src="jquery/popper.min.js"></script>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script> -->
<script src="bootstrap2/js/bootstrap.min.js"></script>
<!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous"> -->
<!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
 <link href='https://fonts.googleapis.com/css?family=Gloria+Hallelujah' rel='stylesheet' type='text/css'> -->

<link href="fontawesome-free-6.2.1/css/all.css" rel="stylesheet">
<!--<link href="font-awesome-4.7.0/css/all.css" rel="stylesheet">-->
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

.custom-select{
  border-radius:2.25rem !important; 
}  

.swal-footer{
  text-align: center; 
}  

.modal {
  margin-top: 150px !important;
  
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
    margin-top:5px;
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

/* unvisited link */
a:link {
  color: black;
  text-decoration: underline;
}

/* visited link */
a:visited {
  color: black;
  text-decoration: underline;
}

/* mouse over link */
a:hover {
  color: black;
  text-decoration: underline;
}

/* selected link */
a:active {
  color: black;
  text-decoration: underline;
}

.titulo{
  text-align:center;color:#fff;font-size:1.2rem;
}

@media only screen and (min-width:320px) and (max-width:480px){
  .contenedor {
    height: 560px; /*altura de scroll segun altura de panatalla*/
  }
  .titulo{
    font-size:1.42rem;
  }
}

@media only screen and (min-width:768px){
  .contenedor {
    height: auto; /*altura de scroll segun altura de panatalla*/
  }
  
}

#cajita_select{
        height:auto;
        width: 100%;
        margin:0px;
        display:table;
        border-spacing:0px;
}
#elemento{
        height:auto;
        width:50%;
        border: 0px solid black;
        display:table-cell;
}

.fade-in {
  animation: fadeIn ease 2s;
  -webkit-animation: fadeIn ease 2s;
  -moz-animation: fadeIn ease 2s;
  -o-animation: fadeIn ease 2s;
  -ms-animation: fadeIn ease 2s;
}
@keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-moz-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-webkit-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-o-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}

@-ms-keyframes fadeIn {
  0% {opacity:0;}
  100% {opacity:1;}
}
.list-group-item {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
#radioGroup {
  display: flex;          /* Activamos Flexbox */
  justify-content: space-around; /* Alineación justificada */
  flex-wrap: wrap;        /* Los elementos se envuelven a la siguiente línea si no hay espacio */
}
.btn-secondary {
    background-color: #6c757d; /* Color de fondo */
    color: #fff; /* Color de texto */
    border: none; /* Borde */
    margin: 10px; /* Espaciado entre botones */
    transition: 0.3s; /* Transición suave para hover */
}

.btn-secondary:hover {
    background-color: #5a6268; /* Color de fondo al pasar el ratón por encima */
    color: #fff; /* Color de texto al pasar el ratón por encima */
}
</style>
</head>
<body>
  <header>
  </header>
<main>
    <?php
    //var_dump($_SESSION);
    //var_dump($_SESSION['aDatos']);
    if(!isset($_SESSION['tipo_tarea'])){
    ?>
    <!-- Modal -->
    <div id="myModal" data-backdrop="static" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
            <i class="fa fa-file-text-o" aria-hidden="true" style='font-size:65px;color:#fa6630;'></i>&nbsp;&nbsp;&nbsp;
            <h4 class="modal-title">¿QUE TAREA DESEA REALIZAR?</h4>
          </div> 
          <form id="myForm">
            <div class="modal-body">  
              <div>
              <?php
              //echo $_SESSION['creacliente'];
              if($_SESSION['ver_pedido']==1){    
              ?>   
                <button type="button" class="btn btn-primary btn-block" onclick="grabar_tarea('pedido');" style="background-color:#FF5001 !important;color:#fff !important;border:none !important;margin-bottom:10px;">PEDIDO</button>
              <?php
              }
              
              if($_SESSION['ver_presupuesto']==1){   
              ?>
                <button type='button' class="btn btn-primary btn-block" onClick="grabar_tarea('presupuesto');" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;;margin-bottom:10px;'>PRESUPUESTO</button>
              <?php
              }
              if($_SESSION['ver_pedido']==1){    
              ?>   
                <button type='button' class="btn btn-primary btn-block" onClick="grabar_tarea('factura');" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;;margin-bottom:10px;'>FACTURA</button>
              <?php
              }
              if($_SESSION['ver_pedido']==1){    
              ?>   
                <button type='button' class="btn btn-primary btn-block" onClick="grabar_tarea('cobro');" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;;margin-bottom:10px;'>COBRO</button>
              <?php
              }
              ?>
    <button type="button" class="btn btn-danger btn-block" onclick="window.history.back();" style="color:#fff !important;border:none !important;margin-top:10px;">CANCELAR</button>
              <div id="mensaje"></div> 
            </div>
             <div class="modal-footer">
              
             <!-- <button type='button' class="btn btn-primary btn-block" onClick="grabar_tarea()">Entrar</button>-->
            </div> 
          </form>
        </div>

      </div>
    </div>
    <!-- Modal -->
    <?php
    
    $name_task="";
    }else if($_SESSION['tipo_tarea']=='presupuesto'){
       $name_task="Presupuesto";
    }else if($_SESSION['tipo_tarea']=='pedido'){
      $name_task="Pedido";
    }else if($_SESSION['tipo_tarea']=='factura'){
      $name_task="Factura";
      //echo "<script>window.location='tarea_factura.php';</script>";
    }else{
      $name_task="Tarea";
    }
    ?>

    <?php
    //echo $_SESSION['tipo_tarea'];
    if(isset($_SESSION['tipo_tarea'])){
    ?>
    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
        <div style="padding:10px 0px 10px 0px;border:0px solid #ccc;width:100%;">
          <div class='titulo'>
                <!-- <i class="fas fa-user-friends" style='font-size:50px;float:left;padding-right:10px;'></i> -->
                <!-- <span><strong>Módulo de Emisión<br /> de Pedidos y Presupuestos</strong></span> -->
                <div style="position:absolute; top:5px; left:0; auto"><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#ffffff;font-size:16px;margin-left:15px;'></i></a></div>   
                <h5 style='color:#fff;'>GENERAR <?php echo strtoupper($name_task);?></h5><hr/>
          </div>
        </div>
        
      </div> <!-- fin container -->
    </div> <!-- fin content -->
    
        
      <div class="content" style='border:0px solid #000;'>   
        <div class="container"  style='border:0px solid #000;'>
        <div class='contenedor'> <!-- inicia el scroll -->
          <?php
          
          $sql3="SELECT 
            a.TIPREG,
            a.NOMBRE,
            a.CODIGO,
            a.RIF,
            a.NIT,
            a.CODVEN,
            a.TIPOCLI,
            a.DIRECC1,
            a.DIRECC2,
            a.NUMTEL,
            a.DIRCORREO,
            a.NUMERO_MOVIL,
            a.PORMAXDESPAR,
            a.PORMAXDESGLO,
            a.PERCREDITO,
            a.LIMITECRE 
          FROM 
            BASECLIENTESPROVEEDORES  as a 
          WHERE 
            a.CODIGO='$codcliente' AND (a.TIPREG = 1)";

          $sentencia4 = $base_de_datos->prepare($sql3, [
          PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
          ]);

          $sentencia4->execute();
          while ($data2 = $sentencia4->fetchObject()){
            $idcod=$data2->CODIGO;
            $nom_cliente=$data2->NOMBRE;
            $vendedor=$data2->CODVEN;
            ?>
            
            <div style='background-color:#939598;color:#fff;height:auto;border-radius:10px 10px 0px 0px;padding:8px 8px 8px 8px;'> Cliente: <b><?php echo $data2->CODIGO;?></b> <b style='font-size:20px;'>|</b> R.U.C: <b><?php echo $data2->RIF;?></b> <b style='font-size:20px;'>|</b> Desc. Global: <b><?php echo $data2->PORMAXDESGLO;?></b></div>
              <div style='background-color:#fff;color:#000;height:auto;border-radius:0px 0px 10px 10px;padding:10px 8px 8px 8px;'>
                <!-- <b>Cliente: <?php echo $data2->NOMBRE;?></b> -->
                <?php
                  if($_SESSION['creacliente']==1){
                    $style_habilitar="text-decoration:underline;";
                  }else{
                    $style_habilitar="pointer-events: none;text-decoration:none;";
                  }
                  ?>
                  <b>Cliente: <a href="form_edit_cliente.php?id=<?php echo $data2->CODIGO;?>&nom_cliente=<?php echo $data2->NOMBRE;?>" style="<?php echo $style_habilitar;?>">
                    <?php echo $data2->NOMBRE;?>
                  </a></b>
            </div>
                    <input type="hidden" class="form-control" id='nit' name='nit' value="<?php echo $data2->NIT;?>" placeholder="" aria-label="nit" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='idcod' name='idcod' value="<?php echo $data2->CODIGO;?>" placeholder="" aria-label="codigo" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='nom_cli' name='nom_cli' value="<?php echo $data2->NOMBRE;?>" placeholder="" aria-label="nombre" aria-describedby="basic-addon1"> 
                    <input type="hidden" class="form-control" id='tipo_cli' name='tipo_cli' value="<?php echo $data2->TIPOCLI;?>" placeholder="" aria-label="tipocli" aria-describedby="basic-addon1"> 
                    <input type="hidden" class="form-control" id='dircli' name='dircli' value="<?php echo $data2->DIRECC1;?>" placeholder="" aria-label="dircli" aria-describedby="basic-addon1"> 
                    <input type="hidden" class="form-control" id='dircli2' name='dircli2' value="<?php echo $data2->DIRECC2;?>" placeholder="" aria-label="dircli2" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='numtel' name='numtel' value="<?php echo $data2->DIRECC2;?>" placeholder="" aria-label="numtel" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='maxdesglo' name='maxdesglo' value="<?php echo $data2->PORMAXDESGLO;?>" placeholder="" aria-label="maxdesglo" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='percredito' name='percredito' value="<?php echo $data2->PERCREDITO;?>" placeholder="" aria-label="percredito" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='limitecre' name='limitecre' value="<?php echo $data2->LIMITECRE;?>" placeholder="" aria-label="limitecre" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='prd_sin_existencia' name='prd_sin_existencia' value="<?php echo $_SESSION["ventamenos"];?>" placeholder="" aria-label="prd_sin_existencia" aria-describedby="basic-addon1">

            <?php
          } /* fin while*/
          //echo $_SESSION['codvendedor_opt']." ".$_SESSION['codalmacen_opt'];
          ?>

      <br />
      
      <div id="cajita_select">
                <div id="elemento" style='padding-right:5px;'>
                  <select class="browser-default custom-select" id="vendedor" name="vendedor" onchange="grabar_vend_alma(this.selectedIndex, 'vendedor');">
                    
                    <?php
                    if(!isset($_SESSION['codvendedor_opt'])){
                      if(!isset($_SESSION['codvendedor'])){  
                        if($vendedor === null or $vendedor === ""){
                          $sql3="SELECT CODVEN, NOMBRE FROM BASEVENDEDORES";
                          $opt="<option value='' disabled selected hidden>Vendedor</option>";

                        }else{
                          $sql3="SELECT CODVEN, NOMBRE FROM BASEVENDEDORES WHERE CODVEN='".$vendedor."'";
                          $opt="";
                          
                        }
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
                        echo "<option value='".$data2->CODVEN."|".$data2->NOMBRE."' selected>".$data2->CODVEN."|".$data2->NOMBRE."</option>";
                      }else{
                        echo "<option value='".$data2->CODVEN."|".$data2->NOMBRE."'>".$data2->CODVEN."|".$data2->NOMBRE."</option>";
                      }
                      
                    }
                    
                    ?>
                  </select>
                </div>
                <div id="elemento" style='padding-left:5px;'>
                  <select class="browser-default custom-select" id="almacen" name="almacen" onchange="grabar_vend_alma(this.selectedIndex, 'almacen');">
                    <?php
                    /*if(!isset($_SESSION['codalmacen'])){  
                      $sql3="SELECT CODIGO, NOMBRE FROM INVENTARIOALMACENES";
                      $opt="<option value='' disabled selected hidden>Almacen</option>";
                    }else{
                      $sql3="SELECT CODIGO, NOMBRE FROM INVENTARIOALMACENES WHERE CODIGO='".$_SESSION['codalmacen']."'";
                      $opt="";
                    }*/

                    if($_SESSION['codalmacen'] == 0){
                      if(!isset($_SESSION['codalmacen']) ){  
                        $sql3="SELECT CODIGO, NOMBRE FROM INVENTARIOALMACENES";
                        $opt="<option value='' disabled selected hidden>Almacen</option>";
                      }else{
                        $sql3="SELECT CODIGO, NOMBRE FROM INVENTARIOALMACENES WHERE CODIGO='".$_SESSION['codalmacen']."'";
                        $opt="";
                      }
                    }else{
                      $sql3="SELECT CODIGO, NOMBRE FROM INVENTARIOALMACENES";
                      //$opt="<option value='' disabled selected hidden>Vendedor</option>";
                    } 
                    /*recuperando todo los productos comandados*/
                    
                    //echo $sql3;EXIT;

                    $sentencia4 = $base_de_datos->prepare($sql3, [
                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                    ]);

                    $sentencia4->execute();
                    echo $opt;
                    while ($data2 = $sentencia4->fetchObject()){
                      //$pormaxdespar=$data2->PORMAXDESPAR;
                      //$CodAlmacen=$data2->CODIGO;
                      //echo "<option value='".$data2->CODIGO."-".$data2->NOMBRE."'>".$data2->CODIGO."-".$data2->NOMBRE."</option>";
                      if(($_SESSION['codalmacen_opt']==$data2->CODIGO) OR ($_SESSION['codalmacen']==$data2->CODIGO)){
                        echo "<option value='".$data2->CODIGO."|".$data2->NOMBRE."' selected>".$data2->CODIGO."|".$data2->NOMBRE."</option>";
                      }else{
                        echo "<option value='".$data2->CODIGO."|".$data2->NOMBRE."'>".$data2->CODIGO."|".$data2->NOMBRE."</option>";
                      }
                    }
                    ?>
                  </select>
                </div>
       </div>
        <br />
        
        
        <div id="mensaje_error"></div> 
            <div>
              <button type='button' class="btn btn-primary btn-block" onClick="mas_prod();" style='background-color:#BCBDC0 !important;color:#000 !important;border:none !important;'><i class="fa-solid fa-plus"></i> <b>AGREGAR PRODUCTOS</b></button>
            </div>
        <br />
      
        
      <!-- </form> -->
      
      
      <!--<div id='layer_container' class="d-flex flex-row justify-content-between mb-3">
        <div id='layer_card' class="card mt-5" style='border: 0px solid rgba(0,0,0,.125) !important;background-color:transparent;border-radius:5px 5px 10px 10px;'>
           <table border="0" width='100%'> -->
          
            <?php         
            //var_dump($_SESSION['aDatos']);
            $subtotal_sum=0;
            $itbm_sum=0;
            $total_sum=0;
            $cantidad_prod=0;
            $max=sizeof($_SESSION['aDatos']);
            //var_dump($_SESSION['aDatos']);
            
            for($i=0; $i<$max; $i++) {
              $k=0;
              foreach ($_SESSION['aDatos'][$i] as $key=> $val){
                $k++;
                if($k==5){ // itbm
                    $cantidad_prod=$cantidad_prod+$val;
                }
              } // fin foreach
            }
            echo "<label>$max registros con $cantidad_prod productos</label>";

            $p=0;
            for($i=0; $i<$max; $i++) {
              if ($i%2==0){
                  //echo "el $numero es par";
                  $color='#EDEDF5';
              }else{
                  $color='#D1D2D4';
              }
              $p++;
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
                  }else if($k==16){ // itbm
                      $exento=$val;
                  }
              } // fin foreach
              $descuento_fmt=round($descuento, 4);
              $subtotal=round(($cantidad*$precio)-$descuento, 2);
              $total=round(($subtotal*($itbm/100))+$subtotal, 2);
              

              $subtotal_sum+=$subtotal;
              $itbm_sum+=($subtotal*($itbm/100));
              $total_sum+=($subtotal*($itbm/100))+$subtotal;
              if($max>1){
                if($p==1){
                  $border="border-radius:20px 20px 0px 0px;";
                }else if($p==$max){
                  $border="border-radius:0px 0px 20px 20px;";
                }else{
                  $border="border-radius:0px 0px 0px 0px;";
                }
              }else{
                $border="border-radius:20px 20px 20px 20px;";
              }

              
              if($exento==1){
                $exento="(E)";
              }else{
                $exento="";
              }
            ?>
            
            <!--nuevo disenio de tarjeta--> 
          
          <div id="contenedor" style='overflow:hidden;border:0px solid #fff;margin-bottom:0px;<?php echo $border;?>'>
            <div style='float:left;border:0px solid #000; width:80%;height:120px;padding:15px 5px 0px 5px;background-color:<?php echo $color;?>;'>
              <?php echo "<b><div style='color:#FF5001;text-align:left;'>".$cod_prod." //</div><div style='text-align:justify:'>".$nom_prod." $exento</div></b>";?>
            </div>
            
            <div style='float:right;border:0px solid #000; width:20%;height:60px;padding-top:15px;text-align:center;background-color:<?php echo $color;?>;'>
              <!-- <a href="eliminar_item.php?iditem=<?php echo $i;?>&nom_cliente=<?php echo $nom_cliente;?>"><img src='imgs/trash_icon.png' width='20px'/></a> -->
              <a class="BTN_Elimiar" data-toggle="modal" data-target="#eliminarModal" data-id-item="<?php echo $i;?>" data-id-nomcliente="<?php echo $nom_cliente;?>" data-id-prod="<?php echo "$cod_prod|$nom_prod";?>"><i class="fa-solid fa-trash-arrow-up" style="color: #ff5001;font-size: x-large;"></i></a>
            </div>
            <div style='float:right;border:0px solid #000; width:20%;height:60px;padding-top:15px;text-align:center;background-color:<?php echo $color;?>;'>
            <a href="form_producto_edit.php?id=<?php echo $format_codcliente;?>&iditem=<?php echo $i;?>&nom_cliente=<?php echo $format_nomcliente;?>"><i class="fa-solid fa-pen-to-square" style="color: #ff5001;font-size: x-large;"></i></a>
            </div>
            <div style='float: left;border:0px solid #000; width:100%;height:80px;background-color:<?php echo $color;?>;'>
              <div style='float:left;border:0px solid #000; width:15%;height:80px;text-align:center;'>
                <b><div style='height:50px;color:#808286;'>Cant.</div>
                <div><?php echo $cantidad;?></div></b>
              </div>
              <div style='float:left;border:0px solid #000; width:20%;height:80px;text-align:center;'>
                <b><div style='height:50px;color:#808286;'>Precio</div>
                <div><?php echo number_format(round($precio, 2), 2);?></div></b>
              </div>
              <div style='float:left;border:0px solid #000; width:20%;height:80px;text-align:center;'>
                <b><div style='height:50px;color:#808286;'>Dcto. Parc.</div>
                <div><?php echo number_format(round($descuento_fmt, 2), 2);?></div></b>
              </div>
              <div style='float:left;border:0px solid #000; width:25%;height:80px;text-align:center;'>
                <b><div style='height:50px;color:#808286;'>Subtotal</div>
                <div><?php echo number_format(round($subtotal, 2), 2);?></div></b>
              </div>
              <div style='float:right;border:0px solid #000; width:20%;height:80px;text-align:center;'>
                <b><div style='height:50px;color:#808286;'>Total</div>
                <div id = "total_factura"><?php echo number_format(round($total, 2), 2);?></div></b>
              </div>
            </div>
          </div>
          
          <?php
            } // fin for
            
            ?> 
          <?php
            
            $sql1="SELECT TOP(1) NOMBRE, DIRECC1, DIRECC2, IDIMPUESTO, IMPPOR FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
            //echo $sql1;
            $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
            $total_reg = $result->fetchColumn();
            if($total_reg!=''){
              //$sql_button="SELECT CONTROL, NUMREF, DESCRIP1, FECEMISS, MONTOSUB, MONTOTOT, TIPTRAN, NOMBRE FROM TRANSACCMAESTRO WHERE CODIGO='$codigo'";
              $sentencia_b = $base_de_datos->prepare($sql1, [
                                          PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                      ]);
              $sentencia_b->execute();
              while ($data_b = $sentencia_b->fetchObject()){
                $nom_empresa=$data_b->NOMBRE;
                $dir_empresa=$data_b->DIRECC1." ".$data_b->DIRECC2;
                $idimpuesto=$data_b->IDIMPUESTO;
                $porcent_imp=$data_b->IMPPOR;
              }
            }

            if($max>0){  
            ?>
            <div id="contenedor" style='overflow:hidden;border:0px solid #fff;margin:10px 0px 5px 0px;width:auto;'>
              <div style='float: left;border:0px solid #000; width:95%;height:30px;'>
                <div style='float:left;border:0px solid #000; width:15%;height:30px;'>
                  
                </div>
                <div style='float:left;border:0px solid #000; width:20%;height:30px;'>
                
                </div>
                <div style='float:left;border:0px solid #000; width:20%;height:30px;'>
                  
                </div>
                <div style='float:left;border:0px solid #000; width:25%;height:30px;text-align:right;'>
                  <b>Subtotal</b>
                </div>
                <div style='float:right;border:0px solid #000; width:20%;height:30px;text-align:right;'>
                  <b><?php echo number_format(round($subtotal_sum, 2), 2);?></b>
                </div>
              </div>
              <div style='float: left;border:0px solid #000; width:95%;height:30px;'>
                <div style='float:left;border:0px solid #000; width:15%;height:30px;'>
                  
                </div>
                <div style='float:left;border:0px solid #000; width:20%;height:30px;'>
                
                </div>
                <div style='float:left;border:0px solid #000; width:20%;height:30px;'>
                  
                </div>
                <div  style='float:left;border:0px solid #000; width:25%;height:30px;text-align:right;'>
                  <?php
                  echo $idimpuesto;
                  ?>
                </div>
                <div style='float:right;border:0px solid #000; width:20%;height:30px;text-align:right;'>
                <b><?php echo number_format(round($itbm_sum, 2), 2);?></b>
                </div>
              </div>
              <div style='float: left;border:0px solid #000; width:95%;height:30px;'>
                <div style='float:left;border:0px solid #000; width:15%;height:30px;'>
                  
                </div>
                <div style='float:left;border:0px solid #000; width:20%;height:30px;'>
                
                </div>
                <div style='float:left;border:0px solid #000; width:20%;height:30px;'>
                  
                </div>
                <div style='float:left;border:0px solid #000; width:25%;height:30px;text-align:right;'>
                  <b>Total</b>
                </div>
                <div style='float:right;border:0px solid #000; width:20%;height:30px;text-align:right;'>
                  <b id="total_factura"><?php echo number_format(round($total_sum, 2), 2);?></b>
                </div>
              </div> 
            </div> 
			
			<div>
          <span>Observacion</span>
            <textarea class="form-control" style="text-transform: uppercase;" id="obv" name="obv" rows="2" onkeyup="countChars();" placeholder=""></textarea>
            <p id='charNum'>0 Carácteres</p>
        </div>
          <?php
            } // fin if
            
            ?> 
          
          
          <!--nuevo disenio de tarjeta--> 
          <!--</div> fin layer_card-->
      <!--</div>fin layer_container-->
		
    </div>  <!-- fin scroll -->
          </div>    
          </div>     
          <!-- <button type="button" id='btnDelete' name='btnDelete'>Close</button> -->
		  
		  
  <?php
  } /* fin IF $_SESSION['tipo_tarea']*/
  ?>
      <div class="row justify-content-between" style='justify-content: center !important;'>
              <?php
                $max=sizeof($_SESSION['aDatos']);
                if($max>0){
              ?>
                <br />
                <div>
                  <button type="buttom" onclick='abrir_desc_global();' class="btn btn-primary" style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important;'><i class="fa-solid fa-coins"></i>&nbsp;<b>TOTALIZAR</b></button>&nbsp;
                </div><br /><br />
              <?php
                  
                }
              ?>
      </div>
      
     
    
    <!-- Modal -->
    <div id="myModal_desc_global" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content" >
          <div class="modal-header">
            <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
            <i class="fa fa-list-alt" aria-hidden="true" style='font-size:65px;color:#fa6630;'></i>&nbsp;
            <h4 class="modal-title">Aplicar Descuento Global y Totalizar</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div> 
          <form id="myForm_desc_global">
            <div class="modal-body">  



              <div style="overflow:hidden;width:100%;">
                <?php
                if($nom_cliente[0]=='?'){
                  $tipo_input='text';
                  $espacio="<br /><br /><br />";
                }else{
                  $tipo_input='hidden';
                  $espacio="";
                }
                ?>
                <div style="float: left;width:100%;">
                  <input type="<?php echo $tipo_input;?>" class="form-control" id='nom_cliente_tarea' name='nom_cliente_tarea' value="" placeholder="Nombre Cliente" aria-label="desc_global" aria-describedby="basic-addon1">
                </div><?php echo $espacio;?>

                <div style="float: left;width:45%;margin-right:5px;">
                  <input type="text" class="form-control" id='desc_global' name='desc_global' value="" placeholder="" aria-label="desc_global" aria-describedby="basic-addon1">
                </div>
                
                <div style="float: right;width:50%;">
                <button type='button' class="btn btn-primary btn-block" onClick="calcular_total();" style='background-color:#E6E7E9 !important;color:#000 !important;border:1px solid #E6E7E9 !important;'>Aplicar Descuento</button>
                </div>
              </div> 
              
              <div class="container-fluid mt-4">
                <div class="row">
                  <div class="col-md-12">
                    <div id="radioGroup">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-5 col-sm-5 col-md-5 col-lg-5">
                    <label>Monto en</label>
                  </div>
                  <div class="col-5 col-sm-5 col-md-5 col-lg-5">
                    <input id="myInput" type="text" class="form-control" placeholder="Valor">
                  </div>
                  <div class="col-2 col-sm-2 col-md-2 col-lg-2">
                    <button id="addBtn" class="btn btn-primary"><i class="fas fa-plus"></i></button>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-md-12">
                    <div id="myTable">
                    </div>
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-12">
                    <span id="detalle_desc_global">
                    </span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6">SubTotal:
                  </div>
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <span id="subtotal" class="lead">: 0</span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6">Desc. Global:
                  </div>
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <span id="descuento_global" class="lead">: 0</span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6">Total_Neto:
                  </div>
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <span id="total_neto" class="lead">: 0</span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6"><?= $idimpuesto ?>:
                  </div>
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <span id="itbms" class="lead">: 0</span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6">Saldo:
                  </div>
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <span id="saldo" class="lead">0</span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6">Total:
                  </div>
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <span id="total" class="lead">Total: 0</span>
                  </div>
                </div>
                <div class="row mt-1 text-center">
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-right">Cambio:
                  </div>
                  <div class="col-6 col-sm-6 col-md-6 col-lg-6 text-left">
                    <span id="cambio" class="lead">0</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              
              <br />
              <button type='button' class="btn btn-primary btn-block" onClick="validar_totalizar(desc_global.value);" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;'>Totalizar</button>
            </div>
          </form>
        </div>

      </div>
    </div>
    <!-- Modal -->  

    <!-- Ventana Modal pide confirmacion-->
    <div class="modal fade" id="eliminarModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->

                    <i class="fa fa-trash-o" aria-hidden="true" style='font-size:65px;color:#fa6630;'></i>&nbsp;&nbsp;&nbsp;
                    <h4 class="modal-title">¿DESEA ELIMINAR ESTE PRODUCTO?</h4>
                    <!-- <h4 class="modal-title" id="myModalLabel">Atencion</h4> -->
                </div>
                <div class="modal-body">
                    <span id="idPerfil"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" style='width:130px;background-color:#E6E7E9 !important;color:#000 !important;border:none !important;' class="btn btn-danger" data-dismiss="modal">No</button>
                    <button type="button" style='width:130px;background-color:#FF5001 !important;color:#fff !important;border:none !important;' class="btn btn-primary" id="btnSi">Si</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Ventana Modal pide confirmacion-->
    <!-- Ventana Modal alerta-->
    <div class="modal fade" id="SeleccModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->

                    <i class="fa fa-check" aria-hidden="true" style='font-size:65px;color:#fa6630;'></i>&nbsp;&nbsp;&nbsp;
                    <h4 class="modal-title"><span id="idmsg"></span></h4>
                    <!-- <h4 class="modal-title" id="myModalLabel">Atencion</h4> -->
                </div>
            </div>
        </div>
    </div>
    <!-- Ventana Modal pide alerta-->

    <!-- Ventana Modal alerta
    <div id="Modal_Confirm" class="modal fade" role="dialog">
    
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    
                    <div class="fade-in">
                    <i class="fa fa-check-circle-o" aria-hidden="true" style='font-size:85px;color:#fa6630;'></i>
                    </div>
                    
                </div>
                <div class="modal-body" style='text-align:center !important;'>
                  <h4 class="modal-title"><span id="msg_confirm2"></span></h4><br />
                  <h3 class="modal-title"><span id="msg_confirm"></span></h3><br />
                </div>
            </div>
        </div>
    </div>-->
    <!-- Ventana Modal pide alerta-->
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

document.getElementById('myForm_desc_global').addEventListener('keydown', function(event) {
    if (event.key === 'Enter' || event.keyCode === 13) {
        event.preventDefault(); // Prevent form submission
    }
});

  $(function(){
    $("#myModal").modal();

  });

  desc_global.addEventListener("focusout", function(e) {
      calcular_total();
  });

  function grabar_tarea(dormir) {
    /*var radios = document.getElementsByName("tarea");
    f=false;
    for (x = 0; x < radios.length; x++)
    if (radios[x].checked) {
      dormir=radios[x].value;
      f=true;
      break;
    }*/
    //alert(dormir);
    const mensaje = document.getElementById("mensaje");
    //if(f){
      
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'ajax/get_formas_pago.php', true);
    xhr.onload = function() {
      if (xhr.status === 200) {
        var data = JSON.parse(xhr.responseText);
        var buttonGroup = document.getElementById('radioGroup');

        for (var i = 0; i < data.length; i++) {
          var label = document.createElement('label');
          label.className = 'btn btn-secondary';

          var input = document.createElement('input');
          input.type = 'radio';
          input.name = 'options';
          input.id = 'option' + (i+1);
          input.value = data[i].CODTAR;
          input.autocomplete = 'off';

          label.appendChild(input);
          label.innerHTML += data[i].NOMBRE.substring(0, 15);
          buttonGroup.appendChild(label);
        }
      } else {
        console.log('Error: ' + xhr.status);
      }
    };
    xhr.send();
    /*}else{
        mensaje.innerHTML = "<label style='color:red;'>Selecciona una tarea!</label>";
    }*/
  }

  function validarForm() {
    const mensaje_er = document.getElementById("mensaje_error");
    var x = document.getElementById("vendedor").value;
    var y = document.getElementById("almacen").value;
    if (x==null || x=="") { 
      //alert("El formulario no puede enviarse sin escoger un vendedor");
      mensaje_er.innerHTML = "<label style='color:white;text-align:center;'>Selecciona un vendedor!</label>";
      document.getElementById("vendedor").focus();
      alert('Seleccionar un vendedor');
      return false; 
    }

    if (y==null || y=="") { 
      //alert("El formulario no puede enviarse sin escoger un almacen");
      mensaje_er.innerHTML = "<label style='color:white;text-align:center;'>Selecciona un almacen!</label>";
      document.getElementById("almacen").focus();
      alert('Seleccionar un almacen');
      return false; 
    }

    return true;

  }

  function abrir_desc_global(){
    resultar=validarForm();
    calcular_total();
    var event = new Event('change');
    var event_click = new Event('click');

    // Seleccionar el elemento que deseas desencadenar el evento
    var element = document.getElementById('total');
    var element_click = document.getElementById('addBtn');

    // Desencadenar el evento en el elemento seleccionado
    element_click.dispatchEvent(event);
    element.dispatchEvent(event_click);
    //alert(resultar);
    if(resultar){
      var divs = document.querySelectorAll('[data-id]');
      for (var i = 0; i < divs.length; i++) {
        var div = divs[i];
        div.parentNode.removeChild(div);
      }
      var radios = document.querySelectorAll('input[type=checkbox][disabled]');
      for (var i = 0; i < radios.length; i++) {
          radios[i].disabled = false;
      }
      $("#myModal_desc_global").modal(
        {
          backdrop: 'static', 
          keyboard: false
        }

      );
    }
  }

  function validar_totalizar(descglob){
    descglobal=descglob;
    maxdesglo=document.getElementById("maxdesglo").value;
    /*sbt_tot=parseFloat(document.getElementById("idsubtotal").innerHTML);*/
    /*const mensaje = document.getElementById("mensaje_error");
    mensaje.innerHTML = "";*/
    if(descglobal!=''){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                  //alert(this.responseText);
                  const myArr = this.responseText.split("|");
                  if(myArr[0]==1){       
                    cotizar(descglobal);        
                    //document.getElementById("detalle_desc_global").innerHTML="<br />"+myArr[1];
                  }else if(myArr[0]==0){
                    document.getElementById("detalle_desc_global").innerHTML = "<br /><label style='color:#000;'>"+myArr[1]+"</label>";
                    
                  }else if(myArr[0]==2){
                    document.getElementById("detalle_desc_global").innerHTML = "<br /><label style='color:#000;'>"+myArr[1]+"</label>";
                    
                  }
              }
        };
        xhttp.open("GET", "recalcular_desc_global.php?descglobal="+descglobal+"&maxdesglo="+maxdesglo, true);
        xhttp.send();
    }else{
      cotizar(descglobal);
    }
  }

  function cotizar(saveme){
    //$("#loading").show();
    //alert();
    //maxdesglo=document.getElementById("maxdesglo").value;
    idcod             = formatear_texto(document.getElementById("idcod").value);
    
   
    idcod = idcod.replace(/(%27)+/g, "%27");
    nom_cliente_tarea = document.getElementById("nom_cliente_tarea").value;
      //nom_cli=document.getElementById("nom_cli").value;
    nom_cli  = formatear_texto(document.getElementById("nom_cli").value);
    nit      = formatear_texto(document.getElementById("nit").value);
    tipo_cli = document.getElementById("tipo_cli").value;
    dircli   = formatear_texto(document.getElementById("dircli").value);
    dircli2  = formatear_texto(document.getElementById("dircli2").value);
    numtel   = document.getElementById("numtel").value;
    obv      = formatear_texto(document.getElementById("obv").value);
    almacen  = formatear_texto(document.getElementById("almacen").value);
    vendedor = formatear_texto(document.getElementById("vendedor").value);
    cambio = document.getElementById("cambio").innerHTML;
    saldo = document.getElementById("saldo").innerHTML;
    total_factura = document.getElementById("total").innerHTML;
    var percredito = document.getElementById("percredito").value;
    var innerHTML;
    var limitecre = document.getElementById('limitecre').value;

      //alert(nom_cli+" jhshsdjd");
    /*const mensaje = document.getElementById("detalle_desc_global");
    mensaje.innerHTML = "";*/
    // Formas de pago //
    var items = document.querySelectorAll('#myTable label');
    if (percredito == 0 && parseInt(saldo)> 0) {
      alert("El cliente no permite cancelar a crédito");return;
    }
    if (percredito == 1 && parseInt(saldo) > parseInt(limitecre)) {
      alert("El monto a credito es mayor al permitido por el cliente");return;
    }
    if (percredito == 0 && items.length == 0) {
      alert("Escoja una forma de pago");return;
    }
    var data = [];
    var pagado = 0;
    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      var id = item.getAttribute('data-id');
      var text = item.innerHTML;
      var input = item.nextElementSibling.querySelector('input');
      var value = input.value;
      pagado = (parseFloat(pagado) + parseFloat(value)).toFixed(2);
      data.push({
        id: id,
        text: text,
        value: value
      });
    }

    swal({
          title: 'AUTORIZANDO FACTURA',
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
    /*if((saveme>0) && (saveme<=maxdesglo)){*/
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    //alert(this.responseText);
                    //$("#loading").hide();

                    swal.close(); // Ocultar el indicador de carga
                    $("#myModal_desc_global").modal('hide');
                    const myArr = this.responseText.split("|");
                    if(myArr[0].trim()=='1'){
                      cal_contro_uni=myArr[1];
                      nro_correlativo=myArr[2];
                      tip_tran=myArr[3];
                      respuesta=myArr[3];
                      var respuesta_fel= JSON.parse(myArr[5]);
                      var respuesta;
                      var jsonObject;
                      var isValidJson = true;
                      
                      try {
                        jsonObject = JSON.parse(respuesta_fel["mensaje"]);
                      } catch (error) {
                        isValidJson = false;
                      }
                      
                      console.log(isValidJson);
                      if (isValidJson) {
                        respuesta = jsonObject["mensaje"];
                      } else {
                        respuesta = respuesta_fel["mensaje"].mensaje;
                      }
                      console.log(respuesta);
                      let mensaje = respuesta  ? respuesta : "Facturación Electrónica no habilitada" ;
                      const imageURL = "imgs/icono_gancho.png";
                      swal("Procesado!", "Tarea Realizada Exitosamente!", "success", {
                       // buttons: true,
                        button: {
                          text: "Aceptar",
                          className: "btn btn-primary d-block mx-auto", // Clases para centrar el botón
                        },
                        icon: 'success',
                        closeOnClickOutside: false,
                        content: {
                          element: "div",
                          attributes: {
                          innerHTML: `
                            <div class="row mt-2">
                              <label class="col-2 col-sm-2 col-md-2 col-lg-2 text-right" for="input3">Total:</label>
                              <div class="col-10 col-sm-10 col-md-10 col-lg-10">
                                <input type="text" id="input3" placeholder="Input 3" class="form-control text-right" value='${total_factura}' readonly>
                              </div>
                            </div>
                            <div class="row mt-2">
                              <label class="col-2 col-sm-2 col-md-2 col-lg-2 text-right" for="input1">Pagado:</label>
                              <div class="col-10 col-sm-10 col-md-10 col-lg-10">
                                <input type="text" id="input1" placeholder="Input 1" class="form-control text-right" value='${parseFloat(pagado).toFixed(2)}' readonly>
                              </div>
                            </div>
                            <div class="row mt-2">
                              <label class="col-2 col-sm-2 col-md-2 col-lg-2 text-right" for="input2">Cambio:</label>
                              <div class="col-10 col-sm-10 col-md-10 col-lg-10">
                                <input type="text" id="input2" placeholder="Input 2" class="form-control text-right" value='${cambio}' readonly>
                              </div>
                            </div>
                            <div class="row mt-2">
                              <label class="col-2 col-sm-2 col-md-2 col-lg-2 text-right" for="input2">Fact. El.:</label>
                              <div class="col-10 col-sm-10 col-md-10 col-lg-10">
                              ${mensaje}
                              </div>
                            </div>
                          `,
                          },
                        },
                      }).then((value) => {
                          if (value) {
                            window.location.href='doc_pdf_demo.php?idcontrol='+cal_contro_uni+'&idfac='+nro_correlativo+'&tiptran='+tip_tran;; // Reemplaza con la URL de destino
                          }
                        });
                      //swal("Perfecto!", "Tarea Realizada Exitosamente!", "success");
                      /*$("#Modal_Confirm").modal();
                      $('#msg_confirm2').html("Tarea Realizada");
                      $('#msg_confirm').html("Exitosamente");*/
                     // setTimeout(function(){ window.location='doc_pdf_demo.php?idcontrol='+cal_contro_uni+'&idfac='+nro_correlativo+'&tiptran='+tip_tran; }, 2000);
                      //window.location='doc_pdf_demo.php?idcontrol='+cal_contro_uni+'&idfac='+nro_correlativo+'&tiptran='+tip_tran;
                    }else{
                      swal("Error!", myArr[1], "danger", {
                        buttons: false,
                        icon: 'error',
                      });
                      document.getElementById("detalle_desc_global").innerHTML="<br />"+myArr[1];
                    }
                }
        };
        xhttp.open("GET", "grabar_factura.php?desc_global="+saveme+"&idcod="+idcod+"&nom_cli="+nom_cli+"&nit="+nit+"&saldo="+saldo+"&cambio="+cambio+"&tipo_cli="+tipo_cli+"&dircli="+dircli+"&dircli2="+dircli2+"&numtel="+numtel+"&almacen="+almacen+"&vendedor="+vendedor+"&nom_cliente_tarea="+nom_cliente_tarea+"&obv="+obv+"&data="+ JSON.stringify(data), true);
        xhttp.send();
     /* }else{
        mensaje.innerHTML = "<label style='color:#000;'><br />% Descuento no puede ser mayor al asignado al cliente o al usuario especial</label>";
      }*/
    //window.location='grabar_presupuesto.php?desc_global='+saveme+'&idcod='+idcod+'&nom_cli='+nom_cli+'&nit='+nit+'&tipo_cli='+tipo_cli+'&dircli='+dircli+'&dircli2='+dircli2+'&numtel='+numtel+'&almacen='+almacen+'&vendedor='+vendedor;
  }

  function mas_prod(){
    const mensaje_er = document.getElementById("mensaje_error");
    idcod=formatear_texto(document.getElementById("idcod").value);
    nom_cli=formatear_texto(document.getElementById("nom_cli").value);
    //alert(nom_cli);
    // nit=document.getElementById("nit").value;
    // tipo_cli=document.getElementById("tipo_cli").value;
    // dircli=document.getElementById("dircli").value;
    // dircli2=document.getElementById("dircli2").value;
    // numtel=document.getElementById("numtel").value;
    
    almacen_arr=formatear_texto(document.getElementById("almacen").value).split("|");
    almacen=almacen_arr[0];
    //alert(almacen_arr[0]);
    almacen_arr2=formatear_texto(document.getElementById("vendedor").value).split("|");
    vendedor=almacen_arr2[0];
    nomvendedor=almacen_arr2[1];
    //alert(vendedor);
    if(vendedor!=''){
      if(almacen!=''){
        //vendedor=document.getElementById("vendedor").value;
        window.location='form_producto.php?id='+idcod+'&nom_cliente='+nom_cli+'&CodAlmacen='+almacen+"&CodVen="+vendedor+"&NomVend="+nomvendedor;
      }else{
        //mensaje_er.innerHTML = "<label style='color:white;text-align:center;'>Selecciona un almacen!</label>";
        //document.getElementById("almacen").focus();
        //$('#idmsg').html('<strong>SELECCIONA UN ALMACEN</strong>');
       // $("#SeleccModal").modal('show');
        //alert('Seleccionar un almacen');
        swal("Alerta!", "Selecciona un almacen!", {buttons:false,});

      }
    }else{
        //mensaje_er.innerHTML = "<label style='color:white;text-align:center;'>Selecciona un vendedor!</label>";
        //document.getElementById("vendedor").focus();
        //$('#idmsg').html('<strong>SELECCIONA UN VENDEDOR</strong>');
        //$("#SeleccModal").modal('show');
        //alert('Seleccionar un vendedor');
        swal("Alerta!", "Selecciona un vendedor!", {buttons:false,});
    }
  }

  function formatear_texto(texto){
    //alert(texto);
    texto=texto.replaceAll("#", "%23");
    texto=texto.replaceAll("&", "%26");
    texto=texto.replaceAll("'", "%27%27");
    //alert(texto);
    return texto;
  }

  function calcular_total(){
    descglobal=document.getElementById("desc_global").value;
    maxdesglo=document.getElementById("maxdesglo").value;
    /*sbt_tot=parseFloat(document.getElementById("idsubtotal").innerHTML);*/
    const mensaje = document.getElementById("mensaje_error");
    mensaje.innerHTML = "";
    descglobal = descglobal ? descglobal : 0.00;
    descglobal = encodeURIComponent(descglobal);
    if(descglobal !== "" ){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                  //alert(this.responseText);
                  const myArr = this.responseText.split("|");
                  if(myArr[0]==1){
                    
                  var obj = JSON.parse( myArr[1] );
                    /*var html = "<table border='0'>";
                  html +="<tr width='50%'>";
                  html +="    <td><b>SubTotal:</b></td><td>&nbsp;&nbsp;<b>"+obj.subtotal_sum+"</b></td>";
                  html +="</tr>";
                  html +="<tr  width='50%'>";
                  html +="    <td><b>Desc. Global:</b></td><td>&nbsp;&nbsp;"+obj.desc_global_result+"</td>";
                  html +="</tr>";
                  html +="<tr width='50%'>";
                  html +="    <td><b>Total_Neto:</b></td><td>&nbsp;&nbsp;<b>"+obj.subtotal_sum_result+"</b></td>";
                  html +="</tr>";
                  html +="<tr width='50%'>    ";
                  html +="    <td><b>ITBMS:</b></td><td>&nbsp;&nbsp;"+obj.itbm_sum_result+"<br /></td>";
                  html +="</tr>";
                  html +="<tr width='50%'>";
                  html +="    <td><b>Total:</b></td><td>&nbsp;&nbsp;<b>"+obj.total_sum_result+"</b></td></tr>";
                  */
                  
                  
                  
                  
                  document.getElementById("subtotal").innerHTML=(obj.subtotal_sum_result).toFixed(2);
                  document.getElementById("descuento_global").innerHTML=obj.desc_global_result;
                  document.getElementById("total_neto").innerHTML= (obj.total_sum_result).toFixed(2);
                  document.getElementById("itbms").innerHTML= (obj.itbm_sum_result).toFixed(2);
                  document.getElementById("total").innerHTML= (obj.total_sum_result).toFixed(2);
                  document.getElementById("saldo").innerHTML= (obj.total_sum_result).toFixed(2);
                  document.getElementById("detalle_desc_global").innerHTML ="";
                  }else if(myArr[0]==0){
                    document.getElementById("detalle_desc_global").innerHTML = "<br /><label style='color:#000;'>"+myArr[1]+"</label>";
                    
                  }else if(myArr[0]==2){
                    document.getElementById("detalle_desc_global").innerHTML = "<br /><label style='color:#000;'>"+myArr[1]+"</label>";
                    
                  }
              }
        };
        xhttp.open("GET", "recalcular_desc_global.php?descglobal="+descglobal+"&maxdesglo="+maxdesglo, true);
        xhttp.send();
    }
  }

  function grabar_vend_alma(indice, tipo){
    if(tipo=='vendedor'){
      var valor = document.getElementById("vendedor").options[indice].value;
    }else if(tipo=='almacen'){
      var valor = document.getElementById("almacen").options[indice].value;
    }
    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                  
              }
        };
    xhttp.open("GET", "grabar_vend_alma.php?valor="+valor+"&tipo="+tipo, true);
    xhttp.send();
  }

  function countChars(){
      //alert(document.getElementById('coment'+obj).value.length);
      var maxLength = 59;
      var strLength = document.getElementById('obv').value.length;
      var charRemain = (maxLength - strLength);
      
      if(charRemain < 0){
          //document.getElementById("charNum"+obj).innerHTML = '<span style="color: red;">Has excedido el límite de carácteres '+maxLength+'</span>';
          document.getElementById('obv').value = document.getElementById('obv').value.substring(0, maxLength); 
      }else{
          document.getElementById("charNum").innerHTML = charRemain+' carácteres disponible';
      }
    }

  $(document).ready(function () {
            $('.BTN_Elimiar').click(function () {
                nomcliente = $(this).data('id-nomcliente'); // getter
                id_item = $(this).data('id-item'); // getter
                arr_y = $(this).data('id-prod').split("|"); // getter
                $('#idPerfil').html("<strong style='font-size:18px;'><label style='color:#fa6630;'>"+arr_y[0]+"</label><br />"+ arr_y[1]) + "</strong>";
            })
            $('#btnSi').click(function () {
                $('#eliminarModal').modal('toggle') // cierra el modal
                window.location="eliminar_item.php?iditem="+id_item+"&nom_cliente="+nomcliente;
                //console.warn(`Se eliminara el perfil con ID: ${id_perfil}`); // muestra en consola el id a eliminar
                // seguir accion de eliminar
                //.... resto del codigo ....
            });


            //alert();
        })

  $(window).bind("pageshow", function(event) {
    if (event.originalEvent.persisted) {
        window.location.reload() 
    }
  });
  var addBtn = document.getElementById('addBtn');
  var myInput = document.getElementById('myInput');
  var checkboxSelected = false;
  
  var saldo = document.getElementById('saldo');
  var lastClickedCheckbox = null;
  // Cargar opciones del select utilizando AJAX
  fetch('ajax/get_formas_pago.php')
    .then(response => {
        // Verifica si la respuesta fue exitosa
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
      // Aquí se pueden utilizar los datos, que ya se han analizado como JSON
      const buttonGroup = document.getElementById('radioGroup');

      for (let i = 0; i < data.length; i++) {
          const label = document.createElement('label');
          label.className = 'btn btn-primary';

          const input = document.createElement('input');
          input.type = 'checkbox';
          input.name = 'options';
          input.id = 'option' + (i + 1);
          input.value = data[i].CODTAR;
          input.autocomplete = 'off';
          input.style = 'display: none;';

          label.appendChild(input);
          label.innerHTML += data[i].NOMBRE.substring(0, 15);
          buttonGroup.appendChild(label);
      }
      var checkboxes = document.querySelectorAll('input[type="checkbox"]');


      checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('click', function(event) {
          event.preventDefault();
          lastClickedCheckbox = this;
          checkboxSelected = true; // Establecer en verdadero cuando se selecciona un checkbox

          var option = event.target;
          var nombre = option.parentNode;
          
          var saldo = document.getElementById('saldo');
          myInput.value = saldo.innerHTML;
          
          if(!option.value)
          {
            alert("Seleccione una forma de pago"); return
          }

        });
      });
    })
    .catch(error => {
        // Manejar errores de red aquí
        console.log('There has been a problem with your fetch operation: ' + error.message);
    });
    

    addBtn.addEventListener('click', function(even2) {
      even2.preventDefault();
      even2.stopImmediatePropagation();
  
      if(!checkboxSelected) {
        alert("Por favor, seleccione al menos una forma de pago");
        return;
      }

      var list = document.getElementById('myTable');
      var saldo = document.getElementById('saldo');
      var cambio = document.getElementById('cambio');
      if(!myInput.value || myInput.value < 0)
      {
        alert("Escriba un valor en la forma de pago"); return
      }


      var pago_sel  = parseFloat(myInput.value);
      var saldo_sel = parseFloat(saldo.innerHTML);
      if( pago_sel > saldo_sel && lastClickedCheckbox.value != "01")
      {
        alert("El pago excede al saldo"); return
      }
      
      var sum = 0;
      var items = document.querySelectorAll('#myTable input');
      for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var value = parseFloat(item.value);
        if (!isNaN(value)) {
          sum += value;
        }
      };


      checkboxSelected = false; // Reiniciar valor después de procesar

      var list_table = document.createElement('div');
      list_table.className = 'row mb-2';
      list_table.setAttribute('data-id', lastClickedCheckbox.value);
      var nombre2 = lastClickedCheckbox.parentNode;
      var innerItem = document.createElement('label');
      innerItem.className = 'col-5 col-md-5';
      innerItem.innerHTML = nombre2.innerText.substring(0, 10);
      innerItem.setAttribute('data-id', lastClickedCheckbox.value);
      innerItem.setAttribute('option-id', lastClickedCheckbox.id);
      list_table.appendChild(innerItem);

      var div_input =  document.createElement('div');
      div_input.className = 'col-5 col-md-5';

      var input = document.createElement('input');
      input.setAttribute('readonly', 'readonly');
      input.type = 'text';
      input.className = 'form-control';
      input.value = myInput.value;
      div_input.appendChild(input);
      list_table.appendChild(div_input);

      var div_boton1 = document.createElement('div');
      div_boton1.className = 'col-2 col-md-2';

      var button1 = document.createElement('a');
      button1.setAttribute('href', "#");
      button1.className = 'delete-btn';
      button1.innerHTML = '';
      var icon = document.createElement('i');
      icon.className = 'fas fa-trash-alt';

      button1.appendChild(icon);  
      div_boton1.appendChild(button1);
      list_table.appendChild(div_boton1);
      list.appendChild(list_table);

      lastClickedCheckbox.disabled = true;
      myInput.value = '';

      // Actualizar la sumatoria
      var total = document.getElementById('total');
      var sum = 0;
      var items = document.querySelectorAll('#myTable input');
      for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var value = parseFloat(item.value);
        if (!isNaN(value)) {
          sum += value;
        }
      }
      var saldo_total = 0 ;
      var cambio_total = 0;
      var diferencia = (parseFloat(total.innerHTML) - sum).toFixed(2);  
      if( diferencia < 0){
        cambio_total = (-1)*diferencia;
      }else{
        saldo_total = diferencia;
      }
      cambio.innerHTML = cambio_total;
      saldo.innerHTML = saldo_total;
    });
  // Agregar opciones seleccionadas a la lista al hacer clic en el botón de agregar
  var select = document.getElementById('mySelect');
  var total_factura = document.getElementById('total_factura');
  var total = document.getElementById('total');
  var saldo = document.getElementById('saldo');
  var cambio = document.getElementById('cambio');
  cambio.innerHTML = "0";
  if (total_factura) {
    saldo.innerHTML = total_factura.innerHTML;
    total.innerHTML = total_factura.innerHTML;
  }
  
  /*select.addEventListener('change', function(event) {
    saldo = document.getElementById('saldo');
    event.preventDefault();
    var option = select.options[select.selectedIndex];
    if(!option.value)
    {
       return
    }
    myInput.value = saldo.innerHTML;

  });*/
  var checkboxes = document.querySelectorAll('input[type=checkbox][name="options"]');

  checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('click', function(event) {
            event.preventDefault();
            if(!this.value) {
                return;
            }
            myInput.value = saldo.innerHTML;
        });
    });
  /* var event = new Event('change');

  // Seleccionar el elemento que deseas desencadenar el evento
  var element = document.getElementById('total');

  // Desencadenar el evento en el elemento seleccionado
  element.dispatchEvent(event);*/

  // Eliminar opciones de la lista y volver a agregarlas al select
  var list = document.getElementById('myTable');
  list.addEventListener('click', function(event) {
    if (event.target.parentNode.classList.contains('delete-btn')) {
      var item = event.target.parentNode.parentNode.parentNode;
      
      //var option = item.querySelector('label').innerHTML;
      var value = item.querySelector('label').getAttribute('data-id');
      var optionId =  item.querySelector('label').getAttribute('option-id');
      /*optionElement.value = value;
      optionElement.text = option.substring(0, 10); // Limitar a 10 caracteres
      select.appendChild(optionElement);*/
      item.parentNode.removeChild(item);
      //var optionId = checkboxes = document.getElementsByName('options');
      var optionButton =document.getElementById(optionId);
      optionButton.disabled = false; // Habilitar el botón      // Restar el valor del elemento eliminado del total
      var saldo = document.getElementById('saldo');
      var sum = 0;
      var items = document.querySelectorAll('#myTable input');
      for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var value = parseFloat(item.value);
        if (!isNaN(value)) {
          sum += value;
        }
      }
      saldo.innerHTML = 'Total: $' + sum.toFixed(2);
      saldo.innerHTML = (parseFloat(total.innerHTML) - sum).toFixed(2)
    }
  });

  </script>
  
</body>
</html> 
