
<?php include_once("cabecera.php"); ?>
<style>
body{ 
  border: 0px solid black;
  padding: 0px;
  background: url('imgs/fondo2.png') no-repeat fixed center;
  background-repeat: no-repeat;
  background-size: cover;
  background-color:#BCBDC0;
}
label{
    color:white;
}

.row.form-group {
    width: 100%; /* para que ocupe todo el ancho disponible */
    justify-content: center; /* centrar el contenido horizontalmente */
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
      <div class='titulo text-center'>
            <!-- <i class="fas fa-user-friends" style='font-size:50px;float:left;padding-right:10px;'></i> -->
            <!-- <span><strong>Módulo de Emisión<br /> de Pedidos y Presupuestos</strong></span> -->
            <div style="position:absolute; top:5px; left:0; auto"><a onClick="location.href='facturacion_electronica.php'"><i class="fa fa-chevron-left" aria-hidden="true" style='color:#fff;font-size:16px;margin-left:15px;'></i></a></div>   
            <h4 style='color:#fff;'>Configuración</h4><hr/>
      </div>
    </div>
    
  </div> <!-- fin container -->
</div> <!-- fin content -->
    <div class="content">
        <div class="container">
            <div class="row form-group border-bottom mb-2">
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" name= "FacElect" id="FacElect">
                            <label class="form-check-label" for="FacElect">
                            Facturación Electrónica?
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                            <label class="form-check-label" for="FacElect">
                            Formato?
                            </label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_factura" id="inlineRadio1" value="PDF">
                            <label class="form-check-label" for="inlineRadio1">PDF</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_factura" id="inlineRadio2" value="Ticket">
                            <label class="form-check-label" for="inlineRadio2">TICKET</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group border-bottom mt-3 mb-3">
                <div class="col-md-1 text-right">
                    <label for="pac">PAC</label>
                </div>
                <div class="col-md-4 text-left">
                    <div class="form-group">
                        <select name="pac" id="pac" class="form-control">
                            <option value="">Seleccione...</option>
                            <option label="The Factory HKA" value="1" >The Factory HKA</option>
                            <option label="EBI" value="2">EBI</option>
                            <option label="Digifact" value="3">Digifact</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1 text-right">
                    <label for="ambientePac">Ambiente</label>
                </div>
                <div class="col-md-4 text-left">
                    <div class="form-group">
                        <select name="ambientePac" id="ambientePac" class="form-control">
                            <option value="">Seleccione...</option>
                            <option label="Demo" value="1" >Demo</option>
                            <option label="Producción" value="2">Producción</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row form-group border-bottom mt-3 mb-3"  style="display:none;">
                <div class="col-md-4" style="display:none;">
                    <div class="form-group">
                        <label for="numDocFiscal">Num. Doc. Fiscal</label>
                        <input type="text" class="form-control" id="numDocFiscal" name="numDocFiscal" value="" readonly>
                    </div>
                </div>
                <div class="col-md-8" style="display:none;">
                    <div class="form-group">
                        <label for="formatoCAFE">Dirección Envío</label>
                        <input type="text" class="form-control" id="DireccionEnvio" name="DireccionEnvio" value="1" readonly="readonly">
                    </div>
                </div>
            </div>
            <span id="pac_thefactory" class="container">
                <div class="row form-group border-bottom mt-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="formatoCAFE">Token Empresa</label>
                            <input type="text" class="form-control" id="tokenEmpresa" name="tokenEmpresa" value="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="formatoCAFE">Token Password</label>
                            <input type="text" class="form-control" id="tokenPassword" name="tokenPassword" value="1">
                        </div>
                    </div>
                </div>
                <div class="row form-group border-bottom mt-2 mb-4"  style="display: none;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tipoEmision">Tipo de Emisión</label>
                            <select name="tipoEmision" id="tipoEmision" class="form-control">
                                <option label="01: Autorización de Uso Previa, operación normal." value="01" selected="selected">01: Autorización de Uso Previa, operación normal.</option>
                                <option label="02: Autorización de Uso Previa, operación en contingencia." value="02">02: Autorización de Uso Previa, operación en contingencia.</option>
                                <option label="03: Autorización de Uso Posterior, operación normal." value="03">03: Autorización de Uso Posterior, operación normal.</option>
                                <option label="04: Autorización de Uso posterior, operación en contingencia." value="04">04: Autorización de Uso posterior, operación en contingencia.</option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tipoSucursal">Tipo de Sucursal</label>
                            <input type="text" class="form-control" id="tipoSucursal" name="tipoSucursal" value="1">
                        </div>
                    </div>
                </div>
                <div class="row form-group border-bottom mt-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codigoSucursal">Código de Sucursal</label>
                            <input type="text" class="form-control" id="codigoSucursal" name="codigoSucursal" value="0000">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="puntoFacturacionFiscal">Punto de Facturación Fiscal</label>
                            <input type="text" class="form-control" id="puntoFacturacionFiscal" name="puntoFacturacionFiscal" value="<?= 000 ?>">
                        </div>
                    </div>
                    <div class="col-md-4" style="display: none;">
                        <div class="form-group">
                            <label for="naturalezaOperacion">Naturaleza de Operación</label>
                            <input type="text" class="form-control" id="naturalezaOperacion" name="naturalezaOperacion" value="01">
                            
                        </div>
                    </div>
                </div>
                <div class="row form-group mt-3 mb-3"  style="display: none;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tipoOperacion">Tipo de Operación</label>
                            <input type="text" class="form-control" id="tipoOperacion" name="tipoOperacion" value="1">
                        </div>
                    </div>
                    <div class="col-md-4 border-bottom">
                        <div class="form-group">
                            <label for="destinoOperacion">Destino de Operación</label>
                            <select id="destinoOperacion" name="destinoOperacion" class="form-control">
                                <option value="1" selected="">1: Panama</option>
                                <option value="2">2: Extranjero</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group mt-3 mb-3" style="display: none;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Formato CAFE</label>
                            <input type="text" class="form-control" id="formatoCAFE" name="formatoCAFE" value="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="entregaCAFE">Entrega CAFE</label>
                            <input type="text" class="form-control" id="entregaCAFE" name="entregaCAFE" value="1">
                        </div>
                    </div>
                </div>
            </span>
            <div id="pac_digifact" class="container">
                <div class="row form-group  mt-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Usuario</label>
                            <input type="text" class="form-control" id="tokenEmpresaDigifact" name="tokenEmpresaDigifact" value="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Password</label>
                            <input type="text" class="form-control" id="tokenPasswordDigifact" name="tokenPasswordDigifact" value="1">
                        </div>
                    </div>
                </div>
                <div class="row form-group border-bottom mt-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="codigoSucursal">Código de Sucursal</label>
                            <input type="text" class="form-control" id="codigoSucursalDigifact" name="codigoSucursalDigifact" value="0000">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="puntoFacturacionFiscal">Punto de Facturación Fiscal</label>
                            <input type="text" class="form-control" id="puntoFacturacionFiscalDigifact" name="puntoFacturacionFiscalDigifact" value="<?= 000 ?>">
                        </div>
                    </div>
                    <div class="col-md-4" style="display: none;">
                        <div class="form-group">
                            <label for="naturalezaOperacion">Naturaleza de Operación</label>
                            <input type="text" class="form-control" id="naturalezaOperacionDigifact" name="naturalezaOperacionDigifact" value="01">
                            
                        </div>
                    </div>
                </div>
                <div class="row form-group  mt-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">RUC</label>
                            <input type="text" class="form-control" id="taxIdDigifact" name="taxIdDigifact" value="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">DV</label>
                            <input type="text" class="form-control" id="dvFigifact" name="dvFigifact" value="">
                        </div>
                    </div>
                </div>
                <div class="row form-group  mt-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Nombre Fiscal</label>
                            <input type="text" class="form-control" id="nombreDigifact" name="nombreDigifact" value="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Email</label>
                            <input type="text" class="form-control" id="emailFigifact" name="emailFigifact" value="">
                        </div>
                    </div>
                </div>
                <div class="row form-group  mt-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Teléfono</label>
                            <input type="text" class="form-control" id="tlfIdDigifact" name="tlfIdDigifact" value="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Dirección</label>
                            <input type="text" class="form-control" id="direcdigifact" name="direcdigifact" value="">
                        </div>
                    </div>
                </div>
                <div class="row form-group mt-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Coordenadas</label>
                            <input type="text" class="form-control" id="coordDigifact" name="coordDigifact" value="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formatoCAFE">Ubicación</label>
                            <input type="text" class="form-control" id="ubiDigifact" name="ubiDigifact" value="">
                        </div>
                    </div>
                </div>
                <div class="row form-group border-bottom mt-3 mb-3">
                    <div class="col-md-4 mb-3">
                        <span style="color:white;top: 0;position: absolute;">Es jurídico?</span>
                        <input type="checkbox" class="form-control" id="esJuridicoDigifact" name="esJuridicoDigifact" value="1">
                        
                    </div>
                    <div class="col-md-4">
                        
                    </div>
                </div>
            </div>
            <div class="row form-group border-bottom mt-3 mb-3">
                <div class="col-md-12 text-center form-group ">
                    <a href="#" class="btn btn-primary" id="guardarFactElectConfig" style = "background-color: #E6E7E9 !important; color: #000 !important; border: none !important;">
                        <b><i class="fa fa-edit"></i>&nbsp;Guardar</b>
                    </a>
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
    });
$(document).ready(function() {
    var rellenarCampos = function () {
        
        $.ajax({
            url: 'ajax/obtenerConfigFacElec.php', // Reemplaza 'obtener_campos.php' con la URL correcta del archivo PHP que devuelve los campos
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var campos = response.data;

                    console.log(campos);
                    if(campos){
                        // Llenar los campos con los valores obtenidos
                        var fac_elec = campos.FACELECT === "0" ? false : true;
                        var ESjURIDICO = campos.JURIDICO_DIGI === "1" ? true : false;
                        $('#FacElect').prop('checked', fac_elec );
                        $('input[name="tipo_factura"][value="' + campos.TIPO_FACTURA + '"]').prop('checked', true);
                        $('#DireccionEnvio').val( campos.DIRECCIONENVIO );
                        $('#tokenEmpresa').val( campos.USUARIO_RUC );
                        $('#tokenPassword').val( campos.CONTRASEÑA );
                        $//('#tipoEmision').val( campos.TIPOEMISION );
                        //$('#tipoSucursal').val( campos.TIPOSUCURSAL );
                        $('#numDocFiscal').val( campos.NROINIFAC );
                        $('#codigoSucursal').val( campos.CODSUC );
                        $('#puntoFacturacionFiscal').val( campos.CODPFACT );
                        //$('#naturalezaOperacion').val( campos.NATURALEZAOPERACION );
                        //$('#tipoOperacion').val( campos.TIPOOPERACION );
                        //$('#destinoOperacion').val( campos.DESTINOOPERACION );
                        //$('#formatoCAFE').val( campos.FORMATOCAFE );
                        // $('#entregaCAFE').val( campos.ENTREGACAFE );
                        $('#pac').val( campos.PAC );
                        $('#ambientePac').val( campos.AMBIENTE );
                        $('#dvFigifact').val( campos.DV_DIGI );                    
                        $('#tokenEmpresaDigifact').val( campos.USUARIO_DIGI );
                        $('#tokenPasswordDigifact').val( campos.PASSWORD_DIGI );
                        $('#codigoSucursalDigifact').val( campos.CODIGOSUCURSALEMISOR );
                        $('#puntoFacturacionFiscalDigifact').val( campos.PUNTOFACTURACIONFISCAL );
                        $('#taxIdDigifact').val( campos.RUC_DIGI );
                        $('#nombreDigifact').val( campos.NEMPRESA_DIGI );
                        $('#emailFigifact').val( campos.EMAIL_DIGI );
                        $('#tlfIdDigifact').val( campos.TEL_DIGI );
                        $('#direcdigifact').val( campos.DIRECCION_DIGI );
                        $('#coordDigifact').val( campos.COORDENADAS_DIGI );
                        $('#ubiDigifact').val( campos.UBICACION_DIGI );
                        $('#esJuridicoDigifact').prop('checked', fac_elec );

                        if(campos.PAC == 1 || campos.PAC == 2){
                                
                            $("#pac_thefactory").show();
                            $("#pac_digifact").hide();
                        }else{
                                
                            $("#pac_thefactory").hide();
                            $("#pac_digifact").show();
                        }

                    }else{
                        
                        $("#pac_thefactory").hide();
                            $("#pac_digifact").hide();
                    }
                } else {
                    swal('¡Error!', 'Error al obtener los campos. Por favor, inténtalo de nuevo.', 'error');
                }
            },
            error: function() {
                swal('¡Error!', 'Error en la solicitud AJAX. Por favor, inténtalo de nuevo.', 'error');
            }
        });

    };
    rellenarCampos();

    $('#guardarFactElectConfig').off().on("click", function(e) {
        e.preventDefault(); // Evitar el envío del formulario normalmente
        enviarFormulario();
    });
    $(document).keydown(function(e) {
    if (e.which === 13) {
        e.preventDefault(); // Evitar el comportamiento predeterminado de la tecla Enter
        enviarFormulario();
        
    }
    });


    function changePAC () {
        var pacProveedor = $('#pac :selected').val();
        var ambiente = $('#ambientePac :selected').val();
        var direccionEnvio;


        if (pacProveedor == '1') {
            direccionEnvio = (ambiente == '1')
                ? "https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl"
                : "http://emision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl";
            $("#pac_thefactory").show();
            $("#pac_digifact").hide();
        } else if (pacProveedor == '2') {
            direccionEnvio = (ambiente == '1')
                ? "http://demointegracion.ebi-pac.com/ws/obj/v1.0/Service.svc?wsdl"
                : "http://emision.ebi-pac.com/ws/obj/v1.0/Service.svc?wsdl";
            $("#pac_thefactory").show();
            $("#pac_digifact").hide();
        } else {
            direccionEnvio = (ambiente == '1')
                ? "https://pactest.digifact.com.pa/pa.com.apinuc/api"
                : "https://apinuc.digifact.com.pa/api";
            $("#pac_thefactory").hide();
            $("#pac_digifact").show();
        }

        $('[name=DireccionEnvio]').val(direccionEnvio);
    }


    $("#pac").on("change",function(){
        changePAC();
    });
    $("#ambientePac").on("change", function(){
        changeAmbientePac();    
    });
    function changeAmbientePac () {
        var ambiente = $('#ambientePac :selected').val();
        var pacProveedor = $('#pac :selected').val();
        var direccionEnvio;
        if (ambiente == '1') {
            if (pacProveedor == '1') {
                direccionEnvio = "https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl";
                $("#pac_thefactory").show();
                $("#pac_digifact").hide();
            } else if (pacProveedor == '2') {
                direccionEnvio = "https://demointegracion.ebi-pac.com/ws/obj/v1.0/Service.svc?wsdl";
                $("#pac_thefactory").show();
                $("#pac_digifact").hide();
            } else  {
                direccionEnvio = "https://pactest.digifact.com.pa/pa.com.apinuc/api";
                $("#pac_thefactory").hide();
                $("#pac_digifact").show();
            } 
        } else {
            if (pacProveedor == '1') {
                direccionEnvio = "https://emision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?wsdl";
                $("#pac_thefactory").show();
                $("#pac_digifact").hide();
            } else if (pacProveedor == '2') {
                direccionEnvio = "https://emision.ebi-pac.com/ws/obj/v1.0/Service.svc?wsdl";
                $("#pac_thefactory").show();
                $("#pac_digifact").hide();
            } else {
                // Proveedor desconocido para ambiente distinto de 1, asignar una URL predeterminada o mostrar un mensaje de error
                direccionEnvio = "https://apinuc.digifact.com.pa/api";
                $("#pac_thefactory").hide();
                $("#pac_digifact").show();
            }
        }

        $('[name=DireccionEnvio]').val(direccionEnvio);
    }
  function enviarFormulario() {
    
    if(!$('#pac').val()){
        alert("Debe seleccionar un PAC");
        return false;
    }
    if(!$('#ambientePac').val()){
        alert("Debe seleccionar un Ambiente, Demo o Producción");
        return false;
    }
    // Verificar si se ha seleccionado algún radio button
    if ( !$("input[name='tipo_factura']:checked").val() ) 
    {
        alert("Seleccione el formato de factura");
        return false;
    }
    // Obtener los datos del formulario
    var formData = new FormData();
    formData.append('FacElect', $('#FacElect').prop('checked'));
    formData.append('tipo_factura', $('input[name=tipo_factura]:checked').val());
    formData.append('DireccionEnvio', $('#DireccionEnvio').val());
    formData.append('tokenEmpresa', $('#tokenEmpresa').val());
    formData.append('tokenPassword', $('#tokenPassword').val());
    formData.append('tipoEmision', $('#tipoEmision').val());
    formData.append('tipoSucursal', $('#tipoSucursal').val());
    formData.append('codigoSucursal', $('#codigoSucursal').val());
    formData.append('numDocFiscal', $('#numDocFiscal').val());
    formData.append('puntoFacturacionFiscal', $('#puntoFacturacionFiscal').val());
    formData.append('naturalezaOperacion', $('#naturalezaOperacion').val());
    formData.append('tipoOperacion', $('#tipoOperacion').val());
    formData.append('destinoOperacion', $('#destinoOperacion').val());
    formData.append('formatoCAFE', $('#formatoCAFE').val());
    formData.append('entregaCAFE', $('#entregaCAFE').val());
    formData.append('pac', $('#pac').val());
    formData.append('ambientePac', $('#ambientePac').val());
    formData.append('tokenEmpresaDigifact', $('#tokenEmpresaDigifact').val());
    formData.append('dvFigifact', $('#dvFigifact').val());
    formData.append('tokenPasswordDigifact', $('#tokenPasswordDigifact').val());
    formData.append('taxIdDigifact', $('#taxIdDigifact').val());
    formData.append('codigoSucursalDigifact', $('#codigoSucursalDigifact').val());
    formData.append('puntoFacturacionFiscalDigifact', $('#puntoFacturacionFiscalDigifact').val());
    formData.append('nombreDigifact', $('#nombreDigifact').val());
    formData.append('emailFigifact', $('#emailFigifact').val());
    formData.append('tlfIdDigifact', $('#tlfIdDigifact').val());
    formData.append('direcdigifact', $('#direcdigifact').val());
    formData.append('coordDigifact', $('#coordDigifact').val());
    formData.append('ubiDigifact', $('#ubiDigifact').val());
    var esjuridico = $('#esJuridicoDigifact').prop('checked');
    formData.append('esJuridicoDigifact', esjuridico);

    

    
    // Realizar la solicitud AJAX
    $.ajax({
      url: 'ajax/guardarFactElecConfig.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        console.log(response.mensaje);
        // Manejar la respuesta del servidor
        if (response == 'success') {
            swal('¡Éxito!', 'Los datos se guardaron correctamente.', 'success');
            rellenarCampos();
        } else {
            swal('¡Error!', 'Error al guardar los datos. Por favor, inténtalo de nuevo.', 'error');
        }
      },
      error: function() {
        alert('Error en la solicitud AJAX. Por favor, inténtalo de nuevo.');
      }
    });
  }
});
</script>  

</body>
</html> 
