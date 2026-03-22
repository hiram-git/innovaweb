<div class="container d-flex justify-content-center">
        <!-- <div class="card" style='border: 0px solid rgba(0,0,0,.125) !important;background-color:transparent;border-radius:5px 5px 10px 10px;'> -->
          <table border="0" width='100%'>
            <?php
            //var_dump($_SESSION['aDatos']);
            $subtotal_sum=0;
            $itbm_sum=0;
            $total_sum=0;
            $max=sizeof($_SESSION['aDatos']);
            //var_dump($_SESSION['aDatos']);
            for($i=0; $i<$max; $i++) {
              if ($i%2==0){
                  //echo "el $numero es par";
                  $color='#EDEDF5';
              }else{
                  $color='#D1D2D4';
              }
              $k=0; 
              foreach ($_SESSION['aDatos'][$i] as $key=> $val){
                  $k++;
                  if($k==1){ // codigo del producto
                      $cod_prod=$val;
                  }else if($k==2){ // nombre del producto
                      $nom_prod=$val;
                  }else if($k==3){ // precio del producto
                      $precio=$val;
                  }else if($k==4){ // cantidad del producto
                      $descuento=$val;
                  }else if($k==5){ // itbm
                      $cantidad=$val;
                  }else if($k==6){ // itbm
                      $itbm=$val;
                  }
              } // fin foreach
              $descuento_fmt=round($descuento, 4);
              $subtotal=round(($cantidad*$precio)-$descuento, 2);
              $total=round(($subtotal*($itbm/100))+$subtotal, 2);
              

              $subtotal_sum+=$subtotal;
              $itbm_sum+=($subtotal*($itbm/100));
              $total_sum+=($subtotal*($itbm/100))+$subtotal;
            ?>
              <tr style='background-color:<?php echo $color;?>;'>
                <td colspan="4" style='text-align:left;'>  
                  <?php echo "<b><span style='color:#FF5001;'>".$cod_prod." //</span> <br />".$nom_prod."</b>";?>
                </td>  
                <td style='text-align:right;'>
                  <a href="eliminar_item.php?iditem=<?php echo $i;?>&nom_cliente=<?php echo $nom_cliente;?>"><img src='imgs/trash_icon.png' width='20px'/></a><br /><br />
                  <a href="form_producto_edit.php?iditem=<?php echo $i;?>&nom_cliente=<?php echo $nom_cliente;?>"><img src='imgs/edit_icon.png' width='20px'/></a>
                </td>              
              </tr>
              <tr style='background-color:<?php echo $color;?>;color:#828285;'>
                <th>Cant.</th>
                <th>Precio</th>
                <th>Dcto. Parc.</th>
                <th>Subtotal</th>
                <th>Total</th>
              </tr>
              <tr style='background-color:<?php echo $color;?>;'>
                <td><?php echo $cantidad;?></td>
                <td><?php echo $precio;?></td>
                <td><?php echo $descuento_fmt;?></td>
                <td><?php echo $subtotal;?></td>
                <td><?php echo $total;?></td>
              </tr>
            <?php
            } // fin for

            if($max>0){  
            ?>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>SubTotal</b></td>
                  <td><b><span id='idsubtotal'><?php echo number_format($subtotal_sum, 2);?></span></b></td>
                </tr>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>ITMBS</td>
                  <td><span id='iditbm'><?php echo number_format($itbm_sum, 2);?><span></td>
                </tr>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>Total</b></td>
                  <td><b><span id='idtot'><?php echo number_format($total_sum, 2);?><span></b></td>
                </tr>
            <?php
            }
            ?>
          </table>
        <!-- </div> -->
      </div>