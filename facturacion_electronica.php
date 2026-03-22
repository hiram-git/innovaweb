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
.button-link {
    display: inline-flex;
    justify-content: center; /* Añadida esta línea */
    align-items: center; /* Añadida esta línea */
    padding: 16px;
      border: 1px solid #ccc;
    border-radius: 4px;
    line-height: normal;
    margin-right: 10px;
}
.swal-text {
  text-align: center;
}
@media only screen and (max-width: 768px) {
    .hide-in-mobile {
        display: none !important; 
    }
}
@media only screen and (max-width: 768px) {
    .in-mobile {
        font-size: 11px;
        width: 50px;
    }
}
.table thead th {
    text-align: center;
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
                <h2 style='color:#fff;'>FACTURACIÓN ELECTRÓNICA</h2><hr/>
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
                    <h5 class="text-white">
                      FACTURAS
                    </h5>
                  </div>
                  <div class="col-sm-6 text-right text-white">
                    <a class="button-link" onclick="window.location.href='factura_electronica_configuracion.php'">
                    <i class="fa fa-cog" aria-hidden="true"></i>Configuración  
                    </a>
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
                    <div id="listadoFacturas"></div>
                </div>
                <!-- /.box-footer-->
              </div>
              <!-- /.box -->
              </div>
              </div>
              </div>
            </dev>
            <input type="hidden" id="parcontrol" value="<?= trim($_SESSION['id_control']) ?>"></input>
            <input type="hidden" id="codigo" value="<?= $codigo ?>"></input>
            <input type="hidden" id="nomcliente" value="<?= $nomcliente ?>"></input>
            <input type="hidden" id="tipopac" value=""></input>
            <!-- /.content -->
        </div>

        </div> <!-- fin container -->
    </div> <!-- fin content -->
</main>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">PDF FACTURACIÓN ELECTRÓNICA</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <iframe src="" width="100%" height="500" frameborder="0"></iframe>
        </div>
      </div>
    </div>
  </div>


<script>
$(document).ready(function() {
  mostrarListado();
  
  var buscarPac = function () {
        
        $.ajax({
            url: 'ajax/obtenerConfigFacElec.php', // Reemplaza 'obtener_campos.php' con la URL correcta del archivo PHP que devuelve los campos
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var campos = response.data;
                    $('#tipopac').val( campos.PAC );
                } else {
                    swal('¡Error!', 'Error al obtener los campos. Por favor, inténtalo de nuevo.', 'error');
                }
            },
            error: function() {
                swal('¡Error!', 'Error en la solicitud AJAX. Por favor, inténtalo de nuevo.', 'error');
            }
        });

    };
    buscarPac();
  function mostrarListado(){
  var html = '';
  html='  <table class="table table-bordered table-striped dt-responsive" id="tablaFacturas" width="100%">';
  html += '<thead>';
  html += '<tr>';
  html += '<th style="width:10px">#</th>';
  html += '<th class="in-mobile">Cliente</th>';
  html += '<th class="hide-in-mobile">Subtotal</th>';
  html += '<th class="hide-in-mobile">Imp</th>';
  html += '<th class="in-mobile">Total</th>';
  html += '<th class="hide-in-mobile">Estado</th>';
  html += '<th class="in-mobile" >Acciones</th>';
  html += '</tr>';
  html += '</thead>';
  html += '<tfoot>';
  html += '<tr>';
  html += '<th style="width:10px">#</th>';
  html += '<th class="in-mobile">Cliente</th>';
  html += '<th class="hide-in-mobile">Subtotal</th>';
  html += '<th class="hide-in-mobile">Imp</th>';
  html += '<th class="in-mobile">Total</th>';
  html += '<th class="hide-in-mobile text-center">Estado</th>';
  html += '<th class="in-mobile" >Acciones</th>';
  html += '</tr>'
  html += '</tfoot>'
  html += '</table>';

  $("#listadoFacturas").empty();
  $("#listadoFacturas").append( html );

  $('#tablaFacturas').DataTable( {
    "destroy":true,
    "ajax": {
      "url": "ajax/mostrarFel.php",
      "data": {
        "accion": "mostrarFel",
        "parcontrol": $("#parcontrol").val(),
      },
      "method": "POST",
    },
    "serverSide": true,
    "responsive": true,
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

    },
    "columnDefs": [
      { 
        "targets": [5,6], // Esto selecciona la primera y tercera columna
        "className": 'text-center' // Esto aplica la clase 'miClase' a las columnas seleccionadas
      },
      {
        "targets": [ 1],
        "className":' text-left'  // Esta clase ocultará estas columnas en diseño responsive
      },
      {
        "targets": [ 2, 3, 4],
        "className":'hide-in-mobile text-right'  // Esta clase ocultará estas columnas en diseño responsive
      },
      {
        "targets": [ 0,1,4,2, 3, 5],
        "className":'in-mobile'  // Esta clase ocultará estas columnas en diseño responsive
      }
    ],
    "columns": [
      { "data": 'CONTROL',"width": "10%" },  // Establece el ancho de la segunda columna, por ejemplo
        { "data": 'NOMBRE', "width": "50%" },  // Establece el ancho de la segunda columna, por ejemplo
        { "data": 'MONTOBRU', "width": "10%" },  // Establece el ancho de la segunda columna, por ejemplo
        { "data": 'MONTOIMP', "width": "10%" },  // Establece el ancho de la segunda columna, por ejemplo
        { "data": 'MONTOTOT', "width": "10%" },  // Establece el ancho de la segunda columna, por ejemplo
        { "data": 'ESTADO', "width": "10%" },  // Establece el ancho de la segunda columna, por ejemplo
        { "data": 'ACCIONES', "width": "10%" },  // Establece el ancho de la segunda columna, por ejemplo
    ]
  });
}
    
  $(document).on("click",".btnReenviarDocumento" , function()
  {

    var control    = $(this).attr("control");
    var nombre   = $(this).attr("nombre");
    
    if(confirm('Desea Reenviar el documento?') == true){

      swal({
        title: 'Enviando',
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

      if($("#tipopac").val() == 1 || $("#tipopac").val() == 2 )
      {
        direccion_envio = 'fel/thfkapanama/factura.php';
      }

      if($("#tipopac").val() == 3 ){
        direccion_envio = 'fel/digifact/enviarDigifact.php';
      }

      var datos = new FormData();
      datos.append("control", window.btoa(control));
      

      fetch( direccion_envio , {
        method: 'POST',
        body: datos
      })
      .then(function(response) {
        return response.json();
      })
      .then(datos => {
        var mensaje = datos.mensaje;

        console.log(datos);
        swal.close(); // Ocultar el indicador de carga
        if (datos.estado === 1) {
          swal({  
            title: "Factura generada exitosamente",
            text: mensaje.mensaje,
            icon: "success",
            className: "text-center"
          })
          .then(() => {
            location.reload(); // Recargar la página
          });
        } else {
          swal({
            title: "Error al generar la factura",
            text: mensaje.mensaje,
            icon: "error",
            className: "text-center"
          });
        }
      })
      .catch((error) => {

        swal.close(); // Cerrar el modal de carga
        swal({
          title: "Error al generar la factura",
          text: error.message,
          icon: "error",
            className: "text-center"
        });
        console.error('Error:', error);
      });
    };
    
  });

  $(document).on("click",".btnImprimirDocumento" , function()
  {
    var control    = $(this).attr("control");
      var datos = new FormData();
      datos.append("accion", "mostrarDocumento");
      datos.append("caso", "Reenvio");
      datos.append("control", control);

      swal({
          title: 'Generando PDF',
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
      fetch('ajax/mostrarDocumento.php', {
        method: 'POST',
        body: datos
      })
      .then(function(response) {
        return response.json();
      })
      .then(datos => {
        swal.close(); // Ocultar el indicador de carga
        var modal = $('#myModal');
        var pdfViewer = modal.find('iframe');
        pdfViewer.attr('src', "data:application/pdf;base64," + datos.PDF);
        modal.modal('show');
        console.log(datos);
      })
      .catch((error) => {

        swal.close(); // Cerrar el modal de carga
        swal({
          title: "Error al generar la factura",
          text: error.message,
          icon: "error",
            className: "text-center"
        });
        console.error('Error:', error);
      });

  });
});
</script>
