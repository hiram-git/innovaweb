<?php
session_start();
include_once "config/db.php";

function parseVersion($versionString) {
    $pattern = '/^(?:Ver\.|Versi[óo]n|Ver\. CR)\s+(\d+(?:\.\d+)+)(?:[ .]([A-Z])?)?$/i';
    
    if (preg_match($pattern, trim($versionString), $matches)) {
        $parts = explode('.', $matches[1]);
        
        // Si hay 4 partes → tomamos la tercera (1.97.27.1 → 27)
        // Si hay 3 partes  → tomamos la tercera (1.97.27 → 27)
        // Si hay 2 partes  → tomamos la segunda
        if (count($parts) >= 3) {
            return $parts[count($parts) - 2];  // anteúltimo número
        }
        
        return end($parts); // caso fallback (versiones cortas)
    }
    
    return null;
}
$namelog=strtoupper($_GET['namelog']);
$passlog=$_GET['passlog'];


$sqlCheckColumn = "
    SELECT 1
    FROM sys.columns
    WHERE object_id = OBJECT_ID('BASEUSUARIOS')
    AND name = 'CLAVEWEB'
";
$stmt = $base_de_datos->query($sqlCheckColumn);
$columnExists = $stmt->fetch();

if (!$columnExists) {
    // Si el campo no existe, ejecutar ALTER TABLE para crearlo
    $sqlAlterTable = "
        ALTER TABLE BASEUSUARIOS
        ADD CLAVEWEB VARCHAR(255) COLLATE Latin1_General_CI_AS
    ";
    $base_de_datos->exec($sqlAlterTable);
}

/*recuperando todo los productos comandados*/
$sql3="SELECT CODUSER, CLAVE,CLAVEWEB, VALVENDEDOR, VALDEPOSITO, ACTPRECIO, VALPRECIO, CREACLIENTE, ACTCLIENTE, VALCLIENTE,
ACTVENDEDOR, VALDIASVENC,ACTDEPOSITO, VALDEPOSITO, ACTDESCTOPAR, ACTDESCTOGLOBAL, CAMBIARPRECIO, VENTAMENOS, ACTFACEXI, CLIRAPMTOCREDI
    FROM BASEUSUARIOS WHERE UPPER(CODUSER)='$namelog'";
$sentencia4 = $base_de_datos->prepare($sql3, [
              PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
]);
$sql_emp = "SELECT CTAVENIMP FROM BASEEMPRESA;";
$sentencia_emp = $base_de_datos->prepare($sql_emp );
$sentencia_emp->execute();
$datos_empresa = $sentencia_emp->fetch( PDO::FETCH_ASSOC );
$version = parseVersion($datos_empresa['CTAVENIMP']);
$sentencia4->execute();
while ($data2 = $sentencia4->fetchObject()){
    
    $_SESSION['clave_nueva'] = false;
    if($version >= 24){
        $campo_clave = $data2->CLAVEWEB;
        if(trim($campo_clave)=="" || is_null($campo_clave)){
            $_SESSION['clave_nueva'] = true;
            echo "2";exit;
        }
    }else{
        $campo_clave = $data2->CLAVE;
    }
    if($data2->CODUSER==$namelog){
        if($campo_clave==$passlog){
            $fp = fopen("config/data.txt", "r");
            while (!feof($fp)){
                $linea = fgets($fp);
                $explinea=explode('|',$linea);
            }
            fclose($fp);
            $_SESSION['zonah']=$explinea[0];

            $_SESSION['sesion_iniciada'] = true;
            $_SESSION['coduser']         = $data2->CODUSER;
            $_SESSION['ventamenos']      = $data2->VENTAMENOS;
            $_SESSION['actfacexi']       = $data2->ACTFACEXI;
            $_SESSION['actdeposito']     = $data2->ACTDEPOSITO;
            $_SESSION['valdiasvenc']     = $data2->VALDIASVENC;

            if(trim($data2->VALVENDEDOR)!=''){
                $_SESSION['codvendedor'] = $data2->VALVENDEDOR;
            }
            
            if(trim($data2->VALDEPOSITO)!=''){
                $_SESSION['codalmacen'] = $data2->VALDEPOSITO;
            }

            /*datos escenciales para la parte de permisos de uso del sistema*/
            $_SESSION['creacliente'] = $data2->CREACLIENTE;
            $_SESSION['actcliente']  = $data2->ACTCLIENTE;
            $_SESSION['valcliente']  = $data2->VALCLIENTE;
            $_SESSION['desctopar']   = $data2->ACTDESCTOPAR;
            $_SESSION['desctoglo']   = $data2->ACTDESCTOGLOBAL;
            $_SESSION['mtodesctoglo']   = $data2->CLIRAPMTOCREDI;
            $_SESSION['titulo_web']  = "INNOVA SOFT";

            $sql32="SELECT VEN_PRESUPUESTO, VEN_PEDIDOS, VEN_VENTAS, ADM_CXC FROM BASEUSUARIOSEXT WHERE UPPER(CODUSER)='$namelog'";
            $sentencia42 = $base_de_datos->prepare($sql32, [
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
                ]);

            $sentencia42->execute();
            while ($data22 = $sentencia42->fetchObject()){                
                $_SESSION['ver_pedido']      = $data22->VEN_PEDIDOS;
                $_SESSION['ver_presupuesto'] = $data22->VEN_PRESUPUESTO;
                $_SESSION['ver_factura']     = $data22->VEN_VENTAS;
                $_SESSION['ver_cobro']       = $data22->ADM_CXC;
                $_SESSION['ver_ot']          = 0;
            }


            if(($data2->ACTPRECIO==0) AND ($data2->VALPRECIO==0)){ 
                //si ambos campos son cero indica que el precio es libre
                $_SESSION['usuario_precio']='libre';
            }else if(($data2->ACTPRECIO==0) AND ($data2->VALPRECIO>0)){ 
                /*si es cero actprecio y tiene un valor valprecio entonces valprecio es el precio a tomar*/
                $_SESSION['usuario_precio']=$data2->VALPRECIO;
            }else if($data2->ACTPRECIO==1){ 
                /*si actprecio es mayor a cero entonces hay que buscar el precio desde la tabla base clientes 
                proveedores, segun el cliente o proveedor que se le este haciendo la cotizacion o pedido*/
                $_SESSION['usuario_precio']='no_definido';
            }
            echo "1";
        }else{
            echo "0";
        }
    }else{
        echo "0";
    }
}
?>