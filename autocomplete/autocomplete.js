let arr;
function autocomplete(inp) {
  if((sessionStorage.getItem('clientes_av')=='') || (sessionStorage.getItem('clientes_av')==null)){
    load_data();
  }else{
    //alert(sessionStorage.getItem('clientes_av'));
    arr=sessionStorage.getItem('clientes_av').split('|');
  }
  
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
    
    //alert(arr[3]);
    //alert("addEventListener");
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      //let posicion;
      //var contador;
      for (i = 0; i < arr.length; i++) {
        /*check if the item starts with the same letters as the text field value:*/
        //posicion = arr[i].substr(0, val.length).toUpperCase().indexOf(val.toUpperCase());
        //if (posicion !== -1){
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) {
              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              recargar(inp.value);
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
          //contador++;
        }
        /*
        if(contador>11){
            alert("rompiendo el ciclo "+i);
            break;
        }*/
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
    
    //alert("addEventListener keydown");
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    //alert("addActive");
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    //alert("removeActive");
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    //alert("closeAllLists");
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}





/*execute a function when someone clicks in the document:*/
document.addEventListener("click", function (e) {
    //alert("closeAllLists click");
    closeAllLists(e.target);
});
}

function load_data(){
    layer_data=document.getElementById("layer_data");
    document.getElementById("input_buscar").placeholder="Procesando...";
    document.getElementById("input_buscar").disabled=true;
    //let totalString = [];
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            //alert(this.responseText);
            var editor=this.responseText;
            editor=editor.substring(0, editor.length - 1);
            arr=editor.split('|');
            sessionStorage.setItem('clientes_av', editor);
            document.getElementById("input_buscar").disabled=false;
            document.getElementById("input_buscar").focus();
            document.getElementById("input_buscar").placeholder="";
        }
    };
    xhttp.open("GET", "autocomplete/load_data.php", true);
    xhttp.send();
    //return tString;
}
/*
function clean(){
    document.getElementById("input_buscar").value="";
}*/

