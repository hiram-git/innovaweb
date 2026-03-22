<?php
include_once "permiso.php";
include_once "config/db.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<LINK href="css/estilo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/jpg" href="imgs/logo.ico"/>
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" href="bootstrap2/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="jquery/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="jquery/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="bootstrap2/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<link rel="stylesheet" href="font-awesome-4.7.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<script src="jquery/sweetalert.min.js"></script>
<title>
    <?php echo $_SESSION['titulo_web'];?>
  </title>
<style>

body {
  border: 0px solid black;
  padding: 0px;
  background: url('imgs/fondo2.png') no-repeat fixed center;
  background-repeat: no-repeat;
  /*background-size: 100%;*/
  background-size: cover;
  background-color:#BCBDC0;
}

.swal-modal {
width: 80% !important;
}
</style>
</head>
<body>
    <header>
    <!-- <div class="logo"><img src="imgs/logotipo.png" width="100%"></div> -->
    <!-- <div class="container d-flex justify-content-center">
      <div class="card mt-5" style='border: 0px solid rgba(0,0,0,.125) !important;'>
          <center><img src="imgs/logotipo.jpg" width='20%'><br />
            <strong>Sistema facturación, administración y contabilidad para Pymes</strong>
          </center>
      </div>
    </div> -->
  </header>
  <main>
<?php
$flag="";
foreach($_POST as $key => $valor){
    if($key=='transaccion'){
        $flag=$valor; // guardar o actualizar
    }
    /*
    else if($key=='codigo'){
        $cod_insert=$valor;
    }else if($key=='codigo_true'){
        $cod_insert_true=$valor;
    }*/
}
/*
$myarr=array("CODIGO", "RIF", "NOMBRE", "NIT", "TIPOCOMERCIO", "DIRECC1", "TIPOCLI", 
    "PORMAXDESGLO", "NOMBREGERENTE", "NUMTELCONTACTO", "NUMTEL", "DIRCORREO", 
    "CODVEN", "NOMBREEGEO1", "NOMBREEGEO2", "NOMBREEGEO3");

$myarr_2=array("cad", "cad", "cad", "cad", "cad", "cad", "cad", "dec", "cad", "cad", 
    "cad", "cad", "cad", "cad", "cad", "cad");

$cad="";
$cad2="";
$p=0;*/
if($flag=='actualizar'){
    $cod=$_POST['codigo'];
    $ruc=$_POST['ruc'];
    $nombre=$_POST['nombre'];
    $dv=$_POST['dv'];
    $direccion=$_POST['direccion'];
    $tipocli=$_POST['tipocli'];
    //$tipopre=$_POST['tipopre'];
    $descglobal=$_POST['descglobal'];
    $contact=strtoupper($_POST['contact']);
    $celular=$_POST['celular'];
    $tel=$_POST['tel'];
    $email=$_POST['email'];
    //$vendedor=$_POST['vendedor'];
    if(isset($_POST['vendedor'])){
        $vendedor = $_POST['vendedor'];
    }else{
        $vendedor ="";
    }
    /*$provincia=$_POST['provincia'];
    $distrito=$_POST['distrito'];
    $corregimiento=$_POST['corregimiento'];*/
    $tipocom=$_POST['tipocom'];

    if($tipocom==0){
        $nombre_empresa = strtoupper(trim($_POST['nombre_natural'])." ".trim($_POST['nombre2']).", ".trim($_POST['apellido'])." ".trim($_POST['apellido2']));
    }else{
        $nombre_empresa = strtoupper(trim($_POST['nombre']));
    }


    if(isset($_POST['provincia'])){
        $provincia = $_POST['provincia'];
    }else{
        $provincia =""; 
    }
    
    if(isset($_POST['distrito'])){
        $distrito = $_POST['distrito'];
    }else{
        $distrito ="";
    }

    if(isset($_POST['corregimiento'])){
        $corregimiento = $_POST['corregimiento'];
    }else{
        $corregimiento ="";
    }

    /*//if ($_POST) {
    foreach($_POST as $key => $valor){
        //echo "$key => $valor<br />";
        if($key!='transaccion'){
            if($myarr_2[$p]=='cad'){
                $cad.=$myarr[$p]."='$valor', ";
            }else{
                $cad.=$myarr[$p]."=$valor, ";
            }

            if($key=='codigo'){
                $cod=$valor;
            }
            $p++;
        }
    }
    //}
    $cadena = substr(trim($cad), 0, -1);*/
    //$cad_sql="UPDATE BASECLIENTESPROVEEDORES SET $cadena WHERE CODIGO='$cod'";
    //$_SESSION['codvendedor_opt']=$vendedor;
    $cad_sql="UPDATE BASECLIENTESPROVEEDORES SET CODIGO='$cod', RIF='$ruc', NOMBRE='$nombre_empresa', NIT='$dv', TIPOCOMERCIO='$tipocom', DIRECC1='$direccion', TIPOCLI='$tipocli', PORMAXDESGLO=$descglobal, NOMBREGERENTE='$contact', NUMTELCONTACTO='$celular', NUMTEL='$tel', DIRCORREO='$email', CODVEN='$vendedor', NOMBREEGEO1='$provincia', NOMBREEGEO2='$distrito', NOMBREEGEO3='$corregimiento' WHERE CODIGO='$cod'";
    //echo $cad_sql;
    $sentencia4 = $base_de_datos->prepare($cad_sql);

    $sentencia4->execute();
    //$msg="Datos actualizado correctamente!";
    echo "<script>
    /*const imageURL = 'imgs/icono_gancho.png';*/
    swal('Actualizado!', 'Datos actualizado correctamente!', 'success', {
      buttons: false,
      icon: 'success',
    });
    </script>";

    echo "
            <script>
                setTimeout(function(){ history.go(-2); }, 2000);
            </script>
            ";
}else if($flag=='guardar'){
    /*validamos sin*/
    /*foreach($_POST as $key => $valor){
        echo "$key = $valor<br />";
    }*/

    $codigo = $_POST['codigo'];
    $codigo_true = $_POST['codigo_true'];
    $ruc = $_POST['ruc'];
    if(isset($_POST['vendedor'])){
        $vendedor = $_POST['vendedor'];
    }else{
        $vendedor ="";
    }
    
    $dv = $_POST['dv'];
    $tipocom = $_POST['tipocom'];
    
    if($tipocom==0){
        $nombre_empresa = strtoupper($_POST['nombre_natural']." ".$_POST['nombre2'].", ".$_POST['apellido']." ".$_POST['apellido2']);
    }else{
        $nombre_empresa = strtoupper($_POST['nombre']);
    }

    if (!empty($_POST['percredito'])){
        $percredito=$_POST['percredito'];
    } else{
        $percredito=0;
    }

    if (!empty($_POST['conespecial'])){
        $conespecial=$_POST['conespecial'];
    } else{
        $conespecial=0;
    }

    if(!empty($_POST['porretimp'])){
        $porretimp=$_POST['porretimp'];
    }else{
        $porretimp=0.00;
    }   
    
    if(!empty($_POST['limitecre'])){
        $limitecre=$_POST['limitecre'];
    }else{
        $limitecre=0.00;
    }

    if(!empty($_POST['diascre'])){
        $diascre=$_POST['diascre'];
    }else{
        $diascre=0;
    }

    if(!empty($_POST['descparcial'])){
        $descparcial=$_POST['descparcial'];
    }else{
        $descparcial=0.00;
    }

    if(!empty($_POST['descglobal'])){
        $descglobal=$_POST['descglobal'];
    }else{
        $descglobal=0.00;
    }

    if(!empty($_POST['dias_bloqueo'])){
        $dias_bloqueo=$_POST['dias_bloqueo'];
    }else{
        $dias_bloqueo=0;
    }
    //echo "$percredito";
    $aplicadstofac = $_POST['aplicadstofac'];
    $direccion = $_POST['direccion'];
    $tipocli = $_POST['tipocli'];
    $tipopre=$_POST['tipopre'];
    $contact = strtoupper($_POST['contact']);
    $celular = $_POST['celular'];
    $tel = $_POST['tel'];
    $email = $_POST['email'];

    if(isset($_POST['provincia'])){
        $provincia = $_POST['provincia'];
    }else{
        $provincia =""; 
    }
    
    if(isset($_POST['distrito'])){
        $distrito = $_POST['distrito'];
    }else{
        $distrito ="";
    }

    if(isset($_POST['corregimiento'])){
        $corregimiento = $_POST['corregimiento'];
    }else{
        $corregimiento ="";
    }
    //$transaccion = guardar

    $sql3="SELECT codigo FROM BASECLIENTESPROVEEDORES WHERE codigo='$codigo'";
    $result = $base_de_datos->query($sql3); //$pdo sería el objeto conexión
    $total_reg = $result->fetchColumn();
    if($total_reg!=''){
        /*$sql3="SELECT MAX(CONTADORCLI) as maximo FROM BASEEMPRESA";
                $sentencia4 = $base_de_datos->prepare($sql3, [
                            PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

                $sentencia4->execute();
                while ($data2 = $sentencia4->fetchObject()){
                    $concli=$data2->maximo+1;
                }*/
        echo "<script>
                swal('Error!', 'Código de cliente existente!', 'error', {
                buttons: false,
                });
                </script>";

        echo "
                <script>
                    setTimeout(function(){ history.go(-1); }, 2000);
                </script>
                ";
    }else{
        //$concli=$codigo;
        $cad_sql="INSERT INTO BASECLIENTESPROVEEDORES (TIPREG, CODIGO, RIF, NOMBRE, NIT, PRECIO, FECHA1, FECHA2, MONTODEB, MONTOCRE, LIMITECRE, TIPOCOMERCIO, APLICADSTOFAC, DIRECC1, TIPOCLI, DIASCRE, PORRETIMP, CONESPECIAL, PORMAXDESPAR, PORMAXDESGLO, NOMBREGERENTE, NUMTELCONTACTO, NUMTEL, DIRCORREO, PERCREDITO, CODVEN, DIASBLOQUEO, NOMBREEGEO1, NOMBREEGEO2, NOMBREEGEO3)
        VALUES 
        ('1', '$codigo', '$ruc', '$nombre_empresa', '$dv', $tipopre, 0, 0, 0.00, 0.00, $limitecre, $tipocom, '$aplicadstofac', '$direccion', '$tipocli', $diascre, $porretimp, '$conespecial', $descparcial, $descglobal, '$contact', '$celular', '$tel', '$email', '$percredito', '$vendedor', $dias_bloqueo, '$provincia', '$distrito', '$corregimiento')";
        //$cad_sql="INSERT INTO BASECLIENTESPROVEEDORES $cad VALUES $cad2";
        //echo $cad_sql;
        $sentencia4 = $base_de_datos->prepare($cad_sql);

        $sentencia4->execute();

        if($codigo==$codigo_true){
            $cad_sql="UPDATE BASEEMPRESA SET CONTADORCLI+=1";
            //echo $cad_sql;
            $sentencia4 = $base_de_datos->prepare($cad_sql);
            $sentencia4->execute();
        }

        //$msg="Datos insertados correctamente!";
        echo "<script>
        /*const imageURL = 'imgs/icono_gancho.png';*/
        swal('Registrado!', 'Datos insertados correctamente!', 'success', {
        buttons: false,
        icon: 'success',
        });
        </script>";

        echo "
            <script>
                setTimeout(function(){ history.go(-2); }, 2000);
            </script>
            ";
    }

    
}else{
    //$msg="Datos vacios!"; 
    echo "<script>
    const imageURL = 'imgs/icono_gancho.png';
    swal('Datos vacios', 'Error al procesar datos!', 'error', {
      buttons: false,
    });
    </script>";

    echo "
    <script>
        setTimeout(function(){ history.go(-1); }, 2000);
    </script>
    ";
}

//echo "<center><br /><br /><h3>$msg</h3></center>";


?>
    </main>
    </body>
</html>