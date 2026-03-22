<?php
include_once "permiso.php";
include_once "config/db.php";

$codcliente=str_replace("%23", "#", $_GET['id']);
$nomcliente=str_replace("%23", "#", $_GET['nom_cliente']);

$format_nomcliente=str_replace("#", "%23", $nomcliente);
$format_codcliente=str_replace("#", "%23", $codcliente);

function cadena_control()
{
    /*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
    usleep(100000);
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime(date("Y-m-d H:i:s"));

    $diff = $fecha1->diff($fecha2);
    $dias = $diff->days;
    $fecha_actual_clarion = $diff->days;
    $fecha_actual_ymd = date("Ymd");

    /*generando hora actual en codigo clarin*/
    /*$aux =  microtime(true);
    $now = DateTime::createFromFormat('U.u', $aux);        
    if (is_bool($now)){
        $now = DateTime::createFromFormat('U.u', $aux += 0.001);
    }*/
    //$now = DateTime::createFromFormat("U.u", microtime(true));
    //$hora_actual = ($now->format("H")*360000)+($now->format("i")*6000)+($now->format("s")*100)+($now->format("u")*10)+1;
    $hora_actual = (date('H') * 360000) + (date("i") * 6000) + (date("s") * 100) + (date("v") / 10) + 1;
    if (strlen($hora_actual) == 7) {
    } else if (strlen($hora_actual) > 7) {
        $hora_actual = substr($hora_actual, 0, 7);
    } else if (strlen($hora_actual) < 7) {
        $res = 7 - strlen($hora_actual);
        for ($t = 1; $t <= $res; $t++) {
            $hora_actual = "0" . $hora_actual;
        }
    }

    /*generando numero aleatorio entre 10000 y 99999*/
    $aleatorio = mt_rand(10000, 99999);

    return "$dias|$hora_actual|$aleatorio|$fecha_actual_clarion|$fecha_actual_ymd";
}
$res_cadena  = cadena_control();

$exp_control = explode('|', $res_cadena);

$dias                 = $exp_control[0];
$hora_actual          = $exp_control[1];
$aleatorio            = $exp_control[2];
$fecha_actual_clarion = $exp_control[3];
$fecha_actual_ymd     = $exp_control[4];
$hora_actual_clarion  = $exp_control[1];
$control_ot = "$dias$hora_actual$aleatorio" . "01";

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
<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css" >
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"  crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="jquery/popper.min.js" ></script>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script> -->
<script src="bootstrap2/js/bootstrap.min.js" ></script>
<!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous"> -->
<!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
 <link href='https://fonts.googleapis.com/css?family=Gloria+Hallelujah' rel='stylesheet' type='text/css'> -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
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
    max-width: 95%;
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
.fila {
  display: flex;
  flex-wrap: wrap;
}

.column-4 {
  flex-basis: 33.33%;
  max-width: 33.33%;
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
          <form>
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
            ?>
            
            <div style='background-color:#939598;color:#fff;height:auto;border-radius:10px 10px 0px 0px;padding:8px 8px 8px 8px;'> Datos del Cliente: <b><?php echo $data2->CODIGO;?></b> <b style='font-size:20px;'>|</b> R.U.C: <b><?php echo $data2->RIF;?></b> <b style='font-size:20px;'>|</b> Desc. Global: <b><?php echo $data2->PORMAXDESGLO;?></b></div>
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
                  </a></b><br>
                  <b>Teléfono:
                    <?php echo $data2->NUMTEL;?>
                  </a></b><br>
                  
                  <b>Email:
                    <?php echo $data2->DIRCORREO;?>
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
                    <span style="color:white;">Vendedor</span>
                  <select class="browser-default custom-select" id="vendedor" name="vendedor" onchange="grabar_vend_alma(this.selectedIndex, 'vendedor');">
                    
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
                    $vendedor = "";
                    while ($data2 = $sentencia4->fetchObject()){
                      //$pormaxdespar=$data2->PORMAXDESPAR;
                      if(($_SESSION['codvendedor_opt']==$data2->CODVEN) OR ($_SESSION['codvendedor']==$data2->CODVEN)){
                        echo "<option value='".$data2->CODVEN."|".$data2->NOMBRE."' selected>".$data2->CODVEN."|".$data2->NOMBRE."</option>";
                        $vendedor = $data2->CODVEN."|".$data2->NOMBRE;
                      }else{
                        echo "<option value='".$data2->CODVEN."|".$data2->NOMBRE."'>".$data2->CODVEN."|".$data2->NOMBRE."</option>";
                      }
                      
                    }
                    
                    ?>
                  </select>
                </div>
                <div id="elemento" style='padding-left:5px;'>
                    <span style="color:white;">Responsable</span>
                  <select class="browser-default custom-select" id="responsable" name="responsable">
                    <?php
                    
                    $sql3="SELECT CODVEN, NOMBRE FROM BASEVENDEDORES";
                        //$opt="<option value='' disabled selected hidden>Vendedor</option>";
                      
                    
                      /*recuperando todo los productos comandados*/
                      
                      //echo $sql3;
  
                      $sentencia4 = $base_de_datos->prepare($sql3, [
                      PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                      ]);
  
                      $sentencia4->execute();
                      echo $opt;
                      echo "<option value=''>Selecciones</option>";

                      while ($data2 = $sentencia4->fetchObject()){
                        //$pormaxdespar=$data2->PORMAXDESPAR;
                        echo "<option value='".$data2->CODVEN."|".$data2->NOMBRE."'>".$data2->CODVEN."|".$data2->NOMBRE."</option>";

                        
                      }
                    ?>
                  </select>
                </div>
       </div>
        <br />
        
        
        <div id="xxxx">
            
            
            <div style='background-color:#939598;color:#fff;height:auto;border-radius:10px 10px 0px 0px;padding:8px 8px 8px 8px;'> Datos del Contacto:</div>
            <div style='background-color:#fff;color:#000;height:auto;border-radius:0px 0px 10px 10px;padding:10px 8px 8px 8px;'>
                <div class="row">
                    <label class="col-1">#OT</label>
                    <div class="col-3">
                        <input class="form-control" id="num_ot" name="num_ot" placeholder="#ot" value="<?= $control_ot;?>" readonly>
                    </div>
                    <label class="col-1">#control</label>
                    <div class="col-3">
                        <input class="form-control" id="num_control" name="num_control" placeholder="#control" value="<?= $_GET["control"]; ?>" readonly>
                    </div>
                    <label class="col-1">Atendido</label>
                    <div class="col-3">
                        <input class="form-control" id="atendido" name="atendido" placeholder="Atendido"  value="<?= $vendedor; ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">&nbsp;</div>
                </div>
                <div class="row">
                    <label class="col-1">Fec. Entrada</label>
                    <div class="col-3">
                            <div class="input-group date" data-provide="datepicker">
                            <input type="text" class="form-control" name="fecha_entrada" id="fecha_entrada" placeholder="Fecha Entrada">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                    </div>
                    <label class="col-1">Fec. Entrega</label>
                        <div class="col-3">
                            <div class="input-group date" data-provide="datepicker">
                            <input type="text" class="form-control" name="fecha_entrega" id="fecha_entrega" placeholder="Fecha Entrega">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                    </div>
                    <label class="col-1">Resp.</label>
                    <div class="col-3">
                        <input class="form-control" id="responsable" name="responsable" placeholder="Responsable">
                    </div>
                </div>
            </div>
        </div> 
        <br />
        
        
        <div id="mensaje_error"></div> 
            <div class="text-right">
              <button type="button" class="btn btn-secondary addProduct" style="background-color:#E6E7E9 !important;color:#000 !important;border:none !important;" ><i class="fa-solid fa-plus"></i> <b>AGREGAR PRODUCTOS</b></button>
              

            </div>
        <br />
        <div class="container" style="background-color:white;">
        <table id="miTabla" class="table">
        <thead>
            <tr>
            <th>Cantidad</th>
            <th>Descripción</th>
            <th>Tamaño</th>
            <th>Material</th>
            <th>N° Caras</th>
            <th>Color</th>
            <th>Acabado</th>
            <th>Observaciones</th>
            <th></th>
            </tr>
        </thead>
        <tbody>
            <!-- Aquí se añadirán las filas -->
        </tbody>
        </table>
        </div>
      
        
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
                <br />
                <div>
                  <button type="buttom" id="botonEnviar" class="btn btn-primary" style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important;'><i class="fa-solid fa-receipt"></i>&nbsp;<b>GUARDAR ORDEN DE TRABAJO</b></button>&nbsp;
                </div><br /><br />
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
          <form>
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
		$(document).ready(function($) {
			$("#loading").hide();
		});
	</script>
  <script>

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
    total_factura = document.getElementById("total_factura").innerHTML;
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
                              <label class="col-3 col-sm-3 col-md-3 col-lg-3 text-left" for="input3">Total:</label>
                              <div class="col-9 col-sm-9 col-md-9 col-lg-9">
                                <input type="text" id="input3" placeholder="Input 3" class="form-control text-right" value='${total_factura}' readonly>
                              </div>
                            </div>
                            <div class="row mt-2">
                              <label class="col-3 col-sm-3 col-md-3 col-lg-3 text-left" for="input1">Pagado:</label>
                              <div class="col-9 col-sm-9 col-md-9 col-lg-9">
                                <input type="text" id="input1" placeholder="Input 1" class="form-control text-right" value='${parseFloat(pagado).toFixed(2)}' readonly>
                              </div>
                            </div>
                            <div class="row mt-2">
                              <label class="col-3 col-sm-3 col-md-3 col-lg-3 text-left" for="input2">Cambio:</label>
                              <div class="col-9 col-sm-9 col-md-9 col-lg-9">
                                <input type="text" id="input2" placeholder="Input 2" class="form-control text-right" value='${cambio}' readonly>
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
        window.location='form_producto_ot.php?id='+idcod+'&nom_cliente='+nom_cli+'&CodAlmacen='+almacen+"&CodVen="+vendedor+"&NomVend="+nomvendedor;
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
                  
                  
                  
                  
                  document.getElementById("subtotal").innerHTML=(obj.subtotal_sum).toFixed(2);
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

  $(document).ready(function(){
    var fechaActual = new Date();


    // Inicializar el segundo campo de fecha con la fecha calculada
      $('[data-provide="datepicker"]').datepicker({
        autoclose: true, // Cerrar automáticamente al seleccionar una fecha
        setDate: fechaActual, // Cerrar automáticamente al seleccionar una fecha
        format: 'yyyy-mm-dd' // Formato de fecha deseado


      });
    });

    $(".addProduct").off().on("click", function(){
      $("#modalOt").modal("show");
    }); 
    $(document).ready(function() {
        $('#guardar').on('click', function() {
            var cantidad = $('#cantidad').val();
            var descripcion = $('#descripcion').val();
            var tamanio = $('#tamanio').val();
            var material = $('#Material').val();
            var caras = $('#caras').val();
            var color = $('#color').val();
            var acabado = $('#acabado').val();
            var observacion = $('#observacion').val();

            var nuevaFila = $('<tr>').append(
            $('<td data-id="cantidad">').text(cantidad),
            $('<td data-id="descripcion">').text(descripcion),
            $('<td data-id="tamanio">').text(tamanio),
            $('<td data-id="material">').text(material),
            $('<td data-id="caras">').text(caras),
            $('<td data-id="color">').text(color),
            $('<td data-id="acabado">').text(acabado),
            $('<td data-id="observacion">').text(observacion),
            $('<td>').html('<button class="btn btn-danger eliminar">Eliminar</button>') // botón de eliminar

            );

            $('#miTabla tbody').append(nuevaFila);

            // Limpiar los campos del formulario y cerrar el modal
            $('#modalOt').find('input,textarea,select').val('');
            $('#modalOt').modal('hide');
        });
    });
    // Función para convertir los datos de la tabla a un arreglo de objetos
    function tablaAJson() {
        var datos = [];
        $('#miTabla tbody tr').each(function() {
            var fila = {};
            $(this).find('td').each(function(i) {
              var key = $(this).data('id');
              var value = $(this).text();
              fila[key] = value;
            });
            datos.push(fila);
        });
        return datos;
    }

    // Función para enviar los datos usando AJAX
    function enviarDatos() {
        var datos = tablaAJson();
        var vendedor = $("#vendedor").val();
        var responsable = $("#responsable").val();
        var num_ot = $("#num_ot").val();
        var num_control = $("#num_control").val();
        var atendido = $("#atendido").val();
        var fecha_entrada = $("#fecha_entrada").val();
        var fecha_entrega = $("#fecha_entrega").val();
        var responsable = $("#responsable").val();

        var datosform = new FormData();

        datosform.append("vendedor", vendedor);
        datosform.append("responsable", responsable);
        datosform.append("num_ot", num_ot);
        datosform.append("num_control", num_control);
        datosform.append("atendido", atendido);
        datosform.append("fecha_entrada", fecha_entrada);
        datosform.append("fecha_entrega", fecha_entrega);
        datosform.append("responsable", responsable);
        datosform.append("productos", JSON.stringify(datos));

        var idcod = $("#idcod").val();
        var nom_cli = $("#nom_cli").val();
        
        $.ajax({
            url: 'ajax/guardar_ot.php', // Cambia esto por la URL a la que quieras enviar los datos
            type: 'POST',
            contentType: 'application/json',
            contentType: false, // Esto debe ser false si estás enviando un FormData
            processData: false, // Esto debe ser false si estás enviando un FormData
            data: datosform,
            success: function(response) {
            // Aquí puedes manejar la respuesta del servidor
              swal({
                  title: 'Éxito',
                  text: 'Datos de prueba insertados correctamente.',
                  icon: 'success',
                  button: 'Aceptar'
              }).then(function(result){
                if(result){
                    window.location='lista_ordenes_trabajo.php?id='+idcod+'&nom_cliente='+nom_cli
                  }
              });
            },
            error: function(error) {
            // Aquí puedes manejar cualquier error que ocurra durante el envío
            swal({
                title: 'Error',
                text: 'Ha ocurrido un error al insertar los datos de prueba.',
                icon: 'error',
                button: 'Aceptar'
            });
            }
        });
    }

    // Enviar los datos cuando se haga click en un botón
    $('#botonEnviar').on('click', function() {
        
        swal({
            title: "¿Está seguro que agregar esta orden?",
            text: "¡Si no lo está puede cancelar está acción!",
            type: "warning",
            buttons: true,
            showCancelButton: true,
            confirmButtonColor: "#007bff",
            cancelButtonColor: "#dc3545",
            confirmButtonText: "Agregar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false,
            closeOnCancel: true
        }).then(function(result){
            if(result){
                enviarDatos();
            }
        });
    });

    $(document).on('click', '.eliminar', function() {
        $(this).closest('tr').remove();
    });


  </script>
  
</body>
<!-- El modal -->
<div class="modal fade" id="modalOt" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <!-- Cabezera del modal -->
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Mi Modal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Cuerpo del modal -->
      <div class="modal-body">
        <form>
          <div class = "row">

            <div class="col-3 col-md-3">
              <label for="campo1">Cantidad1</label>
              <input type="text" class="form-control" id="cantidad">
            </div>

            <div class="col-3 col-md-3">
              <label for="campo2">Descripción</label>
              <textarea type="text" class="form-control" id="descripcion" name="descripcion"></textarea>
            </div>

            <div class="col-3 col-md-3">
              <label for="campo3">Tamaño</label>
              <input type="text" class="form-control" id="tamanio" name="tamanio">
            </div>

            <div class="col-3 col-md-3">
              <label for="campo4">Material</label>
              <input type="text" class="form-control" id="Material" name = "material">
            </div>
          </div>
          <div class = "row">
            <div class="col-3 col-md-3">
              <label for="campo6">N° Caras</label>
              <input type="text" class="form-control" id="caras" name = "caras">
            </div>
            <div class="col-3 col-md-3">
              <label for="campo5">Color</label>
              <select class="form-control" id="color" name="color">
                <option value="">Seleccione</option>
                <option value="Blanco y Negro">Branco y Negro</option>
                <option value="color">Color</option>
              </select>
            </div>

            <div class="col-3 col-md-3">
              <label for="campo6">Acabado</label>
              <input type="text" class="form-control" id="acabado">
            </div>
          </div>
          <div class = "row">
            <div class="col-12 col-md-12">
              <label for="campo2">Observaciones</label>
              <textarea type="text" class="form-control" id="observacion"></textarea>
            </div>

          </div>

        </form>
      </div>

      <!-- Pie del modal -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="guardar">Guardar</button>
      </div>

    </div>
  </div>
</div>
</html> 
