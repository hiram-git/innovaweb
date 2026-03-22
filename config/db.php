
    <?php
    $clave = "H1r4m";
    $usuario = "sirrohan01";
    $nombreBaseDeDatos = "PRUEBAS_AYLEEN";

    $rutaServidor = "DESKTOP-46U0RK7\SQLEXPRESS";
    try {
        $base_de_datos = new PDO("sqlsrv:server=$rutaServidor;database=$nombreBaseDeDatos", $usuario, $clave);
        $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo "Ocurri&oacute; un error con la conexi&oacute;n a Base de Datos: " . $e->getMessage();
    }
    ?>