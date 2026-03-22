<?php
include("permiso.php");
include("config/db.php");

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="imgs/logo.ico">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    <?php echo $_SESSION['titulo_web'];?>
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <!--<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  <link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">-->
  <link href="fontawesome-free-6.2.1/css/all.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/now-ui-dashboard.css?v=1.6.0" rel="stylesheet" />
  <!-- CSS Just for demo purpose, don't include it in your project -->
  <link href="assets/demo/demo.css" rel="stylesheet" />
</head>

<body class="invoice-page sidebar-mini ">
<?php
$idfac=$_GET['idfac'];
$idcontrol=$_GET['idcontrol'];
$tiptran=$_GET['tiptran'];

$MONTOIMP = 0;
if($tiptran=='PRE'){
  $titulo_doc='Cotización';
  $subtitulo_doc='Cotizado';
  $link = "pdf.php";
}else if($tiptran=='PEDxCLI'){
  $titulo_doc='Pedido';
  $subtitulo_doc='Pedido generado';
  $link = "pdf.php";
}else if($tiptran=='FAC'){
  $titulo_doc='Factura';
  $subtitulo_doc='Facturado';
  $link = "pdf_factura.php";
}
?>
<style>
    .button-link {
        display: inline-block;
        padding: 8px 16px;
        text-decoration: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        line-height: normal;
        margin-right: 10px; /* Añade un margen derecho de 10px */

    }
</style>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-transparent  bg-primary  navbar-absolute">
    <div class="container-fluid">
      <div class="navbar-wrapper">
            <div style="text-align: left;">
                <a class="button-link" onclick="window.history.go(-1)">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;<p>Volver</p>   
                </a>
            </div>
            <div style="text-align: right;">
                <a class="button-link" onclick="window.location.href='clientes.php?input_buscar='">
                    <i class="fa fa-home" aria-hidden="true"></i>&nbsp;&nbsp;<p>Inicio</p>   
                </a>
            </div>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->
  <?php
                            $sql1="SELECT TOP(1) NOMBRE, DIRECC1, DIRECC2, NUMFISCAL, IDIMPUESTO, IMPPOR FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
                            //echo $sql1;
                            $sentencia_b = $base_de_datos->prepare($sql1, [
                              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                            ]);
                            $sentencia_b->execute();
                            while ($data_b = $sentencia_b->fetchObject()){
                                $nom_empresa=$data_b->NOMBRE;
                                $dir_empresa=$data_b->DIRECC1." ".$data_b->DIRECC1;
                                $idimpuesto=$data_b->IDIMPUESTO;
                                $porcent_imp=$data_b->IMPPOR;
                                $ruc=$data_b->NUMFISCAL;
                            }
  ?>
  <?php
                            $subtotal=0;
                            $total=0;
                            $total_bruto=0;
                            $total_monto_desc_global=0;
                            $total_monto_desc_parcial=0;
                            $total_imp=0;
                            $label_imp=0;
                            $label_imp2="";
                            $sql1="SELECT CONTROL,
                              CODIGO,
                              DESCRIP1,
                              MONTOSUB,
                              MONTOIMP,
                              MONTOTOT,
                              NOMBRE,
                              DIRECCION,
                              MONTODES,
                              MONTODESCUENTO,
                              MONTOBRU,
                              PORDES,
                              FECEMISS,
                              CODVEN,
                              TIPOFACTURA 
                            FROM TRANSACCMAESTRO WHERE CONTROL='$idcontrol'";
                            $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                            $total_reg = $result->fetchColumn();
                            $porcentaje_descto="";
                            if($total_reg!=''){
                              //$sql_button="SELECT CONTROL, NUMREF, DESCRIP1, FECEMISS, MONTOSUB, MONTOTOT, TIPTRAN, NOMBRE FROM TRANSACCMAESTRO WHERE CODIGO='$codigo'";
                              $sentencia_b = $base_de_datos->prepare($sql1, [
                                      PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                  ]);
                              $sentencia_b->execute();
                              while ($data_b = $sentencia_b->fetchObject()){
                                //$row1 = $result1->fetch_assoc();
                                $detalle=$data_b->DESCRIP1;
                                $subtotal=$data_b->MONTOSUB;
                                $total_bruto=$data_b->MONTOBRU;
                                $porcentaje_descto=round($data_b->PORDES);
                                $total=$data_b->MONTOTOT;
                                $total_imp=$data_b->MONTOIMP;
                                $total_monto_desc_global=$data_b->MONTODES;
                                $total_monto_desc_parcial=$data_b->MONTODESCUENTO;
                                $nom_fac=$data_b->NOMBRE;
                                $dir=$data_b->DIRECCION;
                                $f_factura=$data_b->FECEMISS;
                                $codigo_cli_prov=$data_b->CODIGO;
                                $cod_ven=$data_b->CODVEN;
                                $tipo_factura=$data_b->TIPOFACTURA;
                                  
                                $MONTOIMP = $data_b->MONTOIMP;                                
                                $codigo_cli_prov              = str_replace("'", "''", $codigo_cli_prov);

                                $sql1="SELECT CODIGO,
                                  CONESPECIAL,
                                  PORRETIMP
                                FROM BASECLIENTESPROVEEDORES WHERE CODIGO='$codigo_cli_prov' AND TIPREG='1' AND INTEGRADO = '0';";
                                $r_cli = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                                $reg_cli = $r_cli->fetch(PDO::FETCH_ASSOC);
                                $CONESPECIAL = $reg_cli["CONESPECIAL"];
                                $PORRETIMP   = $reg_cli["PORRETIMP"];
                              }
                            
                            }
                            //echo "$total_reg entramos";
                      $diassemana = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
                      $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                      
                      $datetime = new DateTime($f_factura);
                      //echo $datetime->format('w');
                      $f_factura= $diassemana[$datetime->format('w')]." ".$datetime->format('d')." de ".$meses[$datetime->format('n')-1]. " del ".$datetime->format('Y');
  ?>
  <input type="hidden" id="nrocontrol" value="<?= $idcontrol ?>" />
  <input type="hidden" id="tiptran" value="<?= $tiptran ?>" />
  <input type="hidden" id="idfac" value="<?= $idfac ?>" />
  <div class="wrapper wrapper-full-page ">
    <div class="full-page invoice-page section-image" filter-color="black" data-image="assets/img/bg14.jpg">
      <!--   you can change the color of the filter page using: data-color="blue | green | orange | red | purple" -->
      <div class="content">
        <div class="container">
          <div class="col-md-6 ml-auto mr-auto">
            <div class="card card-invoice mt-5">
              <div class="card-header text-center" data-color-icon="warning">
                <div class="row">
                  <div class="col-3 text-left">
                  <a href='' id="btnImprimirDocumento" class="btn btn-primary btn-round btn-sm"><i class="fa fa-download" style='color:#FFFFFF;font-size:20px;' aria-hidden="true"></i></a>
                  </div>
                  <div class="col-6 text-center mt-3">
                    <span>FEL AUTORIZADA</span>
                  </div>
                  <div class="col-3 text-center">
                  <a href='<?= $link ?>'><button type="button" name="button" class="btn btn-primary btn-round btn-sm"><i class="fa fa-cloud-download" style='color:#FFFFFF;font-size:20px;' aria-hidden="true"></i></button></a>
                  </div>
                </div>
                <div class="row">
                  <div class="col-12">
                    <h4 class="card-title" ><?php echo $titulo_doc;?> <span class="font-weight-light">#<?php echo $idfac;?></span></h4>
                    <h6 class="card-description mt-3 font-weight-bold">
                      <hr><?php echo $subtitulo_doc;?> a <br /><h5 class="font-weight-bold text-capitalize"><?php echo "$nom_fac";?></h5><?php echo "$f_factura";?>
                    </h6>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-12">
                    <!-- <div class="table-responsive"> -->
                    <div>
                      <table border='0' cellpadding=7 width='100%'>
                        <thead>
                          <tr valign='top'>
                            <th>
                            <h6 class="font-weight-bold text-capitalize">Producto</h6>
                            </th>
                            <th >
                              <h6 class="font-weight-bold text-capitalize" style='text-align:center;'>Precio Unitario</h6>
                            </th>
                            <th >
                              <h6 class="font-weight-bold text-capitalize"  style='text-align:right;'>Total</h6>
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                            
                        <?php
                            $sql1="SELECT
                            TRANSACCDETALLES.CONTROL,
                            TRANSACCDETALLES.DESCRIP1,
                            TRANSACCDETALLES.CANTIDAD,
                            TRANSACCDETALLES.TOTAL,
                            TRANSACCDETALLES.MONTOIMP,
                            TRANSACCDETALLES.IMPPOR,
                            TRANSACCDETALLES.PRECOSUNI,
                            TRANSACCDETALLES.MONTODESCUENTO,
                            TRANSACCDETALLES.MONTODESCUENTOGLO,
                            CASE WHEN TRANSACCAMPLIADA.DESAPLIADA IS NULL OR TRANSACCAMPLIADA.DESAPLIADA = '' THEN 
                                '-'
                            ELSE
                                TRANSACCAMPLIADA.DESAPLIADA
                            END AS DESCRIP_NOTA
                        FROM
                            TRANSACCDETALLES
                        LEFT JOIN
                            TRANSACCAMPLIADA ON TRANSACCDETALLES.CONTROL = TRANSACCAMPLIADA.CONTROL AND
                            TRANSACCDETALLES.FECHORA = TRANSACCAMPLIADA.FECHORA
                        WHERE
                            TRANSACCDETALLES.CONTROL ='$idcontrol' AND TRANSACCDETALLES.COMPONENTE ='0'
												ORDER BY TRANSACCDETALLES.FECHORA ASC;";
                            $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                            $total_reg = $result->fetchColumn();
                            
                            $total_nota_entrega=0;
                            if($total_reg!=''){
                              $sentencia_b = $base_de_datos->prepare($sql1, [
                                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                  ]);
                              $sentencia_b->execute();
                              $porcent_imp=0;
                              while ($data_b = $sentencia_b->fetchObject()){
                                  $label_imp2="";
                                  if($data_b->IMPPOR>0){
                                    $porcent_imp=number_format($data_b->IMPPOR);
                                  }else{
                                    $label_imp2="(E)";
                                  }

                                  if($total_monto_desc_global>0){
                                    $total_=$data_b->PRECOSUNI*$data_b->CANTIDAD;
                                    $total_nota_entrega=$total_bruto;
                                  }else{
                                    $total_=$data_b->TOTAL;
                                    $total_nota_entrega=$subtotal;
                                  }
                                  $total_ = $data_b->TOTAL -  $data_b->MONTODESCUENTO;

                                  //$total_ = $total_ - $data_b->MONTODESCUENTO;
                                  $nota = "";
                                  if($data_b->DESCRIP_NOTA != "-"){
                                    $nota = "<span style='font-style: italic;margin-left: 10px;'>".$data_b->DESCRIP_NOTA."</span><br />";  
                                  }
                                  $total_neto=$total_nota_entrega-$total_monto_desc_global;
                                  echo "<tr>
                                  <td >
                                    ".$data_b->DESCRIP1."<br />
                                    ".$nota."
                                    <strong style='font-size:15px;'>Cantidad: ".round($data_b->CANTIDAD)."</strong><br />
                                    <strong style='font-size:15px;'>Desct. Parcial: $".number_format($data_b->MONTODESCUENTO, 2)."</strong><br />
                                    
                                  </td>
                                  <td style='text-align:center;'>
                                  $".number_format($data_b->PRECOSUNI, 2)."
                                  </td>
                                  <td style='text-align:right;'>
                                  $".number_format($total_, 2)." $label_imp2
                                  </td>
                                </tr>";
                                /*<strong style='font-size:15px;'>Desct. Global: ".number_format($row1["MONTODESCUENTOGLO"], 2)."</strong>*/
                              }
                            }
                          ?>
                          <tr>
                            <td class="font-weight-bold">
                              Total
                            </td>
                            <td></td>
                            <td style='text-align:right;'>
                              <h6>
                                <?php echo "$".number_format($total_nota_entrega, 2);?>
                              </h6>
                            </td>
                          </tr> 
                          <tr>
                            <td class="font-weight-bold">
                              Desct. Global&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $porcentaje_descto;?>%
                            </td>
                            <td></td>
                            <td style='text-align:right;'>
                              <h6>
                              <?php echo "$".number_format($total_monto_desc_global, 2);?>
                              </h6>
                            </td>
                          </tr>
                          <tr>
                            <td class="font-weight-bold">
                              Total Neto
                            </td>
                            <td></td>
                            <td style='text-align:right;'>
                              <h6>
                              <?php echo "$".number_format($total_neto, 2);?>
                              </h6>
                            </td>
                          </tr>
                          <tr>
                            <td class="font-weight-bold">
                              (<?php echo $porcent_imp;?>%) <?php echo $idimpuesto;?>
                            </td>
                            <td></td>
                            <td style='text-align:right;'>
                              <h6>
                                <?php echo "$".number_format($total_imp, 2);?>
                              </h6>
                            </td>
                          </tr>               
                          <tr>
                            <td class="font-weight-bold">
                              Total
                            </td>
                            <td></td>
                            <td style='text-align:right;'>
                              <h6>
                              <?php echo "$".number_format($total, 2);?>
                              </h6>
                            </td>
                          </tr> 
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div><?php                    
                  if($CONESPECIAL == "1" AND $_REQUEST["tiptran"] != "PEDxCLI"){

                    switch ($PORRETIMP) {
                      case 100:
                        $ValRetenc = (float)$MONTOIMP; 
                        $codRetencion = 1;
                        $decripRetencion = "Pago por servicio profesional al estado 100%";
                        break;
                      case 50:
                        $ValRetenc = (float)$MONTOIMP*0.5; 
                        $codRetencion = 4;
                        $decripRetencion = "Pago o acreditación por compra de bienes/servicios 50%";
                        break;
                      
                      default:
                        $ValRetenc = ((float)$MONTOIMP*($PORRETIMP/100)); 
                        $decripRetencion = "Otros (disminución de la retención)";
                        $codRetencion = 8;
                        break;
                    }
                    ?> 
                    <div class="row mt-4">
                      <div class="col-12">
                        <table>
                        <tr >
                          <td  width="80%">
                            <span class="font-weight-bold">Retención: </span><?php echo $decripRetencion; ?></td>
                          <td style='text-align:right;' width="20%">
                            <h6>
                              <?php echo "$".number_format($ValRetenc, 2);?>
                            </h6>
                          </td>
                        </tr> 
                        </table>
                      </div>
                    </div>
                    <?php
                  }
                ?>
                <div class="row mt-4">

                  <div class='col-md-6'><b>TIPO DE FACTURA:</b> <?php echo $tipo_factura ?>
                  </div>
                </div>
                <div class="row mt-4">

               <div class='col-md-6'><b>FORMA DE PAGO</b></div><div class='col-md-3'><b>MONTO</b></div> <div class='col-md-3'>&nbsp;</div>

                <?php 
                $sql_forma_pago =  "SELECT
                    T.MONTOPAG 
                    , T.FUNCION 
                    , B.NOMBRE  
                    FROM TRANSACCPAGOS AS T JOIN BASEINSTRUMENTOS B ON B.CODTAR = T.CODTAR  
                    WHERE T.CONTROL = '{$idcontrol}'";
                  $result = $base_de_datos->query($sql_forma_pago); //$pdo sería el objeto conexión
                  $formas_pago = $result->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($formas_pago as $forma_pago) {?>
                  <div class='col-md-6'><?= $forma_pago["NOMBRE"] ?></div><div class='col-md-3'><?= $forma_pago["MONTOPAG"] ?></div> <div class='col-md-3'>&nbsp;</div>
                  <?php }
                  ?>
                </div>
                <div class="row mt-5">
                  <div class="col-12 col-md-12">
                    <?php
                        $sql1="SELECT CODVEN, NOMBRE, NUMTEL, DIRCORREO FROM BASEVENDEDORES WHERE CODVEN='$cod_ven'";
                        //echo $sql1;
                        $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                        $total_reg = $result->fetchColumn();
                        $numtel_vendedor="";
                        $correo_vendedor="";
                        $nom_vendedor="";
                        if($total_reg!=''){
                          //$sql_button="SELECT CONTROL, NUMREF, DESCRIP1, FECEMISS, MONTOSUB, MONTOTOT, TIPTRAN, NOMBRE FROM TRANSACCMAESTRO WHERE CODIGO='$codigo'";
                          $sentencia_b = $base_de_datos->prepare($sql1, [
                                                      PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                                  ]);
                          $sentencia_b->execute();                     
                          while ($data_b = $sentencia_b->fetchObject()){
                            $nom_vendedor=$data_b->NOMBRE;
                            if($data_b->NUMTEL!=""){
                              $numtel_vendedor="<b><label>TEL.</label> ".utf8_encode($data_b->NUMTEL)."</b><br />";
                            }

                            if($data_b->DIRCORREO!=""){
                              $correo_vendedor="<b><label>EMAIL.</label> ".utf8_encode($data_b->DIRCORREO)."</b><br />";
                            }

                            //$ruc=$data_b->RIF;
                            //$dir_empresa=$data_b->DIRECC1;
                          }
                          
                        }
                    ?>
                    <h6 class="text-uppercase card-description font-weight-bold mb-3">
                    <?php echo $subtitulo_doc;?> por
                    </h6>
                    
                    <p class="mb-4">
                        
                        
                      <b><label>EMPRESA</label> <?php echo utf8_encode($nom_empresa);?></b><br />
                      <b><label>R.U.C.</label> <?php echo utf8_encode($ruc);?></b><br />
                      <?php
                      if($dir_empresa!=''){
                        echo "<b><label>DIRECCION</label> ".utf8_encode($dir_empresa)."</b><br />";
                      }
                      ?>
                    <b><label>VENDEDOR</label> <?php echo utf8_encode($nom_vendedor);?></b><br /> 
                    <?php echo $numtel_vendedor; echo $correo_vendedor;  ?>   
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--   you can change the color of the filter page using: data-color="blue | green | orange | red | purple" -->
      <?php
      $sql1="SELECT TOP(1) NOMBRE, DIRECC1, DIRECC2, NUMFISCAL, IDIMPUESTO, IMPPOR FROM BASEEMPRESA WHERE CONTROL='".$_SESSION['id_control']."'";
      //echo $sql1;
      $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
      $total_reg = $result->fetchColumn();
      if($total_reg!=''){
        //$sql_button="SELECT CONTROL, NUMREF, DESCRIP1, FECEMISS, MONTOSUB, MONTOTOT, TIPTRAN, NOMBRE FROM TRANSACCMAESTRO WHERE CODIGO='$codigo'";
        $sentencia_b = $base_de_datos->prepare($sql1, [
                                    PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                ]);
        $sentencia_b->execute();
        while ($data_b = $sentencia_b->fetchObject()){
          $nom_empresa=$data_b->NOMBRE;
          $dir_empresa=$data_b->DIRECC1." ".$data_b->DIRECC2;
          $idimpuesto=$data_b->IDIMPUESTO;
          $porcent_imp=$data_b->IMPPOR;
          $ruc=$data_b->NUMFISCAL;
        }
      }

      
      $subtotal=0;
                            $total=0;
                            $total_bruto=0;
                            $subtotal=0;
                            $total_monto_desc_global=0;
                            $total_monto_desc_parcial=0;
                            $total_imp=0;
                            $label_imp=0;
                            $label_imp2="";
                            $sql1="SELECT CODIGO, DESCRIP1, MONTOSUB, MONTOIMP, MONTOTOT, NOMBRE, DIRECCION, MONTODES, MONTODESCUENTO, MONTOBRU, PORDES, FECEMISS  FROM TRANSACCMAESTRO WHERE CONTROL='$idcontrol'";
                            //echo $sql1;
                            /*$result1 = $conn->query($sql1);
                            $porcentaje_descto="";
                            if ($result1->num_rows > 0) {*/

                            $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                            $total_reg = $result->fetchColumn();
                            $porcentaje_descto="";
                            if($total_reg!=''){
                              $sentencia_b = $base_de_datos->prepare($sql1, [
                                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                              ]);
                              $sentencia_b->execute();
                              while ($data_b = $sentencia_b->fetchObject()){
                                $detalle=$data_b->DESCRIP1;
                                $subtotal=$data_b->MONTOSUB;
                                $total_bruto=$data_b->MONTOBRU;
                                $porcentaje_descto=round($data_b->PORDES);
                                $total=$data_b->MONTOTOT;
                                $total_imp=$data_b->MONTOIMP;
                                $total_monto_desc_global=$data_b->MONTODES;
                                $total_monto_desc_parcial=$data_b->MONTODESCUENTO;
                                $nom_fac=$data_b->NOMBRE;
                                $dir=$data_b->DIRECCION;
                                $f_factura=$data_b->FECEMISS;
                                $codigo_cli_prov=$data_b->CODIGO;     
                                  
                                $MONTOIMP = $data_b->MONTOIMP;                           
                                
                                $codigo_cli_prov              = str_replace("'", "''", $codigo_cli_prov);
                                $sql1="SELECT CODIGO,
                                  CONESPECIAL,
                                  PORRETIMP
                                FROM BASECLIENTESPROVEEDORES WHERE CODIGO='$codigo_cli_prov' AND TIPREG = '1'  AND INTEGRADO = '0'";
                                $r_cli = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                                $reg_cli = $r_cli->fetch(PDO::FETCH_ASSOC);
                                $CONESPECIAL = $reg_cli["CONESPECIAL"];
                                $PORRETIMP   = $reg_cli["PORRETIMP"];
                              }
                            }
                            $diassemana = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
                            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                            
                            $datetime = new DateTime($f_factura);
                            //echo $datetime->format('w');
                            $f_factura= $diassemana[$datetime->format('w')]." ".$datetime->format('d')." de ".$meses[$datetime->format('n')-1]. " del ".$datetime->format('Y');
  
                            $file = "url_img.txt";
                            $fp = fopen($file, "r");
                            $content_img = fread($fp, filesize($file));
                            $arr_img=explode("|",$content_img);
                            $dir_img=$arr_img[0];
                            $width_img=$arr_img[1];
      $html="
      <div class='content'>
        <div class='container'>
          <div class='col-md-6 ml-auto mr-auto'>
            <div class='card card-invoice mt-5'>
              <div class='card-header text-center' data-color-icon='warning'>
                
                <div class='row'>           
                  <div class='col-12'>
                  <img src='$dir_img' width='$width_img' style='border:0px solid #ccc;position: absolute;top:-20px;left: 15px;'/>
                                  
                    <span style='font-size:22px;' >$titulo_doc</span> <span style='font-size:22px;'>#$idfac</span>
                    <h6 class='card-description mt-3 font-weight-bold'>
                      <hr><br /><br />$subtitulo_doc a <br /><h5 class='font-weight-bold text-capitalize' style='color:#000000 !important;'>".$nom_fac." </h5><br /><br /><br />
                    </h6>
                    $f_factura
                  </div>
                </div>
              </div>
              <div class='card-body'>
                <div class='row'>
                  <div class='col-12'>
                    <div class='table-responsive'>
                      <br /><br />
                      <table class='table mt-3' width='100%'>
                        <thead>
                          <tr>
                            <th class='pl-0'>
                            <h6 class='font-weight-bold text-capitalize'>Producto</h6>
                            </th>
                            <th class='px-0'>
                              <h6 class='font-weight-bold text-capitalize' style='text-align:center;'>Precio/Unitario</h6>
                            </th>
                            <th class='pr-0 text-right'>
                              <h6 class='font-weight-bold text-capitalize'>Total</h6>
                            </th>
                          </tr>
                        </thead>
                        <tbody>";
                            
                            $sql1="SELECT
                            TRANSACCDETALLES.CONTROL,
                            TRANSACCDETALLES.DESCRIP1,
                            TRANSACCDETALLES.CANTIDAD,
                            TRANSACCDETALLES.TOTAL,
                            TRANSACCDETALLES.MONTOIMP,
                            TRANSACCDETALLES.IMPPOR,
                            TRANSACCDETALLES.PRECOSUNI,
                            TRANSACCDETALLES.MONTODESCUENTO,
                            TRANSACCDETALLES.MONTODESCUENTOGLO,
                            CASE WHEN TRANSACCAMPLIADA.DESAPLIADA IS NULL OR TRANSACCAMPLIADA.DESAPLIADA = '' THEN 
                                '-'
                            ELSE
                                TRANSACCAMPLIADA.DESAPLIADA
                            END AS DESCRIP_NOTA
                        FROM
                            TRANSACCDETALLES
                        LEFT JOIN
                            TRANSACCAMPLIADA ON TRANSACCDETALLES.CONTROL = TRANSACCAMPLIADA.CONTROL AND
                            TRANSACCDETALLES.FECHORA = TRANSACCAMPLIADA.FECHORA
                        WHERE
                            TRANSACCDETALLES.CONTROL ='$idcontrol' AND  TRANSACCDETALLES.COMPONENTE = '0'
												ORDER BY TRANSACCDETALLES.FECHORA ASC;";
                            $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                            $total_reg = $result->fetchColumn();
                            //$porcentaje_descto="";
                            $total_neto=0;
                            if($total_reg!=''){
                                /*$subtotal=0;
                                $total=0;
                                $label_imp2="";*/
                                $sentencia_b = $base_de_datos->prepare($sql1, [
                                  PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                ]);
                                $sentencia_b->execute();
                                $porcent_imp=0;
                                $total_nota_entrega=0;
                                while ($data_b = $sentencia_b->fetchObject()){
                                    /*$subtotal+=$row1["TOTAL"];
                                    $total+=$row1["MONTOIMP"]+$row1["TOTAL"];*/

                                  $nota = "";
                                  if($data_b->DESCRIP_NOTA != "-"){
                                    $nota = "<span style='font-style: italic;margin-left: 10px;'>".$data_b->DESCRIP_NOTA."</span><br />";  
                                  }

                                    $label_imp2="";
                                    
                                    if($data_b->IMPPOR>0){
                                      $porcent_imp=number_format($data_b->IMPPOR);
                                    }else{
                                      $label_imp2="(E)";
                                    }

                                    if($total_monto_desc_global>0){
                                      $total_=$data_b->PRECOSUNI*$data_b->CANTIDAD;
                                      $total_nota_entrega=$total_bruto;
                                    }else{
                                      $total_=$data_b->TOTAL;
                                      $total_nota_entrega=$subtotal;
                                    }
                                    $total_ = $data_b->TOTAL -  $data_b->MONTODESCUENTO;
                                    $total_neto=$total_nota_entrega-$total_monto_desc_global;
                                    //echo "$total_neto=$total_nota_entrega-$total_monto_desc_global<br />";
                                    $html.= "<tr>
                                    <td class='pl-0'>
                                      ".$data_b->DESCRIP1."<br />
                                      ".$nota."
                                      <strong style='font-size:16px;'>Cantidad: ".round($data_b->CANTIDAD)."</strong><br />
                                      <strong style='font-size:16px;'>Desct. Parcial: $".round($data_b->MONTODESCUENTO)."</strong>
                                    </td>
                                    <td style='text-align:center;'>
                                    $".round($data_b->PRECOSUNI, 2)."
                                    </td>
                                    <td class='pr-0 text-right'>
                                    $".number_format($total_, 2)." $label_imp2
                                    </td>
                                  </tr>";
                                }
                            }
                      $html.="
                          <tr>
                            <td class='px-0 font-weight-bold'>
                            Total
                            </td>
                            <td></td>
                            <td class='px-0 text-right'>
                              
                                $".number_format($total_nota_entrega, 2)."
                              
                            </td>
                          </tr> 
                          <tr>
                            <td class='px-0 font-weight-bold'>
                              Desct. Global&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$porcentaje_descto%
                            </td>
                            <td></td>
                            <td class='px-0 text-right'>
                             
                              $".number_format($total_monto_desc_global, 2)."
                              
                            </td>
                          </tr>
                          <tr>
                            <td class='px-0 font-weight-bold'>
                              Total Neto
                            </td>
                            <td></td>
                            <td class='px-0 text-right'>
                              
                              $".number_format($total_neto, 2)."
                              
                            </td>
                          </tr>
                          <tr>
                            <td class='px-0 font-weight-bold'>
                            ($porcent_imp%) $idimpuesto
                            </td>
                            <td></td>
                            <td class='px-0 text-right'>
                             
                                $".number_format($total_imp, 2)."
                             
                            </td>
                          </tr> 
                          
                          <tr>
                            <td class='px-0 font-weight-bold'>
                              Total
                            </td>
                            <td></td>
                            <td class='px-0 text-right'>
                              
                              $".number_format($total, 2)."
                              
                            </td>
                          </tr> 
                        </tbody>
                      </table>
                    </div>
                  </div>
                  </div><br /><br /><br />";
                  $html.="
                  <div class='row mt-5'>
                  <div class='col-md-12'>
                  <b>TIPO DE FACTURA:</b> ". $tipo_factura ."
                  <table width='50%'>
                    <thead><tr><th>FORMA DE PAGO</th><th>MONTO</th></tr></thead>
                    <tbody>";
                    

                    $sql_forma_pago =  "SELECT
                      T.MONTOPAG 
                      , T.FUNCION 
                      , B.NOMBRE  
                      FROM TRANSACCPAGOS AS T JOIN BASEINSTRUMENTOS B ON B.CODTAR = T.CODTAR  
                      WHERE T.CONTROL = '{$idcontrol}'";
                    $result = $base_de_datos->query($sql_forma_pago); //$pdo sería el objeto conexión
                    $formas_pago = $result->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($formas_pago as $forma_pago) {
                      $html.="<tr><td>".$forma_pago["NOMBRE"]."</td><td>".$forma_pago["MONTOPAG"]."</td></tr>";
                    }
                    $html.="
                    </tbody>
                    </table>
                  </div>
                  </div><br /><br /><br />";

                  
                  if($CONESPECIAL == "1" AND $_REQUEST["tiptran"] != "PEDxCLI"){

                    switch ($PORRETIMP) {
                      case 100:
                        $ValRetenc = (float)$MONTOIMP; 
                        $codRetencion = 1;
                        $decripRetencion = "Pago por servicio profesional al estado 100%";
                        break;
                      case 50:
                        $ValRetenc = (float)$MONTOIMP*0.5; 
                        $codRetencion = 4;
                        $decripRetencion = "Pago o acreditación por compra de bienes/servicios 50%";
                        break;
                      
                      default:
                        $ValRetenc = ((float)$MONTOIMP*($PORRETIMP/100)); 
                        $decripRetencion = "Otros (disminución de la retención)";
                        $codRetencion = 8;
                        break;
                    }
                     
                  
                
                    
                    $html .= "
                    <div class='row mb-5'>
                      <div class='col-12'>
                        <table width='70%'>
                          <tr>
                            <td class='font-weight-bold' colspan='2'>
                              RETENCIÓN
                            </td>
                            <td style='text-align:right;'>
                              <h6>
                                Valor
                              </h6>
                            </td>
                          </tr>
                          <tr>
                            <td class='' colspan='2'>  
                            ". $decripRetencion ."</td>
                            <td style='text-align:right;'>
                              <h6>
                                $".number_format($ValRetenc, 2)."
                              </h6>
                            </td>
                          </tr>
                          <tr>
                            <td class='font-weight-bold' colspan='3'> &nbsp;
                            </td>
                          </tr> 
                        </table>
                      </div>
                    </div>";
                    
                  
                  }
                  $html.= "<div class='row mt-5'>
                  <div class='col-12 col-md-12'>
                    <h6 class='text-uppercase card-description font-weight-bold mb-3'>
                      $subtitulo_doc por
                    </h6>";
                    $sql1="SELECT CODVEN, NOMBRE, NUMTEL, DIRCORREO FROM BASEVENDEDORES WHERE CODVEN='$cod_ven'";
                    //echo $sql1;
                    $result = $base_de_datos->query($sql1); //$pdo sería el objeto conexión
                    $total_reg = $result->fetchColumn();
                    $numtel_vendedor="";
                    $correo_vendedor="";
                    $nom_vendedor="";
                    if($total_reg!=''){
                      //$sql_button="SELECT CONTROL, NUMREF, DESCRIP1, FECEMISS, MONTOSUB, MONTOTOT, TIPTRAN, NOMBRE FROM TRANSACCMAESTRO WHERE CODIGO='$codigo'";
                      $sentencia_b = $base_de_datos->prepare($sql1, [
                                                  PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                                              ]);
                      $sentencia_b->execute();                  
                      while ($data_b = $sentencia_b->fetchObject()){
                        $nom_vendedor=$data_b->NOMBRE;
                        if($data_b->NUMTEL!=""){
                          $numtel_vendedor="<b><label>TEL.</label> ".utf8_encode($data_b->NUMTEL)."</b><br />";
                        }

                        if($data_b->DIRCORREO!=""){
                          $correo_vendedor="<b><label>EMAIL.</label> ".utf8_encode($data_b->DIRCORREO)."</b><br />";
                        }
                      }
                      
                    }
                    $html.= "
                    
                    <p class='m-4'>";
                    
                    
                    $html.="
                      
                      <b><label>EMPRESA</label> ".utf8_encode($nom_empresa)."</b> <br />
                      <b><label>R.U.C.</label> ".utf8_encode($ruc)."</b><br />";
                    if($dir_empresa!=''){  
                      $html.="<b><label>DIRECCION</label> ".utf8_encode($dir_empresa)."</b><br /> ";
                    }

                    $html.="<b><label>VENDEDOR</label> ".utf8_encode($nom_vendedor)."</b> <br />  
                    $numtel_vendedor $correo_vendedor
                      
                    </p>
                  </div><br /><br />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>";
      $html2="<html><head>
      <style>
        body{
          color: #2c2c2c;
          font-size: 14px;
          font-family: 'Montserrat', 'Helvetica Neue', Arial, sans-serif;
          -webkit-font-smoothing: antialiased;
        }

        .full-page>.content {
          padding-bottom: 150px;
          padding-top: 150px;
          width: 100%;
        }

        .full-page>.content, .full-page>.footer {
          position: relative;
          z-index: 4;
        }

        .section-image .container {
          z-index: 2;
          position: relative;
        }

        .ml-auto, .mx-auto {
          margin-left: auto!important;
        }

        .mr-auto, .mx-auto {
          margin-right: auto!important;
        }

        .col, .col-1, .col-10, .col-11, .col-12, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-auto, .col-lg, .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-auto, .col-md, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-auto, .col-sm, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-auto, .col-xl, .col-xl-1, .col-xl-10, .col-xl-11, .col-xl-12, .col-xl-2, .col-xl-3, .col-xl-4, .col-xl-5, .col-xl-6, .col-xl-7, .col-xl-8, .col-xl-9, .col-xl-auto {
          position: relative;
          width: 100%;
          padding-right: 15px;
          padding-left: 15px;
        }

        .card {
          border: 0;
          border-radius: 0.1875rem;
          display: inline-block;
          position: relative;
          width: 100%;
          margin-bottom: 20px;
          box-shadow: 0 1px 15px 1px rgb(39 39 39 / 10%);
        }

        .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
          padding: 12px 7px;
          vertical-align: middle;
          position: absolute;
        }

        .table>thead>tr>th {
          border-bottom-width: 1px;
          font-size: 1.45em;
          font-weight: 300;
          border: 0;
          
      }

      .table td, .table th {
        padding: .75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
      }

      .font-weight-bold {
        font-weight: 700!important;
      }

      .text-capitalize {
        text-transform: capitalize!important;
      }

      h6, .h6 {
          font-size: 1em;
          font-weight: 700;
          text-transform: uppercase;
          line-height: 0.2;
          margin-top:2px !important;
          margin-bottom:5px !important;
      }

      .text-right {
          text-align: right!important;
      }

      .pl-0, .px-0 {
          padding-left: 0!important;
      }

      .pr-0, .px-0 {
        padding-right: 0!important;
      }

      th {
        text-align: inherit;
        text-align: -webkit-match-parent;
      }

      .text-center {
        text-align: center!important;
      }

      .row {

        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        
        margin-right: -15px;
        margin-left: -15px;
      }

      .card .card-header .card-title {
        margin-top: 10px;
      }

      .card-title {
        margin-bottom: .75rem;
      }

      h4, .h4 {
        font-size: 1.714em;
        /*line-height: 1.45em;*/
        margin-top: 2px !important;
        margin-bottom: 0px !important;
      }

      h5, .h5 {
        font-size: 1.57em;
        line-height: 1.4em;
        margin-bottom: 2px !important;
        margin-top:2px !important;
      }

      .font-weight-light {
        font-weight: 300!important;
        font-family: 'Montserrat', 'Helvetica Neue', Arial, sans-serif !important;
      }

      hr {
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 0;
        border-top: 1px solid rgba(0,0,0,.1);
      }

      .description, .card-description, .footer-big p, .card .footer .stats {
        color: #9A9A9A;
        font-weight: 300;
      }

      .font-weight-bold {
        font-weight: 700!important;
      }

      .mt-3, .my-3 {
        /*margin-top: 1rem!important;*/
      }
      </style>
      </head><body>".$html."</body></html>";

      if($tiptran=='FAC'){
        $file = fopen("data_factura.txt", "w+");
        fwrite($file, "$html2");
        fclose($file);
        //$file = fopen("data_factura.txt", "w+");
      }else{
        $file = fopen("data.txt", "w+");
        fwrite($file, "$html2");
        fclose($file);

      }
      
      ?>
      
    </div>
    <!-- loading 
    <div id="loading" style="z-index: 10000; position: fixed; top:0; left:0; background-color: rgba(0,0,0,.7); width: 100vw; height: 100vh;">
      <div style="display: inline-block; position: absolute; top: 50%; left: 50%; margin: -50px 0 0 -50px; transform: translateXY(-50%,-50%);">
        <span class="fas fa-spin fa-spinner fa-5x" style="color:#ff5001"></span>
      </div>
    </div>-->
    <?php include("recursos/loading.php");?>
  </div>
  <!--   Core JS Files   -->
  <script src="assets/js/core/jquery.min.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <!--<script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>-->
  <script src="assets/js/plugins/moment.min.js"></script>
  <!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
  <script src="assets/js/plugins/bootstrap-switch.js"></script>
  <!--  Plugin for Sweet Alert -->
  <script src="assets/js/plugins/sweetalert2.min.js"></script>
  <!-- Forms Validations Plugin -->
  <script src="assets/js/plugins/jquery.validate.min.js"></script>
  <!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
  <script src="assets/js/plugins/jquery.bootstrap-wizard.js"></script>
  <!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
  <script src="assets/js/plugins/bootstrap-selectpicker.js"></script>
  <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
  <script src="assets/js/plugins/bootstrap-datetimepicker.js"></script>
  <!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
  <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
  <!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
  <script src="assets/js/plugins/bootstrap-tagsinput.js"></script>
  <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
  <script src="assets/js/plugins/jasny-bootstrap.min.js"></script>
  <!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
  <script src="assets/js/plugins/fullcalendar.min.js"></script>
  <!-- Vector Map plugin, full documentation here: http://jvectormap.com/documentation/ -->
  <script src="assets/js/plugins/jquery-jvectormap.js"></script>
  <!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
  <script src="assets/js/plugins/nouislider.min.js"></script>
  <!--  Google Maps Plugin    
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>-->
  <!-- Chart JS -->
  <script src="assets/js/plugins/chartjs.min.js"></script>
  <!--  Notifications Plugin    -->
  <script src="assets/js/plugins/bootstrap-notify.js"></script>
  <!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="assets/js/now-ui-dashboard.min.js?v=1.6.0" type="text/javascript"></script><!-- Now Ui Dashboard DEMO methods, don't include it in your project! -->
  <script src="assets/demo/demo.js"></script>
  <script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#loading").hide();
		});
  </script>
  <script>
    $(document).ready(function() {
      demo.checkFullPageBackgroundImage();
    });
    $(document).on("click","#btnImprimirDocumento" , function(event)
    {
    event.preventDefault();
      var control    = $("#nrocontrol").val();
      var tiptran    = $("#tiptran").val();
      var idfac      = $("#idfac").val();
      var datos = new FormData();
      datos.append("accion", "mostrarDocumento");
      datos.append("caso", "Envio");
      datos.append("control", control);
      datos.append("tiptran", tiptran);
      fetch('ajax/mostrarDocumento.php', {
        method: 'POST',
        body: datos
      })
      .then(function(response) {
        return response.json();
      })
      .then(datos => {
        if(!datos){
          alert("No se puedo generar la factura");

          return false;
        }
        var filename = '';
        switch (tiptran) {
          case 'FAC':
            var filename = 'FAC';
            break;
          case 'PRE':
            var filename = 'PRE';
            break;
          case 'PEDxCLI':
            var filename = 'PED';
            break;
        
          default:
            break;
        }
        let base64PDF = datos.PDF;
        let link = document.createElement('a');
        link.download = filename+'_'+idfac+'.pdf';
        link.href = `data:application/pdf;base64,${base64PDF}`;

        // Se dispara el evento de click para empezar la descarga.
        link.click();

      })
      .catch((error) => {
        alert("Error al generar la factura");
        /*swal({
          title: "Error al generar la factura",
          text: "error.message",
          icon: "error",
            className: "text-center"
        });*/
        console.error('Error:', error);
      });

  });

  </script>
</body>

</html>