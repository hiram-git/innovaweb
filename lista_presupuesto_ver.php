<?php
include_once("permiso.php");
include_once "config/db.php";
if(!isset($_GET['id_presupuesto'])){
  $codigo="";
  $nomcliente="";
}else{
  $codigo=$_GET['id_presupuesto'];
  $nomcliente=$_GET['nom_presupuesto'];
}

//$_SESSION['aDatos'] = array();
//unset($_SESSION['tipo_tarea']);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<link href="https://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="autocomplete/mack.css">
<script type="text/javascript" src="autocomplete/autocomplete.js"></script>
</head>
<body>
  <header>
    <!--  <div class="logo_inside"><img src="imgs/logotipo.jpg" width="30%"></div>
    <div class="logo_inside">-->
    <div class="container d-flex justify-content-center">
      <div class="card mt-5" style='border: 0px solid rgba(0,0,0,.125) !important;'>
          <center><a href='index.php'><img src="imgs/logotipo.jpg" width='20%'></a><br />
            <strong>Sistema facturación, administración y contabilidad para Pymes</strong>
          </center>
      </div>
    </div>
</header>
<main>
    <br />
    <div class="content">
        <div class="container">
            <div class="table-responsive custom-table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th colspan="5"><h5><a onClick="window.history.back();"><i class="fa fa-long-arrow-left" aria-hidden="true" style='color:#F15A24;font-size:23px;'></i></a>&nbsp;&nbsp;Presupuestos - <?php echo $nomcliente;?></h5><hr/><br />
                            </th>
                        </tr>
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Cant.</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Desc. Parcial</th>
                            <th scope="col">SubTotal</th>
                            <th scope="col">Total</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT COUNT(*) total FROM TRANSACCDETALLES WHERE CONTROL='$codigo'";
                        $result = $base_de_datos->query($sql); //$pdo sería el objeto conexión
                        $total_reg = $result->fetchColumn();
                        if($total_reg>0){
                            $sql_button="SELECT CONTROL, CODPRO, DESCRIP1, CANTIDAD, PRECOSUNI, MONTOIMP, MONTODESCUENTO, MONTODESCUENTOGLO FROM TRANSACCDETALLES WHERE CONTROL='$codigo'";
                            $sentencia_b = $base_de_datos->prepare($sql_button, [
                                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                ]);
                            $subtotal_sum=0;
                            $montoimp_sum=0;
                            $total_sum=0;
                            $descglobal_sum=0;
                            $total=0;
                            $total_neto=0;
                            $sentencia_b->execute();
                            while ($data_b = $sentencia_b->fetchObject()){
                                //$_SESSION['parcontrol']=$data_b->CodeSucursal;
                                $subtotal=(($data_b->CANTIDAD*$data_b->PRECOSUNI)-round($data_b->MONTODESCUENTO, 2));
                                $subtotal_sum+=(($data_b->CANTIDAD*$data_b->PRECOSUNI)-round($data_b->MONTODESCUENTO, 2));
                                $descglobal_sum+=round($data_b->MONTODESCUENTOGLO, 2);
                                $total_neto+=(($data_b->CANTIDAD*$data_b->PRECOSUNI)-round($data_b->MONTODESCUENTO, 2))-round($data_b->MONTODESCUENTOGLO, 2);
                                $total+=(($data_b->CANTIDAD*$data_b->PRECOSUNI)-round($data_b->MONTODESCUENTO, 2))-round($data_b->MONTODESCUENTOGLO, 2);
                                $total_sum+=((($data_b->CANTIDAD*$data_b->PRECOSUNI)-round($data_b->MONTODESCUENTO, 2))-round($data_b->MONTODESCUENTOGLO, 2))+$data_b->MONTOIMP;
                                $montoimp_sum+=$data_b->MONTOIMP;
                            
                        ?>
                        <tr scope="row">
                            <td>
                            <?php echo $data_b->CODPRO;?>
                            </td>
                            <td><?php echo $data_b->DESCRIP1;?></td>
                            <td>
                            <?php echo round($data_b->CANTIDAD);?>
                            </td>
                            <td><?php echo number_format($data_b->PRECOSUNI, 2);?></td>
                            <td><span style='color:red;'>-<?php echo number_format($data_b->MONTODESCUENTO, 2);?></span></td>
                            <td><?php echo number_format($subtotal, 2);?></td>
                            <td><?php echo number_format($total, 2);?></td>                     
                            
                        </tr>
                        <tr class="spacer"><td colspan="100"></td></tr>
                        <?php
                            } // fin while
                        } // fin if
                        ?>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col">SubTotal</th>
                            <th scope="col"><?php echo number_format($subtotal_sum, 2);?></th>
                            
                        </tr>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col">Desc. Global</th>
                            <td scope="col"><span style='color:red;'>-<?php echo number_format($descglobal_sum, 2);?></span></td>
                            
                        </tr>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col">Total</th>
                            <th scope="col"><?php echo number_format($total, 2);?></th>
                            
                        </tr>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col">ITBMs</th>
                            <td scope="col"><span style='color:#7CB342;'>+<?php echo number_format($montoimp_sum, 2);?></span></td>
                            
                        </tr>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col">Total Neto</th>
                            <th scope="col"><?php echo number_format($total_sum, 2);?></th>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
  
</body>
</html> 
