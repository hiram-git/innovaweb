var can=1;
var canControl=true;
    /*esta funcion hace la carga del items insertado en el input*/
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
      //debugger;
      var tipocliente = document.getElementById("tipocliente").value;
      //if(texto_buscar!=''){
        $("#mymodeltask").modal('show');
        texto_buscar=texto_buscar.replace("+", "|");
        layer_prod=document.getElementById("layer_prod");
        layer_prod.innerHTML="<div style='padding:20px 20px 20px 20px;text-align:center;'><i class='fa fa-cog fa-spin fa-3x fa-fw' style='color:#FD5001;'></i><br /><br />Cargando data...</div>";
      
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                //alert(this.responseText);
                layer_prod.innerHTML=this.responseText;
                $("#mymodeltask").modal('show');
                
                //$("#Modal_Comensales").modal('hide');
            }
        };
        xhttp.open("GET", "buscar_prod.php?txt_buscar="+texto_buscar+"&precio="+precio_select+"&tipocliente="+tipocliente+"&CodAlmacen="+CodAlmacen+"&origen=pag_prod", true);
        xhttp.send();
      //}
}

    /*funcion que al darle click al items desde el div procede a insertarlo en todo el formulario los datos relacionado al items*/
  
function cargar_data(codpro, descrip, precio1, itbm, costoact, costopro, grupoinv, coddep, lineainv, precio1_noformt, disponible, tarea, exento, codempaque = '', tipo_producto = 0, prod_compuesto = 0){
      //alert(precio1+"----"+precio1_noformt+"---"+Number.parseFloat(precio1));
      if((document.getElementById("input_buscar").value!==codpro) && (document.getElementById("input_buscar").value!=='')){
        can = 1 ;
      }
      var ventamenos = document.getElementById("ventamenos").value;
      var actfacexi  = document.getElementById("actfacexi").value;
  
      if(tarea=='presupuesto'){
          document.getElementById("input_buscar").value=codpro;
          document.getElementById("descripcion").value=descrip;
          precio1 =Number.parseFloat(precio1).toFixed(2);
          document.getElementById("precio").value=precio1;
          document.getElementById("precio1_noformt").value=precio1_noformt;
          document.getElementById("itbm").value=itbm;
          document.getElementById("costoact").value=costoact;
          document.getElementById("costopro").value=costopro;
          document.getElementById("grupoinv").value=grupoinv;
          document.getElementById("coddep").value=coddep;
          document.getElementById("lineainv").value=lineainv;
          document.getElementById("exento").value=exento;
          /*document.getElementById("precio1").innerHTML="Precio1: <label style='color:#F15A24;'>"+precio1+"</label><br />";
          document.getElementById("precio2").innerHTML="Precio2: <label style='color:#F15A24;'>"+precio2+"</label><br />";
          document.getElementById("precio3").innerHTML="Precio3: <label style='color:#F15A24;'>"+precio3+"</label><br />";*/

          ///document.getElementById("hits").innerHTML=can;
          document.getElementById("descuento").value=0;
          //can=document.getElementById("cantidad").value;
          calc=can*precio1;
          //calc = calc.toFixed(2);
          calc =Number.parseFloat(calc).toFixed(2);
          document.getElementById("cantidad").value=can;
          //document.getElementById("layer_prod").innerHTML="";
          
          /*calc=(cantidad*precio1)-((cantidad*precio1)*(descuento/100));
          calc = Number(calc.toFixed(2));*/
          document.getElementById("total").innerHTML="<b>Total: "+calc+"</b>";
      }else{
        console.log("can: "+can+" - disponible: "+disponible+" - ventamenos: "+ventamenos+" - actfacexi: "+actfacexi+" - tipo_producto: "+tipo_producto+" - prod_compuesto: "+prod_compuesto);
        if(can<=disponible || ventamenos === "1" || actfacexi === "1" || tipo_producto === "1" || prod_compuesto === "1"){
          document.getElementById("input_buscar").value=codpro;
          document.getElementById("descripcion").value=descrip;
          document.getElementById("precio").value=precio1;
          document.getElementById("precio1_noformt").value=precio1_noformt;
          document.getElementById("itbm").value=itbm;
          document.getElementById("costoact").value=costoact;
          document.getElementById("costopro").value=costopro;
          document.getElementById("grupoinv").value=grupoinv;
          document.getElementById("coddep").value=coddep;
          document.getElementById("lineainv").value=lineainv;
          document.getElementById("exento").value=exento;
          document.getElementById("codempaque").value=codempaque;
          /*document.getElementById("precio1").innerHTML="Precio1: <label style='color:#F15A24;'>"+precio1+"</label><br />";
          document.getElementById("precio2").innerHTML="Precio2: <label style='color:#F15A24;'>"+precio2+"</label><br />";
          document.getElementById("precio3").innerHTML="Precio3: <label style='color:#F15A24;'>"+precio3+"</label><br />";*/

          ///document.getElementById("hits").innerHTML=can;
          document.getElementById("descuento").value=0;
          //can=document.getElementById("cantidad").value;
          calc=can*precio1;
          //calc = calc.toFixed(2);
          calc =Number.parseFloat(calc).toFixed(2);
          document.getElementById("cantidad").value=can;
          //document.getElementById("layer_prod").innerHTML="";
          
          /*calc=(cantidad*precio1)-((cantidad*precio1)*(descuento/100));
          calc = Number(calc.toFixed(2));*/
          document.getElementById("total").innerHTML="<b>Total: "+calc+"</b>";
        }else{
          canControl = false;
          swal("Alerta!", "Esta sobrepasando la cantidad de producto disponible!", {buttons:false,});
        }

        
      }
      $("#mymodeltask").modal('hide');
}


input_buscar.addEventListener("focus", function(e) {
      document.getElementById("a_buscar").style.pointerEvents = "auto";
      document.getElementById("stack").style.color = "#0CB3F9";
      //color:#0CB3F9;
});
    
function cantidad_mas(modo){
      //alert(can);
      if(document.getElementById("cantidad").value!=''){
        can++;
        document.getElementById("cantidad").value=can;
        //alert(can);
        calcular("solo_calcular", modo);
      }
}

function cantidad_reset(){
      //alert(document.getElementById("cantidad").value);
      if(document.getElementById("cantidad").value!=''){
        //document.getElementById("cantidad").value=can++;
        can=document.getElementById("cantidad").value;
        //calcular();
      }
}

function validar_prod_disponible(origen, modo){
        if(origen=='bton_mas'){
            if(document.getElementById("cantidad").value!=''){
                can++;
                document.getElementById("cantidad").value=can;
            }
        }
      //alert('validando prod disponible');
        temp=can;
        //alert(temp);
        codalmacen=formatear_texto(document.getElementById("codalmacen").value);
        codpro=formatear_texto(document.getElementById("input_buscar").value);
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        //alert('Data cargada!');
                        //alert(this.responseText);
                        //document.getElementById("label_alert").innerHTML=this.responseText;
                        myStr=this.responseText;
                        var strArray = myStr.split("|");
                        if(strArray[0]==1){
                          
                          if(origen=='bton_mas'){
                            calcular("solo_calcular", modo);
                          }else if(origen=='textbox'){
                            calcular("solo_calcular", modo);
                          }else{
                            validar_precio('boton_transac', modo);                     
                            //cargar_prod();
                          }                  
                        }else if(strArray[0]==3){
                          
                        }else{
                          //alert("Este precio no cumple con las reglas del juego");
                          //document.getElementById("cantidad").value=can--;
                          //alert('menos');
                          //document.getElementById("cantidad").value=can--;
                          //can=can--;
                          if(origen=='bton_mas'){
                            if(document.getElementById("cantidad").value!=''){
                                can--;
                                document.getElementById("cantidad").value=can;
                            }
                          }

                          if(document.getElementById("cantidad").value!=''){
                            //alert(strArray[1]);
                            swal("Alerta!", strArray[1], {buttons:false,});
                          }
                          //alert(strArray[1]);
                          //document.getElementById("label_alert").innerHTML=strArray[1];
                          //document.getElementById("label_alert").innerHTML=can;
                        }
                        
                    }
        };
        xhttp.open("GET", "validar_prod_disponible.php?codalmacen="+codalmacen+"&codpro="+codpro+"&cantidad="+temp, true);
        xhttp.send();
      
}

function validar_precio(origen, modo){
      //alert('validando precio');
      precio=document.getElementById("precio").value;
      //codpro=document.getElementById("input_buscar").value;
      codpro=formatear_texto(document.getElementById("input_buscar").value);
      //alert(codpro);
      /*validamos si el precio cumple con la regla de precio y costo*/
      var xhttp = new XMLHttpRequest();
              xhttp.onreadystatechange = function() {
                  if (this.readyState == 4 && this.status == 200) {
                      //alert('Data cargada!');
                      //alert(this.responseText);
                      myStr=this.responseText;
                      var strArray = myStr.split("|");
                      if(strArray[0]==1){
                        if(origen=='textbox'){
                          calcular("solo_calcular", modo);
                        }else{
                          calcular("calcular_cargar", modo);
                        }                  
                      }else if(strArray[0]==3){
                        
                      }else{
                        //alert("Este precio no cumple con las reglas del juego");
                        //alert(strArray[1]);
                        swal("Alerta!", strArray[1], {buttons:false,});
                        //document.getElementById("label_alert").innerHTML=strArray[1];
                      }
                      
                  }
              };
      xhttp.open("GET", "validacion_precio.php?precio="+precio+"&codpro="+codpro, true);
      xhttp.send();
}

function calcular(ejecutar, modo){
    //alert("calcular");
      inp=document.getElementById("input_buscar").value;
      if(inp!=""){
        cantidad=document.getElementById("cantidad").value;
        if(cantidad>0){
          precio=document.getElementById("precio").value;
          if(precio>0){
            descuento1=document.getElementById("descuento").value;
            var flag=false;
            if(descuento1.includes('%')){
              flag=true;
              //alert("encontro %");
            }

            descuento=parseFloat(descuento1);
            pormaxdespar=document.getElementById("pormaxdespar").value !== "" ? document.getElementById("pormaxdespar").value : 0;
            cantidad=document.getElementById("cantidad").value;
            precio=document.getElementById("precio").value;

            //alert(descuento+"---"+pormaxdespar);
            //descuento=document.getElementById("descuento").value;
            //alert(pormaxdespar);
            ban=false;
            
            if(flag){
              
              console.log(pormaxdespar+"|"+descuento);
              if(descuento<=parseFloat(pormaxdespar)){
                desc=((cantidad*precio)*(descuento/100));
                calc=(cantidad*precio)-((cantidad*precio)*(descuento/100));
                //calc = Number(calc.toFixed(2));
                calc =Number.parseFloat(calc).toFixed(2);
                document.getElementById("total").innerHTML="<b>Total: "+calc+"</b>";
                document.getElementById("label_alert").innerHTML="";
                ban=true;
                //document.getElementById("bton_asignar").disabled = false; 
              }else{

                swal("Alerta!", "% Descuento no puede ser mayor al asignado al cliente o al usuario especial!", {buttons:false,});
              }
            }else{
              //alert(parseFloat(pormaxdespar));
              //alert(descuento+"<"+cantidad+"*"+precio);
              if(descuento<(cantidad*precio)){
                //alert(descuento+"<="+parseFloat(pormaxdespar));
                desc=descuento;
                total = (cantidad*precio);
                calc=(cantidad*precio)-descuento;
                calc =Number.parseFloat(calc).toFixed(2);
                let porc_desc = ((desc/total)*100);
                console.log(porc_desc+"|"+descuento)
                if(porc_desc<=parseFloat(pormaxdespar) ){
                    //calc = Number(calc.toFixed(2));
                    document.getElementById("total").innerHTML="<b>Total: "+calc+"</b>";
                    document.getElementById("label_alert").innerHTML="";
                    ban=true;
                  //document.getElementById("bton_asignar").disabled = false;
                }else{
                  swal("Alerta!", "Monto del descuento no puede ser mayor al porcentaje asignado al cliente o al usuario especial", {buttons:false,});
                  //document.getElementById("descuento").focus();
                }
              }else{
                if(descuento==''){
                  swal("Alerta!", "Descuento no puede estar en blanco", {buttons:false,});
                }else{
                  swal("Alerta!", "Descuento no puede ser mayor al monto de la transacción", {buttons:false,});
                }
                
              }
            }

            /*************************************************************************/
            /*esta parte solo se ejecuta cuando se da clic al botnon agregar producto*/
            /*depende del proceso del archivo que solicita las funciones             */
            /*************************************************************************/

            if((ban==true) && (ejecutar=='calcular_cargar')){
              if(modo=='edit'){
                /*************************************************************************/
                /*esta parte solo se ejecuta para la parte de editar form_product_edit   */
                /*************************************************************************/
                codigo=formatear_texto(document.getElementById("input_buscar").value);
                nombre=document.getElementById("descripcion").value;
                //alert(nombre);
                nombre = nombre.replaceAll("+", "|");
				        nombre = formatear_texto(nombre);
                precio=document.getElementById("precio").value;
                iditem=document.getElementById("iditem").value;
                cantidad=document.getElementById("cantidad").value;
                itbm=document.getElementById("itbm").value;
                nota=document.getElementById("nota").value;
                var empaque = document.getElementById("codempaque");
                if (empaque !== null) {
                  // Accede a la propiedad 'value' del empaque
                  var codempaque = empaque.value;
                  // Realiza otras operaciones con el valor
                } else {
                  // El empaque no existe o es nulo, maneja el error de acuerdo a tus necesidades
                  var codempaque = "";
                }
                nota = formatear_texto(nota);

                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        //alert(this.responseText);
                        myStr=this.responseText;
                        var strArray = myStr.split("|");
                        document.getElementById("input_buscar").value="";
                        document.getElementById("descripcion").value="";
                        document.getElementById("precio").value="";
                        document.getElementById("descuento").value="";
                        document.getElementById("cantidad").value='';
                        document.getElementById("itbm").value=0;                     
                        window.history.back();
                    }
                };
                xhttp.open("GET", "guardar_prod.php?codigo="+codigo+"&nombre="+nombre+"&precio="+precio+"&descuento="+desc+"&cantidad="+cantidad+"&itbm="+itbm+"&iditem="+iditem+"&nota="+nota+"&codempaque="+codempaque, true);
                xhttp.send();
                /*************************************************************************/
                /*esta parte solo se ejecuta para la parte de editar form_product_edit   */
                /*************************************************************************/
              }else{ 
                /*************************************************************************/
                /*esta parte solo se ejecuta para la parte de insertar form_product      */
                /*************************************************************************/
                var opcionSeleccionada = document.querySelector('input[name="precio_"]:checked').dataset.id;
                codigo=formatear_texto(document.getElementById("input_buscar").value);
                nombre=document.getElementById("descripcion").value;
                nombre = nombre.replaceAll("+", "|");
				        nombre = formatear_texto(nombre);
                precio=document.getElementById("precio").value;
                precio_sel= document.querySelector('input[name="precio_"]:checked').dataset.id;
                precio_noformt=document.getElementById("precio1_noformt").value
                costoact=document.getElementById("costoact").value;
                costopro=document.getElementById("costopro").value;
                grupoinv=document.getElementById("grupoinv").value;
                coddep=document.getElementById("coddep").value;
                lineainv=document.getElementById("lineainv").value;
                cantidad=document.getElementById("cantidad").value;
                itbm=document.getElementById("itbm").value;
                codalmacen=formatear_texto(document.getElementById("codalmacen").value);
                codvend=formatear_texto(document.getElementById("codvend").value);
                nomvend=formatear_texto(document.getElementById("nomvend").value);
                exento=document.getElementById("exento").value;
                nota=document.getElementById("nota").value;
                var empaque = document.getElementById("codempaque");
                if (empaque !== null) {
                  // Accede a la propiedad 'value' del empaque
                  var codempaque = empaque.value;
                  // Realiza otras operaciones con el valor
                } else {
                  // El empaque no existe o es nulo, maneja el error de acuerdo a tus necesidades
                  var codempaque = "";
                }
                nota = formatear_texto(nota);
                tipocliente=document.getElementById("tipocliente").value;

                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        //alert('Data cargada!');
                        //alert(this.responseText);
                        myStr=this.responseText;
                        var strArray = myStr.split("|");
                        document.getElementById("input_buscar").value="";
                        document.getElementById("descripcion").value="";
                        document.getElementById("precio").value="";
                        document.getElementById("precio1_noformt").value="";
                        document.getElementById("descuento").value="";
                        document.getElementById("cantidad").value='';
                        document.getElementById("itbm").value=0;    
                        document.getElementById("costoact").value=0;
                        document.getElementById("costopro").value=0;
                        document.getElementById("grupoinv").value=0;     
                        document.getElementById("coddep").value='';   
                        document.getElementById("lineainv").value='';  
                        document.getElementById("exento").value='';   
                        document.getElementById("nota").value='';     
                        document.getElementById("t_linea").innerHTML=strArray[0];
                        document.getElementById("t_items").innerHTML=strArray[1];
                        document.getElementById("t_subt").innerHTML=strArray[2];
                        /*despues de grabar en RAM el item procedemos remover el div con items*/
                        document.getElementById("layer_prod").innerHTML="";
                        //layer_prod.innerHTML=this.responseText;
                        window.history.go(-1);
                    }
                };
                xhttp.open("GET", "cargar_prod.php?codigo="+codigo+"&nombre="+nombre+"&precio="+precio+"&precio_sel="+precio_sel+"&descuento="+desc+"&cantidad="+cantidad+"&itbm="+itbm+"&costoact="+costoact+"&costopro="+costopro+"&grupoinv="+grupoinv+"&coddep="+coddep+"&lineainv="+lineainv+"&precio_noformt="+precio_noformt+"&codalmacen="+codalmacen+"&codvend="+codvend+"&nomvend="+nomvend+"&exento="+exento+"&nota="+nota+"&tipocliente="+tipocliente+"&codempaque="+codempaque, true);
                xhttp.send();
                /*************************************************************************/
                /*esta parte solo se ejecuta para la parte de insertar form_product      */
                /*************************************************************************/
              }
            }
            /*************************************************************************/
            /*esta parte solo se ejecuta cuando se da clic al botnon agregar producto*/
            /*depende del proceso del archivo que solicita las funciones             */
            /*************************************************************************/
            
          }else{
            //alert("El precio no es válido");
            //document.getElementById("bton_asignar").disabled = true; 
            //document.getElementById("label_alert").innerHTML="El precio no es válido";
            //document.getElementById("precio").focus();
            swal("Alerta!", "El precio no es válido", {buttons:false,});
          }
        }else{
          //if(document.getElementById("descripcion").value!=''){
            //document.getElementById("bton_asignar").disabled = true; 
            //document.getElementById("label_alert").innerHTML="La cantidad no es válida";
            //document.getElementById("cantidad").focus();
            if(cantidad==''){

            }else{
              swal("Alerta!", "La cantidad no es válida", {buttons:false,});
            }
           
          //}
        }
      }
}

function formatear_texto(texto){
  //alert(texto);
  texto=texto.replaceAll("#", "%23");
  texto=texto.replaceAll("&", "%26");
  texto=texto.replaceAll("'", "%27");
  //alert(texto);
  return texto;
}

function clean_field(){
      can=1;
      document.getElementById("input_buscar").value="";
                      document.getElementById("descripcion").value="";
                      document.getElementById("nota").value="";
                      document.getElementById("precio").value="";
                      document.getElementById("precio1_noformt").value="";
                      document.getElementById("descuento").value="";
                      document.getElementById("cantidad").value='';
                      document.getElementById("itbm").value=0;    
                      document.getElementById("costoact").value=0;
                      document.getElementById("costopro").value=0;
                      document.getElementById("grupoinv").value=0;     
                      document.getElementById("coddep").value='';   
                      document.getElementById("lineainv").value='';  
                      document.getElementById("exento").value='';  
                      document.getElementById("total").innerHTML="";
}

function countChars(){
      //alert(document.getElementById('coment'+obj).value.length);
      var maxLength = 79;
      var strLength = document.getElementById('nota').value.length;
      var charRemain = (maxLength - strLength);
      
      if(charRemain < 0){
          //document.getElementById("charNum"+obj).innerHTML = '<span style="color: red;">Has excedido el límite de carácteres '+maxLength+'</span>';
          document.getElementById('nota').value = document.getElementById('nota').value.substring(0, maxLength); 
      }else{
          document.getElementById("charNum").innerHTML = charRemain+' carácteres disponible';
      }
}

function extraer_precio(txt_precio, txt_cod){
      //alert(txt_cod);
      pr=document.getElementById("cantidad").value;
      if(pr!=''){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              //alert(this.responseText);
              document.getElementById("precio").value=Number(parseFloat(this.responseText).toFixed(2));
              calcular();
            }
        };
        xhttp.open("GET", "buscar_prod_precio.php?codigo="+txt_cod+"&t_precio="+txt_precio, true);
        xhttp.send();
      }
}

    var input_buscar20 = document.getElementById("input_buscar");
    input_buscar20.addEventListener("keyup", function(event) {
      if (event.keyCode === 13) {
      event.preventDefault();
      //alert('buscando...');
      buscar_prod(input_buscar20.value, document.getElementById('codalmacen').value);
      //Modal_lista_Fac_Ped_Cot(document.getElementById('tipo_fac').value);
      }
    });