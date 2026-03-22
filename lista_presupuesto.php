<?php
include_once("permiso.php");
include_once "config/db.php";
if(!isset($_GET['id'])){
  $codigo="";
  $nomcliente="";
}else{
  $codigo=$_GET['id'];
  $nomcliente=$_GET['nom_cliente'];
}

if(!isset($_GET['ref'])){
    $ref="";
    $sql_cad="";
}else{
    $ref=$_GET['ref'];
    $sql_cad="AND NUMREF='$ref'";
}
  

//$_SESSION['aDatos'] = array();
//unset($_SESSION['tipo_tarea']);
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
<!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">-->
<link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
<link href="fontawesome-free-6.2.1/css/all.css" rel="stylesheet">
<!-- <link rel="stylesheet" type="text/css" href="autocomplete/mack.css">
<script type="text/javascript" src="autocomplete/autocomplete.js"></script> -->
<title>
    <?php echo $_SESSION['titulo_web'];?>
  </title>
<style>
body{ 
  border: 0px solid black;
  padding: 0px;
  background: url('imgs/fondo2.png') no-repeat fixed center;
  background-repeat: no-repeat;
  /*background-size: 100%;*/
  background-size: cover;
  background-color:#BCBDC0;
}

.Table
{
    display: table;
    width:100%;
    background-color:#fff;
    border-radius:15px 15px 15px 15px;
}
.Heading
{
    display: table-row;
    font-weight: bold;
    text-align: center;
    background-color:#97989A;
    color:#fff;
}

.Heading .Cell:nth-child(1)
{
    border-radius:15px 0px 0px 0px;
    border-top:none;
    padding-top:10px;
}

.Heading .Cell:nth-child(2)
{
    border-top:none;
    border-left:1px solid #fff;
}.Heading .Cell:nth-child(3)
{
    border-left:1px solid #fff;
    border-top:none;
}

.Heading .Cell:nth-child(4)
{
    border-radius:0px 15px 0px 0px;
    border-top:none;
    border-left:1px solid #fff;
}

.Row
{
    display: table-row;
    font-size:12px;
}

.Cell
{
    display: table-cell;
    padding-left: 5px;
    padding-right: 5px;
    text-align:center;
    border-top:1px solid #F2F2F2;
}

.form {
    position: relative
}

.form .fa-search {
    position: absolute;
    top: 20px;
    left: 20px;
    color: #9ca3af
}

.form span {
    position: absolute;
    right: 5px;
    top: 2px;
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
    text-indent: 33px;
    border-radius: 50px
}

.form-input:focus {
    box-shadow: none;
    border: none
}

.form-control {
    border-radius: 2.25rem !important;
    background-color:#fff !important;
    border:1px solid #fff !important;
}

.fa-stack-1x, .fa-stack-2x {
    left: 5px !important;
}

.titulo{
  text-align:center;color:#fff;font-size:1.2rem;
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
                <!-- <i class="fas fa-user-friends" style='font-size:50px;float:left;padding-right:10px;'></i> -->
                <!-- <span><strong>Módulo de Emisión<br /> de Pedidos y Presupuestos</strong></span> -->
                <div style="position:absolute; top:5px; left:0; auto"><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:15px;'></i></a></div>   
                <h5 style='color:#fff;'>PRESUPUESTOS Y PEDIDOS</h5><hr/>
          </div>
        </div>
        
      </div> <!-- fin container -->
    </div> <!-- fin content -->
    <div class="content">
        <div class="container">
            <?php
            $codigo = str_replace("'", "''", $codigo);

            $sql = "SELECT COUNT(*) AS total, TIPTRAN FROM TRANSACCMAESTRO WHERE (CODIGO = '$codigo') AND TIPTRAN IN ('PEDxCLI', 'PRE') GROUP BY TIPTRAN";
            $result = $base_de_datos->query($sql); //$pdo sería el objeto conexión
            $total_reg = $result->fetchColumn();
            $tot_pre=0;
            $tot_ped=0;
            if($total_reg!=''){
                $sentencia4 = $base_de_datos->prepare($sql, [
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);
                    
                $sentencia4->execute();

                while ($data2 = $sentencia4->fetchObject()){
                    if($data2->TIPTRAN=='PRE'){
                        $tot_pre=$data2->total;
                    }else if($data2->TIPTRAN=='PEDxCLI'){
                        $tot_ped=$data2->total;
                    }else if($data2->TIPTRAN=='FAC'){
                        $tot_ped=$data2->total;
                    }
                }
            }
            ?>
            <!-- <div class="table-responsive custom-table-responsive"> -->
            <!-- <h5><a onClick="window.history.back();"><i class="fa fa-long-arrow-left" aria-hidden="true" style='color:#F15A24;font-size:23px;'></i></a>&nbsp;&nbsp;<?php echo $nomcliente;?></h5> -->
            <!-- <h5 style='color:#fff;'><a onClick="window.history.back();"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:10px;'></i></a>&nbsp;&nbsp;Presupuestos y Pedidos</h5><hr/><br /> -->
            <div style='background-color:#939598;color:#fff;height:auto;border-radius:10px 10px 0px 0px;padding:8px 8px 8px 8px;'> <b><?php echo $codigo;?></b> <b style='font-size:20px;'>|</b> Presupuestos: <b><?php echo $tot_pre;?></b> <b style='font-size:20px;'>|</b> Pedidos: <b><?php echo $tot_ped;?></b></div>
            <div style='background-color:#fff;color:#000;height:auto;border-radius:0px 0px 10px 10px;padding:10px 8px 8px 8px;'>
              <b>Cliente: <?php echo $nomcliente;?></b>
            </div>
                <br />
                <div>
                    <form class="form-inline" action='lista_presupuesto.php' method='GET'>
                        <div class="input-group mb-3" style='justify-content: right;border:0px solid #ccc;background-color:#fff;border-radius:20px 20px 20px 20px;'>
                        <input autocomplete="off" type="text" class="form-control" name="ref" id="ref" value="">
                        <input type="hidden" class="form-control" name="id" id="id" value="<?php echo $codigo;?>">
                        <input type="hidden" class="form-control" name="nom_cliente" id="nom_cliente" value="<?php echo $nomcliente;?>">
 
                        <div class="input-group-append">
            
                            <!-- <button class="btn btn-primary" type="submit" style='background-color:#0CB3F9;'><i class="fa fa-filter" style='background-color:#0CB3F9;'></i></button> -->
                            <a onClick="buscar();"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x" style='color:#0CB3F9;font-size:45px;'></i><i class="fa fa-filter fa-stack-1x fa-inverse" style='font-size:20px;'></i></span></a>
                        </div>&nbsp;
                        
                        </div>
                    </form> 
                </div>
                <div class="Table">
                <div class="Heading">
                    <div class="Cell">
                        <p>Ref.</p>
                    </div>
                    <div class="Cell">
                        <p>Fecha</p>
                    </div>
                    <div class="Cell" style='text-align:right;'>
                        <p>Subtotal</p>
                    </div>
                    <div class="Cell">
                        <p>Tipo de Doc.</p>
                    </div>
                </div>
                        <?php
                        $diassemana = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
                        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                      

                        $sql = "SELECT COUNT(*) total FROM TRANSACCMAESTRO WHERE CODIGO='$codigo' AND TIPTRAN IN ('PEDxCLI', 'PRE', 'FAC')";
                        $result = $base_de_datos->query($sql); //$pdo sería el objeto conexión
                        $total_reg = $result->fetchColumn();
                        if($total_reg>0){
                            $sql_button="SELECT CONTROL, NUMREF, DESCRIP1, FECEMISS, MONTOSUB, MONTOTOT, TIPTRAN, NOMBRE FROM TRANSACCMAESTRO WHERE CODIGO='$codigo' AND TIPTRAN IN ('PEDxCLI', 'PRE', 'FAC') $sql_cad";
                            $sentencia_b = $base_de_datos->prepare($sql_button, [
                                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                ]);
                            $sentencia_b->execute();
                            while ($data_b = $sentencia_b->fetchObject()){
                                //$_SESSION['parcontrol']=$data_b->CodeSucursal;
                                if($data_b->TIPTRAN=='PRE'){
                                    $tip_doc='Presupuesto';
                                }else if($data_b->TIPTRAN=='PEDxCLI'){
                                    $tip_doc='Pedido';
                                }else if($data_b->TIPTRAN=='FAC'){
                                    $tip_doc='Factura';
                                }

                                $datetime = new DateTime($data_b->FECEMISS);
                                //echo $datetime->format('w');
                                $f_factura= $meses[$datetime->format('n')-1]." ".$datetime->format('d')." del ".$datetime->format('Y');
                        ?>
                        <div class="Row">
                            <div class="Cell">
                                <p><a href="doc_pdf_demo.php?idcontrol=<?php echo $data_b->CONTROL;?>&idfac=<?php echo $data_b->NUMREF;?>&tiptran=<?php echo $data_b->TIPTRAN;?>"><?php echo $data_b->NUMREF;?></a></p>
                            </div>
                            <div class="Cell">
                                <p><?php echo $f_factura;?></p>
                            </div>
                            <div class="Cell" style='text-align:right;'>
                                <p><?php echo $data_b->MONTOTOT;?></p>
                            </div>
                            <div class="Cell">
                                <p><?php echo $tip_doc;?></p>
                            </div>
                        </div>
                        <?php
                            } // fin while
                        } // fin if
                        ?>
                    </div><!--fin tabla -->
                    <br /><br />
            <!-- </div> -->

            






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
		jQuery(document).ready(function($) {
			$("#loading").hide();
		});
</script>
<script>
    function buscar(){
        //alert(id);
        ref=document.getElementById("ref").value;
        id=document.getElementById("id").value;
        nom_cliente=document.getElementById("nom_cliente").value;
        window.location='lista_presupuesto.php?ref='+ref+'&id='+id+'&nom_cliente='+nom_cliente;  
    }
</script>  
</body>
</html> 
