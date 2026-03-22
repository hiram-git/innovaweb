<?php
include_once "../config/db.php";

$accion = isset( $_POST["accion"] ) ? $_POST["accion"] : '' ;
try {
    // Iniciar transacción
    $base_de_datos->beginTransaction();
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        // Maneja el error, por ejemplo, redirige a una página de error
        die("Error: Invalid request method");
    }
    
    // Asegúrate de que los datos necesarios están presentes
    if (!isset($_POST['vendedor'], $_POST['responsable'], $_POST['num_ot'], $_POST['num_control'], $_POST['atendido'], $_POST['fecha_entrada'], $_POST['fecha_entrega'], $_POST['productos'])) {
        // Maneja el error, por ejemplo, envía una respuesta con un código de estado 400
        die("Error: Missing required data");
    }
    
    // Convierte los productos de JSON a un array de PHP
    $productos = json_decode($_POST['productos'], true);
    
    // Prepara la consulta para la tabla TRANSACCOT
    $fecha_entrada = date('Y-m-d H:i:s', strtotime($_POST['fecha_entrada']));
    $fecha_entrega = date('Y-m-d H:i:s', strtotime($_POST['fecha_entrega']));
    //echo "(",$_POST['num_ot'],",",$_POST['vendedor'],",", $_POST['vendedor'], $fecha_entrada, $fecha_entrega,",", $_POST['num_control'],",", 0,",", $_POST['responsable'],",", date('Y-m-d H:i:s'),",", $_POST['num_control'],");";exit;
    // Prepara la consulta para la tabla TRANSACCOT
    $num_ot = $_POST['num_ot'];
    $vendedor = explode("|",$_POST['vendedor']);
    $num_control = $_POST['num_control'];
    $responsable = $_POST['responsable'];
    $fecha_creacion = date('Y-m-d H:i:s');

    /*echo $sql = "
        INSERT INTO TRANSACCOT
        (CONTROLOT, CODCLIENTE, ATENDIDO, FECHAOT, FECHA_ENTREGA, CONTROLPRES, ESTADO, USUARIO, FECHA_CREACION, CONTROLMAESTRO)
        VALUES ('$num_ot', '$vendedor[0]', '$vendedor[0]', '$fecha_entrada', '$fecha_entrega', '$num_control', 0, '$responsable', '$fecha_creacion', '$num_control');
    ";exit;*/
    $sql = "
        INSERT INTO TRANSACCOT
        (CONTROLOT, CODCLIENTE, ATENDIDO, FECHAOT, FECHA_ENTREGA, CONTROLPRES, ESTADO, USUARIO, FECHA_CREACION, CONTROLMAESTRO)
        VALUES (:num_ot, :vendedor, :atendido, :fecha_entrada, :fecha_entrega, :num_control, :estado, :responsable, :fecha_creacion, :num_control_maestro)
    ";

    $stmt1 = $base_de_datos->prepare($sql);

    // Vincula los datos de prueba a los marcadores de posición
    $stmt1->bindParam(':num_ot', $num_ot, PDO::PARAM_STR);
    $stmt1->bindParam(':vendedor', $vendedor[0], PDO::PARAM_STR);
    $stmt1->bindParam(':atendido', $vendedor[0], PDO::PARAM_STR);
    $stmt1->bindParam(':fecha_entrada', $fecha_entrada, PDO::PARAM_STR);
    $stmt1->bindParam(':fecha_entrega', $fecha_entrega, PDO::PARAM_STR);
    $stmt1->bindParam(':num_control', $num_control, PDO::PARAM_STR);
    $stmt1->bindParam(':estado', $estado);
    $stmt1->bindParam(':responsable', $responsable, PDO::PARAM_STR);
    $stmt1->bindParam(':fecha_creacion', $fecha_creacion, PDO::PARAM_STR);
    $stmt1->bindParam(':num_control_maestro', $num_control, PDO::PARAM_STR);
    // Ejecuta la consulta
    $stmt1->execute();

    // Ejecuta la consulta con los datos recibidos
    //$stmt1->execute(["'".$_POST['num_ot']."'", "'".$_POST['vendedor']."'", "'".$_POST['vendedor']."'",$fecha_entrada, $fecha_entrega, "'".$_POST['num_control']."'", 0, "'".$_POST['responsable']."'", date('Y-m-d H:i:s'), "'".$_POST['num_control']."'"]);
    // Prepara la consulta para la tabla TRANSACCOTDETALLES
    // Prepara la consulta para la tabla TRANSACCOTDETALLES
    $stmt2 = $base_de_datos->prepare("
        INSERT INTO TRANSACCOTDETALLES
        (CONTROLOTDETALLES, CONTROLOT, CODPROD, CANTIDAD, DESCRIPCION, MATERIAL, NUM_CARAS, TIPO, ACABADOS, OBSERVACIONES)

        VALUES (:control_ot_det, :num_ot, :codprod, :cantidad, :descripcion, :material, :num_caras, :tipo, :acabados, :observaciones);
    ");
    // Ejecuta la consulta para cada producto
    foreach ($productos as $producto) {

        $res_cadena  = cadena_control();

        $exp_control = explode('|', $res_cadena);

        $dias                 = $exp_control[0];
        $hora_actual          = $exp_control[1];
        $aleatorio            = $exp_control[2];
        $fecha_actual_clarion = $exp_control[3];
        $fecha_actual_ymd     = $exp_control[4];
        $hora_actual_clarion  = $exp_control[1];
        $control_ot_det = "$dias$hora_actual$aleatorio" . "01";
        //$stmt2->execute([$control_ot_det, $_POST['num_ot'], $producto['codprod'], $producto['cantidad'], $producto['descripcion'], $producto['material'], $producto['num_caras'], $producto['tipo'], $producto['acabados'], $producto['observaciones']]);


        $stmt2->bindParam(':control_ot_det', $control_ot_det);
        $stmt2->bindParam(':num_ot', $num_ot, PDO::PARAM_STR);
        $stmt2->bindParam(':codprod', $producto['codprod'], PDO::PARAM_STR);
        $stmt2->bindParam(':cantidad', $producto['cantidad'], PDO::PARAM_STR);
        $stmt2->bindParam(':descripcion', $producto['descripcion'], PDO::PARAM_STR);
        $stmt2->bindParam(':material', $producto['material'], PDO::PARAM_STR);
        $stmt2->bindParam(':num_caras', $producto['caras'], PDO::PARAM_INT);
        $stmt2->bindParam(':tipo', $producto['color'], PDO::PARAM_STR);
        $stmt2->bindParam(':acabados', $producto['acabado'], PDO::PARAM_STR);
        $stmt2->bindParam(':observaciones', $producto['observaciones'], PDO::PARAM_STR);
        $stmt2->execute();
    }
    
    $base_de_datos->commit();
    // Envía una respuesta con un código de estado 200 para indicar que todo salió bien
    http_response_code(200);// Prepara el mensaje de respuesta en formato JSON
    $response = array('message' => 'Datos insertados correctamente.', "estado" => 1);
    
    // Convierte el array a formato JSON
    $jsonResponse = json_encode($response);
    
    // Imprime el mensaje de respuesta en formato JSON
    header('Content-Type: application/json');
    echo $jsonResponse;exit;
    // Confirmar transacción
} catch (Exception $e) {
    // Si hay un error, revertir la transacción
    
    $base_de_datos->rollBack();
    http_response_code(400);
    $response = array('message' => 'Errores en el proceso', "estado" => 0);
    
    // Convierte el array a formato JSON
    $jsonResponse = json_encode($response);
    
    // Imprime el mensaje de respuesta en formato JSON
    header('Content-Type: application/json');
    echo $jsonResponse;exit;
    throw $e;
}


function cadena_control()
{
    /*generando cantidad de dias transcurrido desde 1800 hasta la actualidad con codigo clarin*/
    $fecha1 = new DateTime("1800-12-28 00:00:00");
    $fecha2 = new DateTime(date("Y-m-d H:i:s"));

    $diff = $fecha1->diff($fecha2);
    $dias = $diff->days;
    $fecha_actual_clarion = $diff->days;
    $fecha_actual_ymd = date("Ymd");

    /*generando hora actual en codigo clarin*/
    /*$aux =  microtime(true);
    $now = DateTime::createFromFormat('U.u', $aux);        
    if (is_bool($now)){
        $now = DateTime::createFromFormat('U.u', $aux += 0.001);
    }*/
    //$now = DateTime::createFromFormat("U.u", microtime(true));
    //$hora_actual = ($now->format("H")*360000)+($now->format("i")*6000)+($now->format("s")*100)+($now->format("u")*10)+1;
    $hora_actual = (date('H') * 360000) + (date("i") * 6000) + (date("s") * 100) + (date("v") / 10) + 1;
    if (strlen($hora_actual) == 7) {
    } else if (strlen($hora_actual) > 7) {
        $hora_actual = substr($hora_actual, 0, 7);
    } else if (strlen($hora_actual) < 7) {
        $res = 7 - strlen($hora_actual);
        for ($t = 1; $t <= $res; $t++) {
            $hora_actual = "0" . $hora_actual;
        }
    }

    /*generando numero aleatorio entre 10000 y 99999*/
    $aleatorio = mt_rand(10000, 99999);

    return "$dias|$hora_actual|$aleatorio|$fecha_actual_clarion|$fecha_actual_ymd";
}



?>