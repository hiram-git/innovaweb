<?php
include_once("permiso.php");
include_once "config/db.php";
if(!isset($_GET['input_buscar'])){
  $input_buscar="";
}else{
  $input_buscar=formatear_texto($_GET['input_buscar']);
}
function formatear_texto($texto){
  $texto=str_replace("%23", "#", $texto);
  $texto=str_replace("%26", "&", $texto);
  $texto=str_replace("%27", "'", $texto);
  return $texto;
}

//echo $input_buscar;
$max=sizeof($_SESSION['aDatos']);
if(isset($_SESSION['id_control'])){  
  if($max>0){
    $img_url="imgs/fondo2.png";
    $opacidad=0.8;
  }else{
    $img_url="imgs/fondo2.png";
    $opacidad=0;
  }
  
}else if($max>0){
  $img_url="imgs/fondo2.png";
  $opacidad=0.8;
}else{
  $img_url="imgs/fondo.jpg";
  $opacidad=0;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css">
<!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
<script src="jquery/jquery-3.2.1.slim.min.js"></script>
<script src="jquery/popper.min.js"></script>
<script src="bootstrap2/js/bootstrap.min.js"></script>
<!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous"> -->
<!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous"> -->
<!-- <link href="https://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet"> -->
<!--<link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">-->
<link href="fontawesome-free-6.2.1/css/all.css" rel="stylesheet">
<!-- <link rel="stylesheet" type="text/css" href="autocomplete/mack.css">
 <script type="text/javascript" src="autocomplete/autocomplete.js"></script>  -->
  <title>
    <?php echo $_SESSION['titulo_web'];?>
  </title>
<style>
<?php
if(isset($_SESSION['id_control'])){
?>
  .modal-backdrop.show {
    opacity: 0.8 !important;
    filter: alpha(opacity=80); /* Para versiones anteriores de IE */
    }
  /*
  .modal {
    top: 0px !important;
  }*/
/*
  .modal-dialog {
    margin: 1.5rem !important;

  }*/

  .modal-content {
    border-radius: 0.5rem;
    border: 0px solid rgba(0,0,0,.2);

  }

  .modal {
    /*left: 0% !important;*/
  }
<?php  
}else{
?>
.modal-backdrop.show {
  opacity: <?php echo $opacidad;?> !important;
  filter: alpha(opacity=80); /* Para versiones anteriores de IE */
}

.modal {
  /*top: 220px !important;*/
  position:relative !important;
}
<?php
}
?>
body { 
  border: 0px solid black;
  padding: 0px;
  background: url(<?php echo $img_url;?>) no-repeat fixed center;
  background-repeat: no-repeat;
  /*background-size: 100%;*/
  background-size: cover;
  background-color:#BCBDC0;
}

.form {
    position: relative
}

.form .fa-search {
    position: absolute;
    top: 4px;
    left: 1px;
    color: #fff;
}

.form span {
    position: absolute;
    right: 5px;
    top: 0px;
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

.caja{

  border:0px solid black;
  width:100px;
  background-color:#BEBFC1;
  padding:5px 5px 5px 5px;
  border-radius: 6px;
  text-align:center;
  font-size:11px;
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
.container {
    padding-right: 5px !important;
    padding-left: 5px !important;
}

.btn{
  background-color:#FF5001 !important;
  color:#fff !important;
  border:none !important; 
  width:100px;
}

.titulo{
  text-align:center;color:#fff;font-size:1.2rem;
}

@media only screen and (min-width:320px) and (max-width:480px){
  .contenedor {
    height: 450px; /*altura de scroll segun altura de panatalla*/
  }
  .titulo{
    font-size:1.42rem;
  }
}

@media only screen and (min-width:768px){
  .contenedor {
    height: 750px; /*altura de scroll segun altura de panatalla*/
  }

  .btn{
    border:1px solid #fff !important;
    background-color:#fff !important;
    color:#000 !important;
  }

  .titulo{
    font-size:1.45rem;
  }
  
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

</style>
</head>
<body>
  <header>
    <?php
  /***************************/
/*extrayendo version*******
$version ="";
$exists = is_file("version.txt");
if($exists){
  $fp = fopen("version.txt", "r");
  while (!feof($fp)){
      $version = fgets($fp);
      $explinea=explode('|',$version);
  }
  fclose($fp);
}
/***************************/

$version = '1.0.0'; // Valor por defecto si no se encuentra en changelog.md

$changelogFile = 'changelog.md';

if (file_exists($changelogFile)) {
    $lines = file($changelogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^##\s*(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
            $version = $matches[1]; // 2026-01-13
            break; // salimos en la primera coincidencia (la más reciente)
        }
    }
}
?>
  </header>
<main>
<!-- Modal -->
<div id="modalform_product" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content" >
      <!--<div class="modal-header">
         <button type="button" class="close" data-dismiss="modal">&times;</button> 
        <i class="fa fa-list-alt" aria-hidden="true" style='font-size:65px;color:#fa6630;'></i>&nbsp;&nbsp;&nbsp;
        <h4 class="modal-title">Aplicar Descuento Global y Totalizar</h4>
      </div> -->
      <form>
        <div class="modal-body">  
          <div style="overflow:hidden;width:100%;">
            <div>
            <input type="text" class="form-control" id='txt_buscar' name='txt_buscar' value="" placeholder="Buscar..." >
            </div>
            <br />
            <div>         
            Almacen
            <select class="browser-default custom-select" id="almacen" name="almacen">
                    <?php
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
                      $opt="";
                      //$opt="<option value='' disabled selected hidden>Vendedor</option>";
                    } 

                    $sentencia4 = $base_de_datos->prepare($sql3, [
                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                    ]);

                    $sentencia4->execute();
                    echo $opt;
                    while ($data3 = $sentencia4->fetchObject()){
                      if(($_SESSION['codalmacen_opt']==$data3->CODIGO) OR ($_SESSION['codalmacen']==$data3->CODIGO)){
                        echo "<option value='".$data3->CODIGO."' selected>".$data3->CODIGO."-".$data3->NOMBRE."</option>";
                      }else{
                        echo "<option value='".$data3->CODIGO."'>".$data3->CODIGO."-".$data3->NOMBRE."</option>";
                      }
                    }
                    ?>
                  </select>
                  </div>
            <br />
                  <div>
                  <?php
                  $sql33="SELECT PRECIOVENTAD,MONEDA FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
                  $sentencia43 = $base_de_datos->prepare($sql33, [
                  PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                  ]);
      
                  $sentencia43->execute();
                  while ($data23 = $sentencia43->fetchObject()){
                    /*como el precio es libre se procede a buscar en la tabla BASEEMPRESA el precio default*/
                    
                    $_SESSION["precio_venta_emp"] = $data23->PRECIOVENTAD;
                    $precio_default=$data23->PRECIOVENTAD;
                    $MONEDA=$data23->MONEDA;
                  }

              $arr_precio=array('libre', 1, 2, 3, 4, 5);
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
                          <input type="radio" onclick="document.getElementById('precio').disabled = true;extraer_precio('precio<?php echo $k_pre;?>', input_buscar.value);" style='width: auto !important;' name="precio_" id="precio_<?php echo $k_pre;?>" value='precio<?php echo $k_pre;?>' checked /> <b>Precio<?php echo $k_pre;?></b>
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

                  if($k==2){
                    echo "<br />";
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
                  if($k==2){
                    echo "<br />";
                  }
                  $k++;
                }
                echo "</div>";
              }else if($_SESSION['usuario_precio']=='no_definido'){
                /*$sql33="SELECT PRECIO FROM BASECLIENTESPROVEEDORES WHERE CODIGO='".$codcliente."'";
                $sentencia43 = $base_de_datos->prepare($sql33, [
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

                $sentencia43->execute();
                while ($data23 = $sentencia43->fetchObject()){*/
                  /*como el precio es libre se procede a buscar en la tabla BASEEMPRESA el precio default*/
                  /*$precio_cli_prov=$data23->PRECIO;
                  if($precio_cli_prov==0){*/
                    $precio_cli_prov=$precio_default;
                  /*}
                }*/
                $k=1;
                echo "<div class='padre' style='border:0px solid #ccc;'>";
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
                  if($k==2){
                    echo "<br />";
                  }
                  $k++;
                }
                echo "</div>";

                
              }
              ?>
                  </div>
                <br />
            <div>
              <!-- <button type='button' class="btn btn-primary btn-block" onClick="calcular_total();" style='background-color:#E6E7E9 !important;color:#000 !important;border:1px solid #E6E7E9 !important;width:98%;'>Aplicar Descuento</button> -->
              <button type='button' class="btn btn-primary btn-block" onClick="buscar_prod(txt_buscar.value, almacen.value);" style='background-color:#FF5001 !important;color:#fff !important;border:1px solid #FF5001 !important;width:100%;'><b>BUSCAR PRODUCTO</b></button>
            </div>
          </div>
          
        </div>

          <br />
          <!-- <div id='layer_prod'></div> -->

      </form>
    </div>

  </div>
</div>
<!-- Modal -->  

    <!-- Modal -->
    <div id="mymodelprod" class="modal fade" role="dialog">
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
if(isset($_SESSION['id_control'])){  
?>
  <div class="content" style='border:0px solid #000;'>   
    <div class="container"  style='border:0px solid #000;'>
      <div style='padding:10px 0px 10px 0px;border:0px solid #ccc;width:100%;'>
        <div class='titulo'>
              <span><strong>Módulo de Emisión<br /> de Pedidos y Presupuestos</strong></span>
        </div>
      </div>
      <form>
            <div class="row height d-flex justify-content-center align-items-center">
                <div class="col-md-6">
                    <div class="form">
                      <input type="text" class="form-control form-input" placeholder="Buscar...." autocomplete="off" name="input_buscar" id="input_buscar" size='100%'> <a onClick="buscar(input_buscar.value);"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x" style='color:#0CB3F9;font-size:45px;'></i><i class="fa fa-search fa-stack-1x fa-inverse" style='font-size:20px;'></i></span></a>
                    </div>
                </div>
            </div>
      </form>
      <br />
      <div class="content" style='border:0px solid #ccc;display: flex;margin: 2px;justify-content: center;flex-wrap: wrap;'>
        <button class="btn" style='background-color:#fff !important;border:none;color:#000!important;auto; margin: 2px;' type="button" id="clean" onClick="input_buscar.value='';"><b>Borrar</b></button>
        <?php
        
          if($_SESSION['creacliente']==1){
            $habilitar="";
            $style_habilitar="pointer-events: auto;";
          }else{
            $habilitar="disabled";
            $style_habilitar="pointer-events: none;";
          }
        ?>
        <?php if($MONEDA != "RD$") { ?>

        <a href='form_nuevo_cliente.php' style="<?php echo $style_habilitar;?>">
          <button class="btn" style='background-color:#fff !important;color:#000!important;border:none;width:auto; margin: 2px;' type="button" <?php echo $habilitar;?>><b>Crear Cliente</b></button>
        </a>
        <?php } ?>
        <button class="btn" style='background-color:#fff !important;border:none;color:#000!important;width:auto; margin: 2px;' type="button" id="b_producto" onClick="modal_buscar_prod();">
        <i class="fa fa-search" style='color:#ccc;font-size:18px;'></i>
        <b>Productos</b></button>
        <?php if($_SESSION["ver_factura"] == 1) { ?>
        <button class="btn" style='background-color:#fff !important;border:none;color:#000!important;width:auto; margin: 2px;' type="button" id="b_producto" onClick="location.href='facturacion_electronica.php';">
        <i class="fa fa-file-invoice-dollar" style='color:#ccc;font-size:18px;'></i>
        <b>Fact. Elec.</b></button>
        <?php } ?>
      </div>
    </div> <!-- fin container-->
  </div> <!-- fin content-->

<div id='layer_clientes' style='border:0px solid #000;'>
    <div id='layer_container' class="container"  style='border:0px solid #000;'>
    <?php
      
      if(($input_buscar!='') AND ($input_buscar!='*')){
        $cad_sql="
DECLARE @Busqueda Nvarchar(max)
DECLARE  @key VARCHAR(80)
DECLARE  @keys int
DECLARE  @conteo int
DECLARE  @tipo varchar(1)
DECLARE	 @string    nvarchar(MAX)
DECLARE  @separator nvarchar(MAX)


SET @key=0
SET @keys=0
SET @conteo=0
SET  @tipo=0
SET @string='$input_buscar'
SET @separator='*'

SET NOCOUNT ON;
declare @keywords cursor

set @keywords = CURSOR FOR
WITH X(N) AS (SELECT 'Table1' FROM (VALUES (0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0),(0)) T(C)),
Y(N) AS (SELECT 'Table2' FROM X A1, X A2, X A3, X A4, X A5, X A6, X A7, X A8) ,
T(N) AS (SELECT TOP(ISNULL(LEN(@string),0)) ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) -1 N FROM Y),
Delim(Pos) AS (SELECT t.N FROM T WHERE (SUBSTRING(@string, t.N, LEN(@separator+'x')-1) LIKE @separator OR t.N = 0)),
Separated(value) AS (SELECT SUBSTRING(@string, d.Pos + LEN(@separator+'x')-1, LEAD(d.Pos,1,2147483647) OVER (ORDER BY (SELECT NULL)) - d.Pos - LEN(@separator))
FROM Delim d
WHERE @string IS NOT NULL)
SELECT s.value,COUNT(*) OVER () as Keys
FROM Separated s
WHERE s.value <> @separator



OPEN @keywords

FETCH NEXT FROM @keywords
INTO @key,@keys

WHILE @@FETCH_STATUS = 0
BEGIN
SET @Conteo=@Conteo+1
SET  @Busqueda = IIF(@Conteo=1,'',@Busqueda)

SET  @Busqueda = CONCAT( @Busqueda , ' (NOMBRE LIKE ''%'+@key+'%'' OR   CODIGO LIKE ''%'+@key+'%'') ')
SET  @Busqueda = @Busqueda +IIF((@conteo=@keys),'',' AND ')

FETCH NEXT FROM @keywords
INTO @key,@keys
END
CLOSE @keywords;
DEALLOCATE @keywords;


EXEC ('SELECT TIPREG, NOMBRE, NOMBRE1, NOMBRE2, APELLIDO1, APELLIDO2, TIPOCOMERCIO, CODIGO, FECHA1S, NUMTEL, DIRCORREO, NUMERO_MOVIL, NUMTELCONTACTO, NOMBREGERENTE 
FROM BASECLIENTESPROVEEDORES
WHERE (TIPREG = 1 AND INTEGRADO=0) AND '+@Busqueda+' ORDER BY CODIGO ASC'
)
";
        //$cad_sql="SELECT a.TIPREG, a.NOMBRE, a.CODIGO, a.FECHA1S, a.NUMTEL, a.DIRCORREO, a.NUMERO_MOVIL, a.NUMTELCONTACTO, a.NOMBREGERENTE FROM BASECLIENTESPROVEEDORES  as a WHERE (a.TIPREG = 1) AND (a.NOMBRE LIKE '%$input_buscar%' OR a.CODIGO LIKE '%$input_buscar%') ORDER BY a.CODIGO DESC";
      }else{
        $AND = "";
        if((isset($_SESSION['valcliente']) and $_SESSION['valcliente']!="") and $_SESSION['actcliente'] == "0"){
          $arrayNumeros = explode(',', $_SESSION['valcliente'] );

          // Coloca comillas simples alrededor de cada número
          $arrayConComillas = array_map(function($numero) {
              return "'" . $numero . "'";
          }, $arrayNumeros);
          
          // Une el array nuevamente en una cadena
          $cadenaConComillas = implode(',', $arrayConComillas);
          
          $AND = " AND codigo IN (" . $cadenaConComillas . ") ";
        }
        if((isset($_SESSION['codvendedor']) and $_SESSION['codvendedor']!="")){
          $arrayVendedores = explode(',', $_SESSION['codvendedor'] );

          // Coloca comillas simples alrededor de cada número
          $arrayComillas = array_map(function($numero) {
              return "'" . $numero . "'";
          }, $arrayVendedores);
          
          // Une el array nuevamente en una cadena
          $stringConComillas = implode(',', $arrayComillas);
          
          $AND = " AND CODVEN IN (" . $stringConComillas . ") ";
        }
        $cad_sql="SELECT TOP(5) a.TIPREG, a.NOMBRE, a.NOMBRE1, a.NOMBRE2, a.APELLIDO1, a.APELLIDO2, a.CODIGO, a.FECHA1S, a.NUMTEL, a.DIRCORREO, a.NUMERO_MOVIL, a.NUMTELCONTACTO, a.NOMBREGERENTE, a.TIPOCOMERCIO
        FROM BASECLIENTESPROVEEDORES  as a 
        WHERE (a.TIPREG = 1 AND INTEGRADO=0)  
        $AND
        ORDER BY a.CODIGO DESC";
      }
      /*recuperando todo los productos comandados*/
      $sql3="$cad_sql";

      $sentencia4 = $base_de_datos->prepare($sql3, [
      PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
      ]);

      $sentencia4->execute();
    ?>
    
      <div id='layer_scroll' class='contenedor'>
      <?php
      while ($data_cliente = $sentencia4->fetchObject()){
        /*$sql = "SELECT COUNT(*) total FROM TRANSACCMAESTRO WHERE CODIGO='$data2->CODIGO'";
        $result = $base_de_datos->query($sql); //$pdo sería el objeto conexión
        $total_reg = $result->fetchColumn();*/

        $codcli              = str_replace("'", "''", $data_cliente->CODIGO);

        $sql = "SELECT COUNT(CONTROL) AS total, TIPTRAN FROM TRANSACCMAESTRO WHERE (CODIGO = '$codcli') GROUP BY TIPTRAN";
        //echo "$sql";
        $result = $base_de_datos->query($sql); //$pdo sería el objeto conexión
        $total_reg = $result->fetchColumn();
        $tot_pre=0;
        $tot_ped=0;
        $tot_fact=0;
        if($total_reg!=''){
            $sentencia44 = $base_de_datos->prepare($sql, [
            PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
            ]);
                    
            $sentencia44->execute();

            while ($data22 = $sentencia44->fetchObject()){
              if($data22->TIPTRAN=='PRE'){
                $tot_pre=$data22->total;
              }else if($data22->TIPTRAN=='PEDxCLI'){
                $tot_ped=$data22->total;
              }else if($data22->TIPTRAN=='FAC'){
                $tot_fact=$data22->total;
              }
            }
        }
      ?>
        
        <!--<div id='layer_table' class='d-flex flex-row justify-content-between mb-3' style='background-color:#fff;border:0px solid #fff;'>dsfdsfsdf
        </div> fin layer_table -->
            <!--nuevo diseño-->
            <div id="contenedor" style='overflow:hidden;border:0px solid #ccc;border-radius: 10px 10px 10px 10px;margin-bottom:15px;'>
            <div style='float: left;border:0px solid #000; width:100%;height:auto;font-size:13px;background-color:#97989A;color:#fff;padding:5px 5px 5px 5px;'>
                <div style='float:left;border:0px solid #000;padding-right:5px;width:auto;height:auto;text-align:left;'>
                <?php echo $data_cliente->CODIGO;?>
                </div>
                <div style='float:left;border:0px solid #000;border-left:2px solid #fff;padding-left:5px;padding-right:5px;width:auto;height:auto;text-align:left;'>
                  Presupuestos: <b><?php echo $tot_pre;?></b>
                </div>
                <div style='float:left;border:0px solid #000;border-left:2px solid #fff;padding-left:5px;padding-right:5px;width:auto;height:auto;text-align:left;'>
                  Pedidos: <b><?php echo $tot_ped;?></b>
                </div>
                <div style='float:left;border:0px solid #000;border-left:2px solid #fff;padding-left:5px;width:auto;height:auto;text-align:left;'>
                  Facturas: <b><?php echo $tot_fact;?></b>
                </div>
                
            </div>
            <div style='float: left;border:0px solid #000; width:100%;height:auto;background-color:#fff;padding:20px 5px 5px 5px;'>
                  <?php
                  $correoo="";
                  if($data_cliente->NOMBREGERENTE!=''){
                    if($data_cliente->DIRCORREO!=''){
                      $correoo="<br /><span><b>Correo: <a href='mailto:".$data_cliente->DIRCORREO."'>".$data_cliente->DIRCORREO."</a></b></span>";
                    }else{
                      $correoo="";
                    }
                    
                  }
                  if($data_cliente->TIPOCOMERCIO == "0"){
                    $nombre = $data_cliente->NOMBRE1." ".$data_cliente->NOMBRE2. ", ".$data_cliente->APELLIDO1. " ".$data_cliente->APELLIDO2;
                  }else{
                    $nombre = $data_cliente->NOMBRE;
                  }
                  ?>
              <span><b>Nombre: <?php echo $nombre;?></b></span><?php echo $correoo;?>
            </div>
            <div style='float: left;border:0px solid #000; width:100%;height:auto;font-size:14px;background-color:#fff;padding:5px 5px 5px 5px;'>
                <div style='float:left;border:0px solid #000; width:25%;height:auto;text-align:center;'>
                  <img src='imgs/icono_contacto.png' width='35px' /> <br /><?php echo $data_cliente->NOMBREGERENTE;?>
                </div>
                <div style='float:left;border:0px solid #000; width:25%;height:auto;text-align:center;'>
                <a href="tel:<?php echo $data_cliente->NUMTEL;?>"><img src='imgs/icono_telefono.png' width='35px'  /> <br /><?php echo $data_cliente->NUMTEL;?></a>
                </div>
                <div style='float:left;border:0px solid #000; width:25%;height:auto;text-align:center;'>
                <a href="tel:<?php echo $data_cliente->NUMTELCONTACTO;?>"><img src='imgs/icono_celular.png' width='35px' /> <br /><?php echo $data_cliente->NUMTELCONTACTO;?></a>
                </div>
                <div style='float:right;border:0px solid #000; width:25%;height:auto;text-align:center;'>
                <a href="https://api.whatsapp.com/send?phone=+507<?php echo $data_cliente->NUMTELCONTACTO;?>"><span class="fa-stack fa-lg" style='font-size: 1.25em !important;'><i class="fa fa-circle fa-stack-2x" style='color:#FF5001;font-size:37px;margin:-2.2px 0px 0px 0px;'></i><i class="fa-brands fa-whatsapp fa-stack-1x fa-inverse" style='font-size:25px;'></i></span> <br /><?php echo $data_cliente->NUMTELCONTACTO;?></a>
                </div>
            </div>
            <div style='float: left;border:0px solid #000; width:100%;height:40px;background-color:#E8E6E7;padding:5px 5px 5px 5px;'>
                <div style='float:left;border:0px solid #000; width:33%;height:40px;'>
                
                <?php
                $codcli              = str_replace("'", "''", $data_cliente->CODIGO);

                $sql = "SELECT COUNT(*) total FROM TRANSACCMAESTRO WHERE CODIGO='".$codcli."'";
                $result = $base_de_datos->query($sql); //$pdo sería el objeto conexión
                $total_reg = $result->fetchColumn();
                if($total_reg>0){
                  $style_habilitar="";
                }else{
                  ///$habilitar="disabled";
                  $style_habilitar="pointer-events: none;color:#D1D1D1;";
                }

                $format_nom_cliente=str_replace("#", "%23", $data_cliente->NOMBRE);
                $format_codigo=str_replace("#", "%23", $data_cliente->CODIGO);
                ?>
                  <div class='caja' style='float:left;'>
                          <a id='link_cliente' href="lista_presupuesto.php?id=<?php echo $format_codigo;?>&nom_cliente=<?php echo $format_nom_cliente;?>" style="<?php echo $style_habilitar;?>">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            Documentos
                          </a>
                  </div>
                
                </div>
                <?php if($MONEDA != "RD$") { ?>
                <div style='float:left;border:0px solid #000; width:33.6%;height:40px;'>
                <div class='caja' style='margin: 0 auto;'>
                        <?php
                        //echo $_SESSION['creacliente'];
                          if($_SESSION['creacliente']==1){
                            //$habilitar="";
                            
                            $style_habilitar="";
                          }else{
                            ///$habilitar="disabled";
                            $style_habilitar="pointer-events: none;color:#D1D1D1;";
                          }

                          $format_nom_cliente=str_replace("#", "%23", $data_cliente->NOMBRE);
                          $format_codigo=str_replace("#", "%23", $data_cliente->CODIGO);

                          
                        ?>
                        
                        <?php 
                            if($_SESSION['actcliente']==1){
                              $habilitar="";
                              $style_habilitar="pointer-events: auto;";
                            }else{
                              $habilitar="disabled";
                              $style_habilitar="pointer-events: none;";
                            }
                          ?>
                        <a href="form_edit_cliente.php?id=<?php echo $format_codigo;?>&nom_cliente=<?php echo $format_nom_cliente;?>" style="<?php echo $style_habilitar;?>">
                          <i class="fa fa-pencil" aria-hidden="true"></i>
                          Editar Cliente
                        </a>
                      </div>
                </div>
                <?php } ?>
                <div style='float:right;border:0px solid #000; width:33%;height:40px;'>
                <div class='caja' style='float:right;'>
                        <?php
                        //echo $_SESSION['creacliente'];
                          if(($_SESSION['ver_presupuesto']==1) OR ($_SESSION['ver_pedido']==1) OR ($_SESSION['ver_factura']==1) OR ($_SESSION['ver_cobro']==1)){
                            //$habilitar="";
                            
                            $style_habilitar="";
                          }else{
                            ///$habilitar="disabled";
                            $style_habilitar="pointer-events: none;color:#D1D1D1;";
                          }

                          $format_nom_cliente=str_replace("#", "%23", $data_cliente->NOMBRE);
                          $format_codigo=str_replace("#", "%23", $data_cliente->CODIGO);
                        ?>
                        <a href="tarea_presupuesto.php?id=<?php echo $format_codigo;?>&nom_cliente=<?php echo $format_nom_cliente;?>" style="<?php echo $style_habilitar;?>">
                          <i class="fa fa-file-text-o" aria-hidden="true"></i>
                          Crear Tarea
                        </a>
                      </div>
                </div>
            </div>
        </div>
          <!--nuevo diseño-->
      <?php
      } /* fin while*/
      ?>


      </div><!-- fin layer_scroll -->
    </div> <!-- fin layer_container -->
  </div> <!-- fin layer_clientes -->
<?php
} /*fin $_SESSION['id_control']*/
?>
<?php
if(!isset($_SESSION['id_control'])){  
?>
  <!-- Modal -->
  <div class="container">
      
      <div style="padding:10px 0px 10px 0px;border:0px solid #ccc;width:100%;margin-top:6em;">
        <div style='text-align:center;color:#fff;'>
              <!-- <i class="fas fa-user-friends" style='font-size:50px;float:left;padding-right:10px;'></i> -->
              <span style='font-size:18px;'>Hola, <strong>Bienvenido</strong></span>
        </div>
      </div><br />
  <div id="myModal" data-backdrop="static" class="modal fade" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
          <h4 class="modal-title">Sucursales</h4>
        </div> 
        <form>
          <div class="modal-body">  
            <select class="browser-default custom-select" id="select_datlog">
            <?php
                /*recuperando todo los productos comandados*/
                $sql3="SELECT MAX(CONTADORCLI) as maximo FROM BASEEMPRESA";
                $sentencia4 = $base_de_datos->prepare($sql3, [
                            PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

                $sentencia4->execute();
                while ($data23 = $sentencia4->fetchObject()){
                    $concli=$data23->maximo;
                }

                /*recuperando todo los productos comandados*/
                $sql3="SELECT a.CONTROL, a.NOMBRE, a.CONTADORCLI, b.CODUSER FROM BASEEMPRESA AS a INNER JOIN BASEUSUARIOSSUC AS b ON a.CONTROL = b.CONTROL WHERE (b.CODUSER = '".$_SESSION['coduser']."')";
                $sentencia4 = $base_de_datos->prepare($sql3, [
                            PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

                $sentencia4->execute();
                while ($data24 = $sentencia4->fetchObject()){
                    echo "<option value='".$data24->CONTROL." - ".$data24->NOMBRE."'>".$data24->CONTROL." - ".$data24->NOMBRE."</option>";
                }
            ?>
              <option selected></option>
            </select> 
            <div id="mensaje"></div>   
          </div>
          <div class="modal-footer">
            <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
            <button type='button' class="btn btn-primary btn-block" onClick="grabar_empresa();" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;margin: 0 auto;'><strong>Entrar</strong></button>
          </div>
        </form>
      </div>

    </div>
  </div>
  <!-- Modal -->
<?php
  }
?>

<?php
 //var_dump($_SESSION['aDatos']);
  $max=sizeof($_SESSION['aDatos']);
  if($max>0){
  ?>
    <!-- Modal -->
    <div id="mymodeltask" data-backdrop="static" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
            <i class="fa fa-times-circle-o" aria-hidden="true" style='font-size:65px;color:#fa6630;'></i>&nbsp;&nbsp;&nbsp;
            <h4 class="modal-title">Existe una tarea abierta, deseas continuar o cerrar la tarea?</h4>
          </div> 
          <form>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" onClick="window.location='cerrar_tarea.php'" style='background-color:#E6E7E9 !important;color:#000 !important;border:none !important;'>Cerrar</button>
              <button type='button' class="btn btn-primary btn-block" onClick="continuar();" style='background-color:#FF5001 !important;color:#fff !important;border:none !important;'>Continuar</button>
            </div>
          </form>
        </div>

      </div>
    </div>
    <!-- Modal -->
    
  <?php
  }else{
    unset($_SESSION['tipo_tarea']);
  }

?>

<?php
  if(isset($_SESSION['id_control'])){  
?>
<div class="content" style='border:0px solid #ccc;text-align:center;'>
<div class="container"  style='border:0px solid #000;'>
<button type='button' class="btn" onClick="window.location='cerrar_session.php'" ><strong>Salir</strong></button>
  </div>
</div>
<?php
}
?>
<br /><br />
<?php
echo "<div style='color:#ccc;text-align:center;'> INNOVA SOFT - Todos los derechos reservados &reg; ".date("Y")." </div>"; 
echo "<div style='color:#ccc;text-align:left;'> &nbsp; &nbsp; &nbsp;Ver. ".$version." </div><br />"; 
?>

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
  $(function(){
    $("#myModal").modal();
    $("#mymodeltask").modal();  
  });

 function continuar(){
    setTimeout(function(){ history.go(1); }, 0);
 }

function grabar_empresa() {
    select_empresa=document.getElementById("select_datlog").value;
    const mensaje = document.getElementById("mensaje");
    if(select_empresa!=''){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                //alert(this.responseText);
                if(this.responseText==1){
                    $("#myModal").modal('hide');
                    location.reload();
                }else{
                    mensaje.innerHTML = "<label style='color:red;'>Error data!</label>";
                } 
            }
        };
        xhttp.open("GET", "grabar_empresa.php?empresa="+select_empresa, true);
        xhttp.send();
    }else{
        mensaje.innerHTML = "<label style='color:red;'>Selecciona una sucursal!</label>";
    }
}

if(document.getElementById("select_datlog")){
  var input_suc = document.getElementById("select_datlog");
  input_suc.addEventListener("keydown", function(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      grabar_empresa();
    }
  });
}

if(document.getElementById("txt_buscar")){
  var input_buscar_prod = document.getElementById("txt_buscar");
  var input_buscar_almacen = document.getElementById("almacen");
  //alert(input_buscar_prod.value+" gf "+input_buscar_almacen.value);
  input_buscar_prod.addEventListener("keydown", function(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      buscar_prod(input_buscar_prod.value, input_buscar_almacen.value);
    }
  });
}

function buscar_prod(texto_buscar, CodAlmacen){
      //alert(CodAlmacen);
      if(document.getElementById("precio_0").checked){
        //precio libre
        precio_select="precio"+document.getElementById("precio_default").value;;
      }else if(document.getElementById("precio_1").checked){
        precio_select="precio1";
      }else if(document.getElementById("precio_2").checked){
        precio_select="precio2";
      }else if(document.getElementById("precio_3").checked){
        precio_select="precio3";
      }else if(document.getElementById("precio_4").checked){
        precio_select="precio4";
      }else if(document.getElementById("precio_5").checked){
        precio_select="precio5";
      }
      //alert(precio_select);
      
      //if(texto_buscar!=''){
        
        $("#modalform_product").modal('hide');
        $("#mymodelprod").modal('show');
        texto_buscar=texto_buscar.replace("+", "|");
        layer_prod=document.getElementById("layer_prod");
        layer_prod.innerHTML="<div style='padding:20px 20px 20px 20px;text-align:center;'><i class='fa fa-cog fa-spin fa-3x fa-fw' style='color:#FD5001;'></i><br /><br />Cargando data...</div>";
      
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                //alert(this.responseText);
                layer_prod.innerHTML=this.responseText;
                $("#mymodelprod").modal('show');
                
                //$("#Modal_Comensales").modal('hide');
            }
        };
        xhttp.open("GET", "buscar_prod_preview.php?txt_buscar="+texto_buscar+"&precio="+precio_select+"&CodAlmacen="+CodAlmacen, true);
        xhttp.send();
      //}
}

  function modal_buscar_prod(){
    $("#modalform_product").modal();  
  }

  function recargar(inp){
    //alert('buscando2...');
    inp = formatear_texto(inp);
    window.location='clientes.php?input_buscar='+inp;  
  }

  function buscar(inp){
    //alert('buscando...');
    inp = formatear_texto(inp);
    window.location='clientes.php?input_buscar='+inp;  
  }

  var input_bus = document.getElementById("input_buscar");
  input_bus.addEventListener("keydown", function(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      buscar(input_bus.value)
      //buscar_prod(input_buscar_prod.value, input_buscar_almacen.value);
    }
  });
  
  function formatear_texto(texto){
    //alert(texto);
    texto=texto.replaceAll("#", "%23");
    texto=texto.replaceAll("&", "%26");
    texto=texto.replaceAll("'", "%27%27%27%27");
    //alert(texto);
    return texto;
  }

  $(window).bind("pageshow", function(event) {
    if (event.originalEvent.persisted) {
        window.location.reload();
    }
  });
  </script>
</body>
</html> 
