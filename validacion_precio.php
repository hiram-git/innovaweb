<?php
include_once "permiso.php";
include_once "config/db.php";
$precio=$_GET['precio'];
$codpro=$_GET['codpro'];
if(($_GET['precio']!='') AND ($_GET['codpro']!='')){
    $sql3="SELECT COSTOPRO, PRECIO1, PRECIO2, PRECIO3, PRECIO4, PRECIO5, PROCOMPUESTO FROM INVENTARIO WHERE CODPRO='".$codpro."'";
    //echo $sql3;
    $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
    $total_reg = $result->fetchColumn();
    if($total_reg!=''){
        $sentencia4 = $base_de_datos->prepare($sql3, [
        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
        ]);

        $sentencia4->execute();
        while ($data2 = $sentencia4->fetchObject()){
            $costopro=round($data2->COSTOPRO, 2);
            $precio1=round($data2->PRECIO1, 2);
            $precio2=round($data2->PRECIO2, 2);
            $precio3=round($data2->PRECIO3, 2);
            $precio4=round($data2->PRECIO4, 2);
            $precio5=round($data2->PRECIO5, 2);
            $PROCOMPUESTO=round($data2->PROCOMPUESTO, 2);
        }
    }

    $sql3="SELECT VENTAMENOS, VENTAMENOSPVP FROM BASEUSUARIOS WHERE CODUSER='".$_SESSION['coduser']."'";
    //echo $sql3;
    $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
    $total_reg = $result->fetchColumn();
    if($total_reg!=''){
        $sentencia4 = $base_de_datos->prepare($sql3, [
        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
        ]);

        $sentencia4->execute();
        while ($data2 = $sentencia4->fetchObject()){
            //$data2->VENTAMENOS;
            //$data2->VENTAMENOSPVP;

            if($data2->VENTAMENOS==1){
                echo "1|no importa precio ni costo";
            }else if($data2->VENTAMENOSPVP==1){
                if( $PROCOMPUESTO != 1){
                    if($precio>$costopro){
                        echo "1|precio debajo del minimo pero por encima del costo";
                    }else{
                        echo "0|El precio esta por debajo del costo";
                    }
                }else{
                    
                    echo "1|.";
                }
            }else{
                /*$sql3="SELECT PVPMENOR FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
                //echo $sql3;
                $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
                $total_reg = $result->fetchColumn();
                if($total_reg!=''){
                    $sentencia4 = $base_de_datos->prepare($sql3, [
                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                    ]);
                    
                    $sentencia4->execute();
                    while ($data2 = $sentencia4->fetchObject()){
                        //$data2->PVPMENOR;
                        if($data2->PVPMENOR==1){
                            if($precio>=$precio1){
                                echo "1|$precio >= $precio1";
                            }else{
                                echo "0|El precio esta por debajo del precio mínimo";
                            }
                        }else if($data2->PVPMENOR==2){
                            if($precio>=$precio2){
                                echo "1|$precio >= $precio2";
                            }else{
                                echo "0|El precio esta por debajo del precio mínimo";
                            }
                        }else if($data2->PVPMENOR==3){
                            if($precio>=$precio3){
                                echo "1|$precio >= $precio3";
                            }else{
                                echo "0|El precio esta por debajo del precio mínimo";
                            }
                        }else if($data2->PVPMENOR==4){
                            if($precio>=$precio4){
                                echo "1|$precio >= $precio4";
                            }else{
                                echo "0|El precio esta por debajo del precio mínimo";
                            }
                        }else if($data2->PVPMENOR==5){
                            if($precio>=$precio5){
                                echo "1|$precio >= $precio5";
                            }else{
                                echo "0|El precio esta por debajo del precio mínimo";
                            }
                        }
                    }
                }*/
                echo "1|.";
            }
        }
    }else{
        echo "0|no existe usuario";
    }
}else{
    echo "3";
}
?>