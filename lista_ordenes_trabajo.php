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

<?php include_once("cabecera.php"); ?>

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
.bg-innova {
    background-color: #FF5000!important;
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

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <content class="content-header">
              <div class="container-fluid">
                <div class="row mb-2">
                  <div class="col-sm-6">
                    <h2>
                      Administrar O.T.
                    </h2>
                  </div>
                </div>
              </div><!-- /.container-fluid -->
            </content>

            <!-- Main content -->
            <dev class="content">
              <div class="container-fluid">
              <!-- Default box -->
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-primary card-outline">
                    <div class="card-header">

                    </div>
                    <div class="card-body">
                    <div id="listadoOrdTrab"></div>
                </div>
                <!-- /.box-footer-->
              </div>
              <!-- /.box -->
              </div>
              </div>
              </div>
            </dev>
            <input type="hidden" id="codigo" value="<?= $codigo ?>"></input>
            <input type="hidden" id="nomcliente" value="<?= $nomcliente ?>"></input>
            <!-- /.content -->
        </div>

        </div> <!-- fin container -->
    </div> <!-- fin content -->
</main>
<script>
$(document).ready(function() {
    mostrarListado();
    function mostrarListado(){
        var html = '';
        html='  <table class="table table-bordered table-striped dt-responsive" id="tablaOrdTrabajo" width="100%">';
        html += '<thead>';
        html += '<tr>';
        html += '<th style="width:10px">Control</th>';
        html += '<th>Nombre</th>';
        html += '<th>Nro Tel.</th>';
        html += '<th>Correo</th>';
        html += '<th>Vendedor</th>';
        html += '<th>Estado</th>';
        html += '<th>Acciones</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tfoot>';
        html += '<tr>';
        html += '<th style="width:10px">Control</th>';
        html += '<th>Nombre</th>';
        html += '<th>Nro Tel.</th>';
        html += '<th>Correo</th>';
        html += '<th>Vendedor</th>';
        html += '<th>Estado</th>';
        html += '<th>Acciones</th>';
        html += '</tr>'
        html += '</tfoot>'
        html += '</table>';

        $("#listadoOrdTrab").empty();
        $("#listadoOrdTrab").append( html );

        $('#tablaOrdTrabajo').DataTable( {
          "destroy":true,
          "ajax": {
            "url": "ajax/listarOrdenesTrabajo.php",
            "data": {
              "accion": "mostrarOT",
              "codigo": document.getElementById("codigo").value
            },
            "method": "POST",
          },
            "defenRender":true,
            "retrieve":true,
            "processing":true,
              "language": {

                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
              },
              "oAria": {
              "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
              "sSortDescending": ": Activar para ordenar la columna de manera descendente"
              }

            }
          } );
    }
    
  $(document).on("click",".btnAgregarOT" , function()
  {

    var control    = $(this).attr("control");
    var id   = document.getElementById("codigo");
    var nombre   = $(this).attr("nombre");
    var data = {
      nombre : nombre,
      control : control
    };
    if(confirm('Desea Agregarla orden de trabajo?') == true){
      var data = window.btoa(JSON.stringify(data, true));
      location.href="tarea_ot.php?control="+control+"&nom_cliente="+nombre+"&id="+codigo.value;
    };
    
  });
});
</script>
