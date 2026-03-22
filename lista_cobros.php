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
    border-radius:5px 5px 5px 5px;
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
/*
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
*/
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
@media (max-width: 767px) {
    .hide-on-mobile {
        display: none;
    }
    
    .btn {
      font-size: 11px !important; /* Cambia el tamaño de la fuente según tus preferencias */
    }
    .hide-on-mobile td,
    .hide-on-mobile th {
        font-size: 11px !important; /* Cambia el tamaño de la fuente según tus preferencias */
    }
  #miTabla {
    width: 100% !important;
    font-size: 11px !important; /* Ajusta el tamaño de la fuente según tus preferencias para dispositivos móviles */
  }
  .dataTables_paginate,
  .dataTables_info{
    font-size: 11px !important; /* Ajusta el tamaño de la fuente según tus preferencias para dispositivos móviles */

  }
  .miTabla_filter{
        display: none;

  }
  .dataTables_length{
        display: none;

  }
  #miTabla_filter {
    display: none;
    flex-direction: row;
  }
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
                <h5 style='color:#fff;'>Cobros</h5><hr/>
          </div>
        </div>
        
      </div> <!-- fin container -->
    </div> <!-- fin content -->
    <div class="content">
        <div class="container">
          <?php
            $codigo = str_replace("'", "''", $codigo);
            $sql = "SELECT COUNT(*) AS total, TIPTRAN FROM TRANSACCMAESTRO WHERE (CODIGO = '$codigo') GROUP BY TIPTRAN";
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
                      <div class="input-group mb-3" style='display:none;justify-content: right;border:0px solid #ccc;background-color:#fff;border-radius:20px 20px 20px 20px;'>
                      <input autocomplete="off" type="text" class="form-control" name="ref" id="ref" value="">
                      <input type="hidden" class="form-control" name="codigo" id="codigo" value="<?php echo $codigo;?>">
                      <input type="hidden" class="form-control" name="nom_cliente" id="nom_cliente" value="<?php echo $nomcliente;?>">
                      </div>
                </div>
                <div class="container Table">
                <table id="miTabla" class="table table-striped">
                <thead>
                    <tr>
                    <th class="hide-on-mobile" width="10%">Doc</th>
                    <th>Num</th>
                    <th>Emisión</th>
                    <th class="hide-on-mobile">Venc.</th>
                    <th class="hide-on-mobile">Saldo</th>
                    <th class="hide-on-mobile">Monto Pago</th>
                    <th width="20%" >Monto Fact.</th>
                    <th></th>
                    </tr>
                </thead>
                <tbody> <?php
                        $diassemana = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
                        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                      

                        $sql = "SELECT COUNT(*) total FROM TRANSACCMAESTRO WHERE CODIGO='$codigo' AND TIPTRAN IN ('PEDxCLI', 'PRE','FAC')";
                        $result = $base_de_datos->query($sql); //$pdo sería el objeto conexión
                        $total_reg = $result->fetchColumn();
                        if($total_reg>0){
                            $sql_button="SELECT CONTROL, NUMREF, DESCRIP1, MONTOSUB, MONTOTOT, TIPTRAN, NOMBRE, MONTOSAL, FECEMISS, FECVENCS FROM TRANSACCMAESTRO WHERE CODIGO='$codigo' AND TIPTRAN IN ('FAC') AND MONTOSAL > 0 $sql_cad";
                            $sentencia_b = $base_de_datos->prepare($sql_button, [
                                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                ]);
                            $sentencia_b->execute();
                            while ($data_b = $sentencia_b->fetchObject()){
                                $sql3 = "SELECT SUM(MONTOTOT) MONTOTOT FROM TRANSACCMAESTRO t WHERE t.TIPTRAN = 'PAGxFAC' AND  CODIGO = '".$codigo."' AND CONTROLDOC='".$data_b->CONTROL."';";

                                $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
                                $total_reg = $result->fetchColumn();

                                //$_SESSION['parcontrol']=$data_b->CodeSucursal;
                                if($data_b->TIPTRAN=='PRE'){
                                    $tip_doc='Presupuesto';
                                }else if($data_b->TIPTRAN=='PEDxCLI'){
                                    $tip_doc='Pedido';
                                }else if($data_b->TIPTRAN=='FAC'){
                                    $tip_doc='Factura';
                                }

                                $datetime = new DateTime($data_b->FECEMISS);
                                $fec_venc = new DateTime($data_b->FECVENCS);
                                //echo $datetime->format('w');
                                $f_factura= $meses[$datetime->format('n')-1]." ".$datetime->format('d')." del ".$datetime->format('Y');
                                $f_venc= $meses[$fec_venc->format('n')-1]." ".$fec_venc->format('d')." del ".$fec_venc->format('Y');
                        ?>
                        <tr>
                            <td>FAC</td>
                            <td><?php echo $data_b->NUMREF;?></td>
                            <td><?php echo $f_factura;?></td>
                            <td><?php echo $f_venc;?></td>
                            <td> <p><?php echo $data_b->MONTOSAL;?></p></td>
                            <td> <p><?php echo $total_reg;?></p></td>
                            <td> <p><?php echo $data_b->MONTOTOT;?></p></td>
                            <td> <button class="btn btn-primary btn-xs" onclick="abrirModal(this)"
                              data-id="<?php echo $data_b->NUMREF;?>"
                              data-control="<?php echo $data_b->CONTROL;?>"
                              data-fecemis="<?php echo $f_factura;?>"
                              data-fecvenc="<?php echo $f_venc;?>"
                              data-saldo="<?php echo $data_b->MONTOSAL;?>"
                              data-pago="<?php echo $total_reg;?>"
                              data-total="<?php echo $data_b->MONTOTOT;?>"
                             >Pagar</button> </td>
                        </tr>
                        
                        <?php
                            } // fin while
                        } // fin if
                        ?>
                    <!-- Agrega más filas según sea necesario -->
                </tbody>
                </table>
                </div><!--fin tabla -->
                <br /><br />
            <!-- </div> --><!-- Modal -->

            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">

                    <!-- Modal content-->
                    <div class="modal-content" >
                        <div class="modal-header justify-content-center">
                        <h5>COBROS CUENTAS POR COBRAR</h5>
                        </div> 
                        <form>
                        <div class="modal-body">  
                            
                            <div class="container mt-2">
                                <div class="row align-items-right">
                                  <div class="col-10 col-md-10">&nbsp;
                                  </div>
                                  <div class="col-2 col-md-2">
                                    <a id="printList" class="btn btn-primary text-white"><i class="fas fa-print"></i></a>
                                  </div>
                                </div>
                                <input id="modal_control" type="hidden" class="form-control" placeholder="Valor">

                                <div class="row border-bottom align-items-center">
                                    <div class="col-6 col-md-5 text-right h5">
                                    DOCUMENTO
                                    </div>
                                    <div class="col-6 col-md-7"> <span id="modal_documento" class="h6">FAC</span>
                                    </div>
                                </div>
                                <div class="row border-bottom align-items-center">
                                    <div class="col-6 col-md-5 text-right h5">
                                    NUMERO
                                    </div>
                                    <div class="col-6 col-md-7">
                                    <span id="modal_numero"  class="h6"></span>
                                    </div>
                                </div>
                                <div class="row border-bottom align-items-center">
                                    <div class="col-6 col-md-5 text-right h5">
                                    FECHA
                                    </div>
                                    <div class="col-6 col-md-7">
                                     <span id="modal_fecha" class="h6"></span>
                                    </div>
                                </div>
                                <div class="row border-bottom align-items-center">
                                    <div class="col-6 col-md-5 text-right h5">
                                    VENCE
                                    </div>
                                    <div class="col-6 col-md-7">
                                     <span id="modal_vence" class="h6"></span>
                                    </div>
                                </div>
                                <div class="row border-bottom align-items-center">
                                    <div class="col-6 col-md-5 text-right h5">
                                    PAGADO
                                    </div>
                                    <div class="col-6 col-md-7">
                                    <span id="modal_pagado" class="h6"></span>
                                    </div>
                                </div>
                                <div class="row border-bottom align-items-center">
                                    <div class="col-6 col-md-5 text-right h5">
                                    SALDO
                                    </div>
                                    <div class="col-6 col-md-7">
                                    <span id="modal_saldo" class="h6"></span>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-5 col-md-5">
                                    <select id="mySelect" class="form-control">
                                        <option value="" selected disabled>Elija una forma de pago</option>
                                    </select>
                                    </div>
                                    <div class="col-5 col-md-5">
                                    <input id="myInput" type="text" class="form-control" placeholder="Valor"  oninput="validarInput(this)">
                                    </div>
                                    <div class="col-2 col-md-2">
                                    <button id="addBtn" class="btn btn-primary"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 col-md-12">
                                    <div id="myTable" >
                                    </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-12" id="mostrarListadoCobros">
                                    
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">

                            <button type='button' id="procesarCobro" class="btn btn-info">Aceptar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                        </form>
                    </div>

                </div>
            </div>

            






        </div>
    </div>
    <!-- loading 
    <div id="loading" style="z-index: 10000; position: fixed; top:0; left:0; background-color: rgba(0,0,0,.7); width: 100vw; height: 100vh;">
		<div style="display: inline-block; position: absolute; top: 50%; left: 50%; margin: -50px 0 0 -50px; transform: translateXY(-50%,-50%);">
			<span class="fas fa-spin fa-spinner fa-5x" style="color:#ff5001"></span>
		</div>
	</div>-->
    <?php include("recursos/loading.php");?>


  <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="miModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="miModalLabel">COBROS</h5>
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
</main>
<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#loading").hide();
		});
</script>
<script>
function validarInput(input) {
    var regex = /[^0-9]/; // Esta expresión regular busca caracteres no permitidos
    if (regex.test(input.value)) {
        var caracterNoPermitido = input.value.match(regex)[0];
        alert('El campo Valor sólo permite números');
        input.value = input.value.replace(regex, ''); // Elimina el caracter especial del valor del input
    }
}
$(document).ready(function() {
  $('#miTabla').DataTable({
        createdRow: function (row, data, index) {
            if (data[0]) {
                $('td', row).eq(0).addClass('hide-on-mobile');
                $('td', row).eq(3).addClass('hide-on-mobile');
                $('td', row).eq(4).addClass('hide-on-mobile');
                $('td', row).eq(5).addClass('hide-on-mobile');
            } 
        },
        stateSave: true,
        language: {
            search: 'Buscar',
            lengthMenu: 'Mostrar _MENU_ registros',
            zeroRecords: 'No encontrado',
            info: 'Mostrando _PAGE_ de _PAGES_',
            infoEmpty: 'No hay registros',
            infoFiltered: '(filtrados de _MAX_ registros)',
        },
    });
});
function abrirModal(btn) {

  var id = $(btn).data('id');
  var control = $(btn).data('control');
  var fecemis = $(btn).data('fecemis');
  var fecvenc = $(btn).data('fecvenc');
  var saldo = $(btn).data('saldo');
  var total = $(btn).data('total');
  mostrarListado( id, control );
  var id = $(btn).data('id');
  var fecemis = $(btn).data('fecemis');
  var fecvenc = $(btn).data('fecvenc');
  var saldo = $(btn).data('saldo');
  var pago = $(btn).data('pago');
  var total = $(btn).data('total');

  $('#modal_control').val("").val(control);
  $('#modal_numero').text("").text(id);
  $('#modal_fecha').text("").text(fecemis);
  $('#modal_vence').text("").text(fecvenc);
  $('#modal_saldo').text("").text(saldo);
  $('#modal_pagado').text("").text(pago);
  $('#myInput').val(saldo);
  $('#myModal').modal('show');
}

function mostrarListado( referencia, control ){
    $("#myTable").empty();
  var select = document.getElementById('mySelect');
  var options = select.options;

  for (var i = 0; i < options.length; i++) {
    options[i].disabled = false;
  }
  var html = '';
  console.log(referencia);
  html='  <table class="table table-bordered table-striped dt-responsive" id="tablaListadoCobro" width="100%">';
  html += '<thead>';
  html += '<tr>';
  html += '<th style="width:20%">FECHA</th>';
  html += '<th>DESC.</th>';
  html += '<th>Monto.</th>';
  html += '</tr>';
  html += '</thead>';
  html += '</table>';

  $("#mostrarListadoCobros").empty();
  $("#mostrarListadoCobros").append( html );

  $('#tablaListadoCobro').DataTable( {
    "destroy":true,
    "paging": false,       // Desactivar paginación
    "searching": false,    // Desactivar búsqueda
    "info": false,         // Desactivar información de la tabla
    "ordering": false,
    "ajax": {
      "url": "ajax/mostrarListadoCobros.php",
      "data": {
        "accion": "mostrarListadoCobros",
        "codigo": referencia,
        "control": control,
        "cliente": $("#codigo").val()
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

    },
    "columnDefs": [
      {
        width: '20%', 
        targets: 0
      },
      {
        width: '60%', 
        targets: 1
      },
      {
        width: '20%', 
        targets: 2
      }
    ]
  } );
}// Obtener referencias a los modales
var modal1 = document.getElementById('myModal');
var modal2 = document.getElementById('miModal');

// Obtener referencias a los botones
var openModal2Button = document.getElementById('openModal2');
var closeModal2Button = document.getElementById('closeModal2');
// Cuando se muestre el modal #miModal
$('#miModal').on('show.bs.modal', function () {
  // Ocultar el modal #myModal
  $('#myModal').modal('hide');
});

// Cuando se cierre el modal #miModal
$('#miModal').on('hidden.bs.modal', function () {
  // Mostrar el modal #myModal
  $('#myModal').modal('show');
});

$(document).on("click",".btnImprimirDocumento" , function()
{
  

  $('#myModal').modal('hide');
  modal1.style.display = 'none';
  var control    = document.getElementById('modal_numero').innerHTML;;
    var datos = new FormData();
    datos.append("accion", "mostrarDocumento");
    datos.append("control", control);
    datos.append("tiptran", "PAGOS");

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
      var modal = $('#miModal');
      var pdfViewer = modal.find('iframe');
      pdfViewer.attr('src', "data:application/pdf;base64," + datos.PDF);
      modal.modal('show');
      $('#myModal').modal('hide');
      
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
$(document).on("click","#printList" , function()
{
  var control    = document.getElementById('modal_numero').innerHTML;;
    var datos = new FormData();
    datos.append("accion", "mostrarDocumento");
    datos.append("control", control);
    datos.append("tiptran", "LISTAPAGOS");

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
      var modal = $('#miModal');
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
// Cargar opciones del select utilizando AJAX
var xhr = new XMLHttpRequest();
  xhr.open('GET', 'ajax/get_formas_pago.php');
  xhr.onload = function() {
    if (xhr.status === 200) {
      var data = JSON.parse(xhr.responseText);
      var select = document.getElementById('mySelect');
      for (var i = 0; i < data.length; i++) {
        var option = document.createElement('option');
        option.value = data[i].CODTAR;
        option.text = data[i].NOMBRE.substring(0, 15);
        select.appendChild(option);
      }
    } else {
      console.log('Error: ' + xhr.status);
    }
  };
  xhr.send();
  // Agregar opciones seleccionadas a la lista al hacer clic en el botón de agregar
  var addBtn  = document.getElementById('addBtn');
  var select  = document.getElementById('mySelect');
  var myInput = document.getElementById('myInput');
  var total   = document.getElementById('total');
  var saldo   = document.getElementById('modal_saldo');
  var cambio  = document.getElementById('cambio');
  
  select.addEventListener('change', function(event) {
    saldo = document.getElementById('saldo');
    event.preventDefault();
    var option = select.options[select.selectedIndex];
    if(!option.value)
    {
       return
    }
   // myInput.value = saldo.innerHTML;

  });
 /* var event = new Event('change');

// Seleccionar el elemento que deseas desencadenar el evento
var element = document.getElementById('total');

// Desencadenar el evento en el elemento seleccionado
element.dispatchEvent(event);*/
  addBtn.addEventListener('click', function(event) {
    event.preventDefault();
    var option = select.options[select.selectedIndex];
    var saldo   = document.getElementById('modal_saldo');
    var valor_input = parseFloat(myInput.value);
    if(!option.value)
    {
      alert("Seleccione una forma de pago"); return
    }
    if(!valor_input)
    {
      alert("Escriba un valor en la forma de pago"); return
    }
    console.log(valor_input);
    if(valor_input <= 0)
    {
      alert("Escriba un valor válido"); return
    }
    
    
    var pago_sel  = parseFloat(valor_input);
    var saldo_sel = parseFloat(saldo.innerHTML);
    if( pago_sel > saldo_sel && option.value != "01")
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

    if( pago_sel > saldo_sel && option.value != "01")
    {
      alert("El pago excede al saldo"); return
    }
    var list = document.getElementById('myTable');

    var list_table = document.createElement('div');
    list_table.className = 'row mb-2';
    list_table.setAttribute('data-id', option.value);

    var innerItem = document.createElement('label');
    innerItem.className = 'col-5 col-md-5';
    innerItem.innerHTML = option.text.substring(0, 10);
    innerItem.setAttribute('data-id', option.value);
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

    select.querySelector('option:checked').disabled = true;
    select.value = '';
    myInput.value = '';

    // Actualizar la sumatoria
    var total = document.getElementById('total');
      var saldo_total = 0 

  });

  // Eliminar opciones de la lista y volver a agregarlas al select
  var list = document.getElementById('myTable');
  list.addEventListener('click', function(event) {
    if (event.target.parentNode.classList.contains('delete-btn')) {
      var item = event.target.parentNode.parentNode.parentNode;
      
      var option = item.querySelector('label').innerHTML;
      var value = item.querySelector('label').getAttribute('data-id');
      var select = document.getElementById('mySelect');
      var optionElement = document.createElement('option');
      /*optionElement.value = value;
      optionElement.text = option.substring(0, 10); // Limitar a 10 caracteres
      select.appendChild(optionElement);*/
      item.parentNode.removeChild(item);
      select.querySelector('option[value="' + value + '"]').disabled = false;
      // Restar el valor del elemento eliminado del total
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
    }
  });

  // Obtener el botón y agregar un controlador de eventos para el clic
  $('#procesarCobro').click(function(event) {
    // Detener el comportamiento por defecto del botón
    event.preventDefault();

    var codigo = document.getElementById('codigo').value;
    var control = document.getElementById('modal_control').value;
    var nom_cliente = document.getElementById('nom_cliente').value;
    var ref = document.getElementById('modal_numero').innerHTML;
    var saldo = document.getElementById('modal_saldo').innerHTML;

    var items = document.querySelectorAll('#myTable label');
    if(items.length<1)
    {
      alert("Seleccione una forma de pago"); return
    }

    // Obtener los datos del formulario y convertirlo a un objeto JSON
    
    var data_fp = [];
    var pagado = 0;
    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      var id = item.getAttribute('data-id');
      var text = item.innerHTML;
      var input = item.nextElementSibling.querySelector('input');
      var value = input.value;
      pagado = (parseFloat(pagado) + parseFloat(value)).toFixed(2);
      data_fp.push({
        id: id,
        text: text,
        value: value
      });
    }
    
    var saldo_sel = parseFloat(saldo);
    if( pagado > saldo_sel )
    {
      alert("El monto no puede ser mayor que el saldo"); return
    }

      var datos = new FormData();
      event.preventDefault(); 
      datos.append( "accion", "grabarCobro" );
      datos.append( "codigo", codigo );
      datos.append( "control", control );
      datos.append( "ref", ref );
      datos.append("formasPago", JSON.stringify(data_fp));
      var codigocli = codigo.replace(/''/g, "'");
    // Realizar la petición AJAX utilizando fetch
    fetch('grabar_cobro.php', {
      method: 'POST',
      body: datos
    })
    .then(function(response) {
      // Verificar si la petición fue exitosa
      if (response.ok) {
        return response.text();
      } else {
        throw new Error('Error en la petición AJAX.');
      }
    })
    .then(function(data) {
      // La petición AJAX se realizó con éxito
      console.log('Respuesta del servidor:', data);
      
      // Mostrar la respuesta en un modal utilizando swal
      swal({
        type: "success",
        title: data ,
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      }).then((value) => {
        if (value) {

          window.location.href='lista_cobros.php?id='+codigocli+'&nom_cliente='+nom_cliente; // Reemplaza con la URL de destino
          $("#myTable").empty();
          mostrarListado( ref );
        }
      });
    })
    .catch(function(error) {
      // Ocurrió un error durante la petición AJAX
      console.error('Error en la petición:', error);
      
      // Mostrar el mensaje de error en un modal utilizando swal
      swal({
        title: 'Error en la petición',
        text: 'Ocurrió un error durante la petición AJAX.',
        type: 'error'
      });
    });
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
