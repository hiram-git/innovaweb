<?php
include 'fcript.php';

// Prueba la función
$valor = "12345";
$esperado_hex = "711ae1237508f237"; // qá#uò7 en hexadecimal

echo "Probando con entrada: $valor\n";
echo "Salida 12345: $esperado_hex\n\n";

    $resultado = fcrypt($valor);
    $resultado_hex = bin2hex($resultado);

    echo "Salida  test: $resultado_hex\n";
    echo "Longitud de salida: " . strlen($resultado) . " bytes\n";

    if ($resultado_hex === $esperado_hex) {
        echo "Salida (raw): $resultado\n";
    } else {
        echo "No coincide con la esperada.\n";
    }
    echo "----------------------------------------\n";

?>