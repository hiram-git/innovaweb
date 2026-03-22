
$(document).on("click","#btn_empaque" , function(event){
    event.preventDefault();
    var codigo = $(this).attr("data-id");
    var almacen = $(this).attr("data-almacen");
    var precio_check = "";
    
    if(document.getElementById("precio_0").checked){
    //precio libre
    precio_check="precio"+document.getElementById("precio_default").value;;
    }else if(document.getElementById("precio_1").checked){
    precio_check="precio1";
    }else if(document.getElementById("precio_2").checked){
    precio_check="precio2";
    }else if(document.getElementById("precio_3").checked){
    precio_check="precio3";
    }else if(document.getElementById("precio_4").checked){
    precio_check="precio4";
    }else if(document.getElementById("precio_5").checked){
    precio_check="precio5";
    }
    if(canControl){
        mostrarListado();

    }
    function mostrarListado(){
        var html = '';
        html='  <table class="table table-bordered table-striped dt-responsive" id="miTabla" width="100%">';
        html += '<thead>';
        html += '<tr>';
        html += '<th class="hide-on-mobile">CODPRO</th>';
        html += '<th>EMPAQUE</th>';
        html += '<th>CANTIDAD</th>';
        html += '<th>PRECIO</th>';
        html += '<th class="hide-on-mobile">CONTROLEMP</th>';
        html += '<th></th>';
        html += '</tr>';
        html += '</thead>';
        html += '</table>';
        

        $("#listadoEmpaque").empty();
        $("#listadoEmpaque").append( html );
        $('#mymodeltask').modal('hide');
        $('#modal2').modal('show');
        
    
        var table = $('#miTabla').DataTable( {
            createdRow: function (row, data, index) {
                if (data[0]) {
                    $('td', row).eq(0).addClass('hide-on-mobile');
                    $('td', row).eq(4).addClass('hide-on-mobile');
                }
            },
            "destroy":true,
            "ajax": {
              "url": "ajax/mostrarEmpaques.php",
              "data": {
                  "accion": "mostrarEmpaques",
                  "CODPRO": codigo,
                  "precio": precio_check,
                  "almacen": almacen
              },
              "method":"POST"
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
    
        $('#miTabla tbody').on('click', 'tr', function () {
            var data = table.row( this ).data();
            console.log(data);
            if(confirm('Desea Agregar el empaque '+data[1]+'?') == true){
                let htmlString = data[5];

                let parser = new DOMParser();
                let doc = parser.parseFromString(htmlString, 'text/html');
                let button = doc.querySelector('button');

                let total = parseFloat(data[3]);
                let imppor = parseFloat(button.getAttribute('IMPPOR').replace(' ','').replace(',', ''));
                let subtotal = (total  / ((100+imppor)/100));
                subtotal = parseFloat(subtotal).toFixed(2)
                
                impuesto = total-subtotal;
                cargar_data(button.getAttribute('CODPRO'), data[1], total, button.getAttribute('IMPPOR'), button.getAttribute('COSTOACT'), button.getAttribute('COSTOPRO'), button.getAttribute('GRUPOINV'), button.getAttribute('CODDEP'), button.getAttribute('LINEAINV'), button.getAttribute('Precio_fijo'), button.getAttribute('disponible'), button.getAttribute('tipo_tarea'), button.getAttribute('EXENTO'), data[4],button.getAttribute('TIPINV'),button.getAttribute('PROCOMPUESTO'))

                $('#modal2').modal('hide');
                
            }
        } );
    }
});
$(document).on("click","#btn_comp" , function(event){
    event.preventDefault();
    var codigo = $(this).attr("data-id");
    var almacen = $(this).attr("data-almacen");
    var precio_check = "";
    
    if(document.getElementById("precio_0").checked){
    //precio libre
    precio_check="precio"+document.getElementById("precio_default").value;;
    }else if(document.getElementById("precio_1").checked){
    precio_check="precio1";
    }else if(document.getElementById("precio_2").checked){
    precio_check="precio2";
    }else if(document.getElementById("precio_3").checked){
    precio_check="precio3";
    }else if(document.getElementById("precio_4").checked){
    precio_check="precio4";
    }else if(document.getElementById("precio_5").checked){
    precio_check="precio5";
    }
    if(canControl){
        mostrarListado();

    }
    function mostrarListado(){
        var html = '';
        html='  <table class="table table-bordered table-striped dt-responsive" id="miTablaComp" width="100%">';
        html += '<thead>';
        html += '<tr>';
        html += '<th class="hide-on-mobile">CODPRO</th>';
        html += '<th>EMPAQUE</th>';
        html += '<th>CANTIDAD</th>';
        html += '<th>PRECIO</th>';
        html += '<th></th>';
        html += '</tr>';
        html += '</thead>';
        html += '</table>';
        

        $("#listadoEmpaque").empty();
        $("#listadoEmpaque").append( html );
        $('#mymodeltask').modal('hide');
        $('#modal2').modal('show');
        
    
        var table = $('#miTablaComp').DataTable( {
            createdRow: function (row, data, index) {
                if (data[0]) {
                    $('td', row).eq(0).addClass('hide-on-mobile');
                }
            },
            "destroy":true,
            "ajax": {
              "url": "ajax/mostrarComponentes.php",
              "data": {
                  "accion": "mostrarComponentes",
                  "CODPRO": codigo,
                  "precio": precio_check,
                  "almacen": almacen
              },
              "method":"POST"
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
    
        $('#miTablaComp tbody').on('click', 'tr', function () {
            var data = table.row( this ).data();
            console.log(data);
            if(confirm('Desea Agregar el producto compuesto '+data[1]+'?') == true){
                let htmlString = data[4];

                let parser = new DOMParser();
                let doc = parser.parseFromString(htmlString, 'text/html');
                let button = doc.querySelector('button');

                let total = parseFloat(data[3]);
                let imppor = parseFloat(button.getAttribute('IMPPOR').replace(' ','').replace(',', ''));
                let subtotal = (total  / ((100+imppor)/100));
                subtotal = parseFloat(subtotal).toFixed(2)
                console.log(button.getAttribute('procompuesto'));
                impuesto = total-subtotal;
                cargar_data(button.getAttribute('CODPRO'), data[1], total, button.getAttribute('IMPPOR'), button.getAttribute('COSTOACT'), button.getAttribute('COSTOPRO'), button.getAttribute('GRUPOINV'), button.getAttribute('CODDEP'), button.getAttribute('LINEAINV'), button.getAttribute('Precio_fijo'), button.getAttribute('disponible'), button.getAttribute('tipo_tarea'), button.getAttribute('EXENTO'), '',button.getAttribute('TIPINV'),"1")

                $('#modal2').modal('hide');
                
            }
        } );
    }
});