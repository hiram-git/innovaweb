<?php 
//include_once "permiso.php";
//session_start();
//include_once "config/db.php";
//session_start();
?>

            <select class="browser-default custom-select" id="provincia" onChange="cargar_distrito(provincia.value);">
              <?php
              /*recuperando todo los productos comandados*/
              $sql3="SELECT * FROM BASEPROVINCIA";
              //echo $sql3;

              $sentencia4 = $base_de_datos->prepare($sql3, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
              ]);

              $sentencia4->execute();
              echo "<option selected></option>";
              while ($data2 = $sentencia4->fetchObject()){
                //$pormaxdespar=$data2->PORMAXDESPAR;
                echo "<option value='".$data2->NOMBREEGEO1."'>".$data2->DESNOMBREEGEO1."</option>";
              }
              ?>
            </select>

            <div id='layer_distrito'>
            <select class="browser-default custom-select" id="distrito" onChange="cargar_corregimiento(distrito.value);">
              <option selected></option>
            </select>
            </div>

            <div id='layer_corregimiento'>
            <select class="browser-default custom-select">
              <option selected></option>
            </select>
            </div>
