<?php
include_once("permiso.php");
include_once "config/db.php";

$data = base64_decode($_GET['data']);
$data = json_decode($data, true);
$control = $data["control"];

$sql_orden = "SELECT T.CONTROL, T.NOMBRE, BC.NUMTEL, BC.DIRCORREO, BV.NOMBRE AS NOMBRE_VENDEDOR, '' ESTADO 
FROM TRANSACCMAESTRO AS T 
LEFT JOIN BASECLIENTESPROVEEDORES BC ON T.CODIGO = BC.CODIGO
LEFT JOIN BASEVENDEDORES BV ON T.CODVEN = BV.CODVEN
WHERE T.TIPTRAN IN ('PEDxCLI', 'PRE') AND T.CONTROL = '{$control}'";

$result = $base_de_datos->query($sql_orden);
$orden_trabajo = $result->fetch(PDO::FETCH_ASSOC);
  

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
<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="jquery/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="jquery/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="bootstrap2/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

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
    /*border-radius: 2.25rem !important;*/
    background-color:#fff !important;
    /*border-bottom:1px solid #000 !important;*/
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
.btn-primary {
    color: #fff;
    background-color: #2ca8ff!important;
    border-color: #2ca8ff!important;
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
                <div style="position:absolute; top:5px; left:0; auto"><a onClick="location.href='clientes.php?input_buscar='"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:15px;'></i></a></div>   
                <h5 style='color:#fff;'>Orden de trabajo</h5><hr/>
          </div>
        </div>
        
      </div> <!-- fin container -->
    </div> <!-- fin content -->
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-offset-2 col-lg-12 mb-2">
                    <div class="card card-danger card-outline">
                        <div class="card-header"><h3>Datos del Cliente</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 col-lg-6">
                                    <div class="form-group">
                                        <label>NOMBRE</label>
                                        <input class="form-control" type="text" id="nombres" name="nombres" value="<?php echo $orden_trabajo["NOMBRE"]; ?>" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-lg-6">
                                    <div class="form-group">
                                        <label >TELEFONO</label>
                                        <input class="form-control" type="text" id="nro_telefono" name="nro_telefono" value="<?php echo $orden_trabajo["NUMTEL"]; ?>" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-lg-6">
                                    <div class="form-group">
                                        <label>EMAIL</label>
                                        <input class="form-control" type="text" id="correo" name="correo" value="<?php echo $orden_trabajo["DIRCORREO"]; ?>" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-lg-6">
                                    <div class="form-group">
                                        <label>VENDEDOR</label>
                                        <input class="form-control" type="text" id="vendedor" name="vendedor" value="<?php echo $orden_trabajo["NOMBRE_VENDEDOR"]; ?>" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="content mt-2 mb-4">
      <div class="container">
        <!-- /.HACIA ABAJO  COMIENZA LA INFORMACIÃ“N DE LA CITA -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card card-primary">
              <div class="card-header">
                  <h3 class="card-title">Información de la Contacto</h3>
              </div>     
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div class="row">
                <div class="col-md-12  border border-left-0 border-top-0 border-bottom-0 p-4">
                  <form  method="post" enctype="multipart/form-data" id="formAgregarLeadDetalle">
                    <div class="row">
                      <div class="col-md-3">
                          <div class="form-group">
                          <label># OT</label>
                            <input class="form-control" type="text" id="fecha" name="fecha" value="<?php echo $data["control"] ?>" readonly />

                          </div>
                      </div>
                      <div class="col-md-3">
                          <div class="form-group">
                          <label># PRESUPUESTO</label>
                            <input class="form-control" type="text" id="fecha" name="fecha" value="<?php echo $data["control"] ?>" readonly />
                          </div>

                      </div>
                      <div class="col-md-3">
                          <div class="form-group">
                          <label>ATENDIDO POR:</label>
                              <select class="form-control select2"  name= "agFormaContacto" id="agFormaContacto" style="width:90%;">
                              </select>
                          </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>RESPONSABLE</label>
                          <select class="form-control select2"  name= "agContactado" id="agContactado" style="width:90%;">
                          <option value="1">Si</option>
                          <option value="0">No</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>FECHA ENTRADA</label>
                          <input class="form-control" type="text" id="fecha" name="fecha" value="<?php echo date('d-m-Y'); ?>" readonly />

                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>FECHA ENTREGA</label>
                          <div class="input-group date" data-provide="datepicker">
                            <input type="text" class="form-control">
                            <div class="input-group-addon">
                              <span class="glyphicon glyphicon-th"></span>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="col-md-8">
                </div>
              </div>
            </div>
          <!-- /.card-body -->
          </div>
        <!-- /.card -->
        </div>
      </div>
    </div>
    <!-- /.content -->

    <div class="content">
      <div class="container">
        <!-- /.HACIA ABAJO  COMIENZA LA INFORMACIÃ“N DE LA CITA -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card card-danger">
              <div class="card-header">
                <div class="row">
                  <div class="col-md-9 text-center">
                    <h3 class="card-title">DATOS DEL PRODUCTO</h3>
                  </div>
                  <div class="col-md-3 text-center">
                      <button class="btn btn-success addProduct"><i class = "fa fa-plus"></i>&nbsp;Agregar Producto</button>
                  </div>
                </div>
              </div>     
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div class="row ">
                <div class="col-md-8">
                    <div id="tabla_citas" class="m-3"></div>
                </div>
              </div>
            </div>
          <!-- /.card-body -->
          </div>
        <!-- /.card -->
        </div>
      </div>
      <!-- /.AQUI TERMINA SOBRE LA CITA-->
    </div>


</div>
<!-- El modal -->
<div class="modal fade" id="modalOt" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
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
              <label for="campo3">Material</label>
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
                <option value="blanconegro">B/N</option>
                <option value="color">Color</option>
              </select>
            </div>

            <div class="col-3 col-md-3">
              <label for="campo6">N° Caras</label>
              <input type="text" class="form-control" id="caras">
            </div>

            <div class="col-3 col-md-3">
              <label for="campo6">N° Caras</label>
              <input type="text" class="form-control" id="campo6">
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
        <button type="button" class="btn btn-primary">Guardar</button>
      </div>

    </div>
  </div>
</div>

</div>
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
    $(".addProduct").off().on("click", function(){
      $("#modalOt").modal("show");
    });    $(document).ready(function(){
      $('[data-provide="datepicker"]').datepicker();
    });
</script>  
</body>
</html> 

