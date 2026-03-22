<?php
include_once "permiso.php";
include_once "config/db.php";

$codcliente=str_replace("%23", "#", $_GET['id']);
$nomcliente=str_replace("%23", "#", $_GET['nom_cliente']);

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
</style>
</head>
<body>
  <header>
  </header>
<main>
    <?php
    //var_dump($_SESSION);
    //var_dump($_SESSION['aDatos']);
    $id = urlencode($_GET['id']);
    $nom_cliente = urlencode($_GET['nom_cliente']);
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
              if($_SESSION['ver_factura']==1){    
              ?>   
                <button type='button' class="btn btn-primary btn-block" onClick="grabar_tarea('factura');" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;;margin-bottom:10px;'>FACTURA</button>
              <?php
              }
              if($_SESSION['ver_cobro']==1){    
              ?>   
                <button type='button' class="btn btn-primary btn-block" onClick="grabar_tarea('cobro');" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;;margin-bottom:10px;'>COBRO</button>
              <?php
              }
              if($_SESSION['ver_ot']==1){    
              ?>   
                <button type='button' class="btn btn-primary btn-block" onClick="grabar_tarea('ot');" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;;margin-bottom:10px;'>ORDEN DE TRABAJO</button>
              <?php
              }
              ?>
              <button type='button' class="btn btn-primary btn-block" onClick="window.history.back();" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;;margin-top:10px;;margin-bottom:10px;'>CANCELAR</button>
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
      echo "<script>window.location='tarea_factura.php?id=".$id."&nom_cliente=".$nom_cliente."'</script>";
    }else if($_SESSION['tipo_tarea']=='cobro'){
      $name_task="Factura";
      echo "<script>window.location='lista_cobros.php?id=".$id."&nom_cliente=".$nom_cliente."'</script>";
    }else if($_SESSION['tipo_tarea']=='ot'){
      $name_task="ot";
      echo "<script>window.location='lista_ordenes_trabajo.php?id=".$id."&nom_cliente=".$nom_cliente."'</script>";
    }else{
      $name_task="Tarea";
    }
    ?>

    <?php
    if(isset($_SESSION['tipo_tarea'])){
    ?>
    <div class="content" style='border:0px solid #000;'>   
      <div class="container"  style='border:0px solid #000;'>
        <div style="padding:10px 0px 10px 0px;border:0px solid #ccc;width:100%;">
          <div class='titulo'>
                <!-- <i class="fas fa-user-friends" style='font-size:50px;float:left;padding-right:10px;'></i> -->
                <!-- <span><strong>Módulo de Emisión<br /> de Pedidos y Presupuestos</strong></span> -->
                <div style="position:absolute; top:5px; left:0; auto"><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:15px;'></i></a></div>   
                <h5 style='color:#fff;'>GENERAR <?php echo strtoupper($name_task);?></h5><hr/>
          </div>
        </div>
        
      </div> <!-- fin container -->
    </div> <!-- fin content -->
    
        
      <div class="content" style='border:0px solid #000;'>   
        <div class="container"  style='border:0px solid #000;'>
        <div class='contenedor'> <!-- inicia el scroll -->
          <?php
          $codcliente              = str_replace("'", "''", $codcliente);
           $sql3="SELECT a.TIPREG, a.NOMBRE, a.CODIGO, a.RIF, a.NIT, a.CODVEN, a.TIPOCLI, a.DIRECC1, a.DIRECC2, a.NUMTEL, a.DIRCORREO, a.NUMERO_MOVIL, a.PORMAXDESPAR, a.PORMAXDESGLO 
          FROM BASECLIENTESPROVEEDORES  as a WHERE a.CODIGO='$codcliente' AND (a.TIPREG = 1)";

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
                    <input type="hidden" class="form-control" id='tipo_tarea' name='tipo_tarea' value="<?php echo $_SESSION['tipo_tarea'];?>" placeholder="" aria-label="nit" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='nit' name='nit' value="<?php echo $data2->NIT;?>" placeholder="" aria-label="nit" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='idcod' name='idcod' value="<?php echo $data2->CODIGO;?>" placeholder="" aria-label="codigo" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='nom_cli' name='nom_cli' value="<?php echo $data2->NOMBRE;?>" placeholder="" aria-label="nombre" aria-describedby="basic-addon1"> 
                    <input type="hidden" class="form-control" id='tipo_cli' name='tipo_cli' value="<?php echo $data2->TIPOCLI;?>" placeholder="" aria-label="tipocli" aria-describedby="basic-addon1"> 
                    <input type="hidden" class="form-control" id='dircli' name='dircli' value="<?php echo $data2->DIRECC1;?>" placeholder="" aria-label="dircli" aria-describedby="basic-addon1"> 
                    <input type="hidden" class="form-control" id='dircli2' name='dircli2' value="<?php echo $data2->DIRECC2;?>" placeholder="" aria-label="dircli2" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='numtel' name='numtel' value="<?php echo $data2->DIRECC2;?>" placeholder="" aria-label="numtel" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='maxdesglo' name='maxdesglo' value="<?php echo $data2->PORMAXDESGLO;?>" placeholder="" aria-label="maxdesglo" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='codcliente' name='codcliente' value="<?php echo $codcliente;?>" placeholder="" aria-label="codcliente" aria-describedby="basic-addon1">
                    <input type="hidden" class="form-control" id='nomcliente' name='nomcliente' value="<?php echo $nomcliente;?>" placeholder="" aria-label="nomcliente" aria-describedby="basic-addon1">

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

                    if(!isset($_SESSION['codalmacen_opt'])){
                      if(!isset($_SESSION['codalmacen'])){  
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
                    
                    //echo $sql3;

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
                <div><?php echo number_format(round($total, 2), 2);?></div></b>
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
                  <b><?php echo number_format(round($total_sum, 2), 2);?></b>
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

              <span id='detalle_desc_global'>

              </span>  
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
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                //alert(this.responseText);
                if(this.responseText==1){
                    $("#myModal").modal('hide');
                    if(dormir=='presupuesto'){
                      /*estamos en la misma pantalla de presupuesto*/
                      location.reload();
                    }else if(dormir=='pedido'){
                      /*estamos en la misma pantalla de pedido*/
                      location.reload();
                    }else if(dormir=='factura'){
                      /*estamos en la misma pantalla de pedido*/
                      location.reload();
                    }else if(dormir=='cobro'){
                      location.reload();
                    
                    }else if(dormir=='ot'){
                      location.reload();
                    }
                }else{
                    mensaje.innerHTML = "<label style='color:white;'>Error data!</label>";
                } 
            }
        };
        xhttp.open("GET", "grabar_tarea.php?tarea="+dormir, true);
        xhttp.send();
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
    //alert(resultar);
    if(resultar){
      $("#myModal_desc_global").modal();
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
    nom_cliente_tarea = document.getElementById("nom_cliente_tarea").value;
      //nom_cli=document.getElementById("nom_cli").value;
        nom_cli    = formatear_texto(document.getElementById("nom_cli").value);
        nit        = formatear_texto(document.getElementById("nit").value);
        tipo_cli   = document.getElementById("tipo_cli").value;
        dircli     = formatear_texto(document.getElementById("dircli").value);
        dircli2    = formatear_texto(document.getElementById("dircli2").value);
        numtel     = document.getElementById("numtel").value;
        obv        = formatear_texto(document.getElementById("obv").value);
        almacen    = formatear_texto(document.getElementById("almacen").value);
        vendedor   = formatear_texto(document.getElementById("vendedor").value);
    var tipo_tarea = document.getElementById("tipo_tarea").value;
    var link = "grabar_presupuesto";
    if(tipo_tarea == "factura")
      link = "grabar_factura";
    //alert(nom_cli+" jhshsdjd");
    /*const mensaje = document.getElementById("detalle_desc_global");
    mensaje.innerHTML = "";*/
    
    swal({
          title: 'AUTORIZANDO PEDIDO',
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
            $("#myModal_desc_global").modal('hide');
            const myArr = this.responseText.split("|");
            if(myArr[0].trim()=='1'){
              cal_contro_uni=myArr[1];
              nro_correlativo=myArr[2];
              tip_tran=myArr[3];
              const imageURL = "imgs/icono_gancho.png";
              swal("Procesado!", "Tarea Realizada Exitosamente!", "success", {
                button: {
                  text: "Aceptar",
                  className: "btn btn-primary d-block mx-auto", // Clases para centrar el botón
                },
                icon: 'success',
                closeOnClickOutside: false,
              })
              .then((value) => {
                if (value) {
                  window.location.href='doc_pdf_demo.php?idcontrol='+cal_contro_uni+'&idfac='+nro_correlativo+'&tiptran='+tip_tran;; // Reemplaza con la URL de destino
                }
              });
              //swal("Perfecto!", "Tarea Realizada Exitosamente!", "success");
              /*$("#Modal_Confirm").modal();
              $('#msg_confirm2').html("Tarea Realizada");
              $('#msg_confirm').html("Exitosamente");*/
              //setTimeout(function(){ window.location='doc_pdf_demo.php?idcontrol='+cal_contro_uni+'&idfac='+nro_correlativo+'&tiptran='+tip_tran; }, 2000);
              //window.location='doc_pdf_demo.php?idcontrol='+cal_contro_uni+'&idfac='+nro_correlativo+'&tiptran='+tip_tran;
            }else{
              // Mostrar mensaje de error
                swal("Error!", "Error al ejecutar el proceso", "error", {
                    button: {
                        text: "Aceptar",
                        className: "btn btn-primary d-block mx-auto",
                    },
                    icon: 'error',
                    closeOnClickOutside: false,
                });
              document.getElementById("detalle_desc_global").innerHTML="<br />"+myArr[1];
            }
          }
        };
        xhttp.open("GET", link+".php?desc_global="+saveme+"&idcod="+idcod+"&nom_cli="+nom_cli+"&nit="+nit+"&tipo_cli="+tipo_cli+"&dircli="+dircli+"&dircli2="+dircli2+"&numtel="+numtel+"&almacen="+almacen+"&vendedor="+vendedor+"&nom_cliente_tarea="+nom_cliente_tarea+"&obv="+obv, true);
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
    if(descglobal!=''){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                  //alert(this.responseText);
                  const myArr = this.responseText.split("|");
                  if(myArr[0]==1){               
                    document.getElementById("detalle_desc_global").innerHTML="<br /> Descuento: "+descglobal;
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
  </script>
  
</body>
</html> 
