<?php

session_start();
include_once "config/db.php";
try {
    if (isset($_GET['namelog']) && isset($_GET['passlog'])) {
        $username = trim($_GET['namelog']);
        $new_password = trim($_GET['passlog']);

        if (!empty($username) && !empty($new_password)) {
            // Asumiendo que pin.php contiene la conexión PDO como $conn
            $stmt = $base_de_datos->prepare("UPDATE BASEUSUARIOS SET CLAVEWEB = :password WHERE CODUSER = :username");
            $stmt->execute(['password' => $new_password, 'username' => $username]);

            if ($stmt->rowCount() > 0) {
                echo "1"; // Éxito
            } else {
                echo "0"; // No se actualizó ningún registro
            }
        } else {
            echo "0"; // Parámetros vacíos
        }
    } else {
        echo "0"; // Solicitud inválida
    }
} catch (PDOException $e) {
    echo "0"; // Error en la base de datos
}
?>