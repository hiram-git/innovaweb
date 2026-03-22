<?php
include_once "permiso.php";
include_once "config/db.php";
$mesa=$_GET['mesa'];
$comensal=$_GET['comensal'];
$precio_tot=0;
$tip_tran="PEDxCLI";
$sql_com="";
if($comensal>0){
    $sql_com=" AND b.TaliaSeat='$comensal'";
}
echo "
<table class='tbl' cellspacing='0' cellpadding='0' border='0'>";
        /*echo "<tr><td style='border-bottom: 1px solid #8f8f8f;'></td><td style='border-bottom: 1px solid #8f8f8f;'></td><td style='border-bottom: 1px solid #8f8f8f;'>dfdsfs</td>
        </tr>";*/
        ?>
            <!-- <tr><td COLSPAN=2><hr style='border-top: 1px solid #ccc;'></td></tr> -->
        <?php
        /*recuperando todo los productos comandados*/
        $sql3="SELECT a.CONTROL, b.CODPRO, b.CANTIDAD, b.DESCRIP1, b.LineaPadreRPOS, B.LineaRPOS, b.PRECOSUNI, b.TaliaComment, b.FHPRODBASE, b.TaliaSeat  FROM TRANSACCMAESTRO AS a, TRANSACCDETALLES AS b WHERE a.CONTROL='".$id_control_maestro."' AND a.CONTROL=b.CONTROL AND a.TIPTRAN='$tip_tran' AND b.TIPTRAN='$tip_tran' AND b.COMPONENTE=0 AND TaliaItemState!='ST_PAD' $sql_com ORDER BY B.LineaRPOS ASC";
        //echo $sql3;
        $sentencia4 = $base_de_datos->prepare($sql3, [
        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
        ]);

        $sentencia4->execute();
        $background_colors = array('#FFEBEE', '#F3E5F5', '#E3F2FD', '#E0F2F1', '#F9FBE7', '#FFF3E0', '#FFCDD2', '#C5CAE9', '#B3E5FC','#DCEDC8','#FFECB3','#FFCCBC');
        while ($data = $sentencia4->fetchObject()){
            //echo $data->LineaPadreRPOS;
            //if($data->TaliaSeat==1){
                if($data->LineaPadreRPOS==-1){
                    $color="white";
                }else{
                    $color="#C6C6C6";
                }
            /*}else{
                $color=$background_colors[$data->TaliaSeat];
            }*/

            if($data->LineaPadreRPOS==-1){
                $identidad=$data->LineaRPOS." - ";
            }else{
                $identidad=$data->LineaPadreRPOS." - ";
            }

            $identidad="";
            //echo $data->Pedido." - ".$data->Estado." - ".$data->NumeroD;
            //$nropedcli=$data->NROPEDCLI;
            $taliacoments="";
            if($data->TaliaComment!="//////"){
                $taliacoments=$data->TaliaComment;
            }
            echo "<tr style='background-color:$color;color:#8C0429;'>
            <td style='padding-top:15px;' valign='top'><font style='color:black;'>
            ".$identidad."Cant: ".number_format($data->CANTIDAD)."</font><br />".$data->CODPRO."
            </td>";
            if($data->LineaPadreRPOS==-1){
            ?>
            <td style='padding-top:3px;text-align:center;' valign='top'>          
                <div class='contenedor_masmenos'>
                    <div class='container_table2' style='border:0px solid #ccc; width:auto;'>
                        
                        <div class='column'>
                            <div class="flotante4" id="btn_menos" onclick="menos('<?php echo $data->FHPRODBASE;?>', <?php echo $mesa;?>, <?php echo $data->LineaRPOS;?>)">--</div>
                        </div>
                        <?php
            echo "
                        &nbsp;
                        ";
                            ?>
                        <div class='column'>
                            <div class="flotante5" id="div_com<?php echo $data->FHPRODBASE;?>">
                            <span id="output-area<?php echo $data->FHPRODBASE;?>"><?php echo $data->TaliaSeat;?></span>
                            <input id="input<?php echo $data->FHPRODBASE;?>" type="hidden" value='<?php echo $data->TaliaSeat;?>'>
                            </div>
                        </div>
                            <?php
            echo "
                        &nbsp;
                        ";
                            ?>
                        <div class='column'>
                            <div class="flotante6" id="btn_mas" onclick="mas('<?php echo $data->FHPRODBASE;?>', <?php echo $mesa;?>, <?php echo $data->LineaRPOS;?>)">+</div>
                        </div>
                    </div>
                </div>
            </td>
            <?php
            }else{
            ?>
                <td style='padding-top:3px;text-align:center;' valign='top'>......</td>
            <?php 
            }
            echo "</tr>";
            echo "<tr style='background-color:$color;color:#8C0429;'>
            <td COLSPAN=2>".$data->DESCRIP1." <font style='color:black;'>".$taliacoments."</font>
            </td></tr>";

            if($data->LineaPadreRPOS==-1){
            ?>
            <tr><td COLSPAN=2><hr style='border-top: 1px solid #ccc;'></td></tr>
            <?php
            }
            //$precio_tot=$precio_tot+number_format($data->PRECOSUNI, 2,".", ",");


        }
       
        if($comensal>0){

        }else{
        //var_dump($_SESSION['aDatos']);
        $max=sizeof($_SESSION['aDatos']);
        for($i=0; $i<$max; $i++) {
            $k=0;
            //while (list ($key, $val) = each ($_SESSION['aDatos'][$i])) { 
            foreach ($_SESSION['aDatos'][$i] as $key=> $val){
                $k++;
                //echo "$key -> $val ,";
                if($k==1){
                    $cod_prod=$val;
                }else if($k==2){
                    $nom_prod=$val;
                }else if($k==3){
                    $precio=number_format($val, 2);
                }else if($k==4){
                    $cantidad=$val;
                }else if($k==5){ // itbm
                    $itbm=$val;
                }else if($k==7){
                    $codpadre=$val;
                }else if($k==8){
                    $iditems=$val;
                }else if($k==9){ // itbm
                    $tipo_items=$val;
                }
            } // inner array while loop
            

            $coments = trim(buscar_coments($iditems));
            //echo $searchByKey;
            if($codpadre==''){
                $color="white";
            }else{
                $color="#C6C6C6";
            }
            
            if($tipo_items=='CB'){
                echo "<tr style='background-color:$color;'><td style='padding-top:15px;' valign='top'>$cod_prod<br />$nom_prod</td>
                <td style='padding-top:30px;text-align:center;' COLSPAN=3>$cantidad</td>
                ";
                echo "</tr>";
            }else{
                $precio_tot=$precio_tot+$precio;
                echo "<tr style='background-color:$color;'><td style='padding-top:15px;' valign='top'>$cod_prod<br />$nom_prod
                
                </td>
                <td style='padding-top:20px;text-align:center;' valign='top' COLSPAN=3>$cantidad <a href='cargar_pedido.php?CodeProduct=$cod_prod&eliminar=2&codpadre=$codpadre&iditems=$iditems'><img src='imgs/delete.png' width='30px'/></a></td>
                ";
                echo "</tr>";
                echo "<tr><td COLSPAN=3>
                <textarea id='coment$iditems' class='form-control' placeholder='Introduzca el comentario' rows='1' cols='25' maxlength='59'
                onClick=\"abrir_coments('$cod_prod', '$iditems');\" onkeyup=\"countChars('$iditems');\" 
                onmouseout=\"grabar_coments('$cod_prod', '$iditems', '$coments')\">$coments</textarea>
                <p id='charNum$iditems'>0 characters</p></td></tr>
                ";
            }
        } // outer array for loop
        }
        ?>

</table>

<?php


function buscar_coments($keyVal) {
    foreach ($_SESSION['aDatos'] as $key => $val) {
        if ($keyVal == $val['iditems']) {
          //$resultSet['name'] = $val['name'];
          //$resultSet['key'] = $key;
          //$resultSet['iditems'] = $val['iditems'];
          return $val['coments'];
        }
    }
    return '';
}
?>