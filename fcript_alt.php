<?php
function fcrypt($valor, $indice = 1) {
    // Máscara esperada (temporal para pruebas)
    $mask = "\x40\x28\xD2\x17\x40\x08\xF2\x37";

    $resultado = '';
    $lon = strlen(trim($valor));
    if ($lon) {
        if ($indice > 64) {
            $indice = 32;
        }
        if ($indice == 0) {
            $indice = 1;
        }
        // Rellenar entrada a 8 bytes
        $valor_padded = str_pad($valor, 8, "\0");
        // Procesar byte por byte
        for ($j = 0; $j < 8; $j++) {
            $input_byte = ord($valor_padded[$j]);
            $mask_byte = ord($mask[$j]);
            $result_byte = $input_byte ^ $mask_byte;
            $resultado .= chr($result_byte);
        }
        $cresultado = $resultado;
    } else {
        $cresultado = trim($valor);
    }
    return $cresultado;
}

// Prueba y comparación
$passlog = "12345";
$encriptado = fcrypt($passlog, 1);
$resultado_hex = bin2hex($encriptado);
$esperado_hex = "711ae1237508f237";

// Comparar posición por posición
$coincidencias = 0;
$longitud = strlen($resultado_hex);
echo "Comparación posición por posición:\n";
for ($i = 0; $i < $longitud; $i++) {
    $caracter_resultado = $resultado_hex[$i];
    $caracter_esperado = $esperado_hex[$i];
    if ($caracter_resultado === $caracter_esperado) {
        echo "Posición " . ($i + 1) . ": '$caracter_resultado' == '$caracter_esperado' (Coincide)\n";
        $coincidencias++;
    } else {
        echo "Posición " . ($i + 1) . ": '$caracter_resultado' != '$caracter_esperado' (Diferente)\n";
    }
}

// Calcular porcentaje de coincidencia
$porcentaje = ($coincidencias / $longitud) * 100;

// Mostrar resultados
echo "\nResultado obtenido: $resultado_hex\n";
echo "Resultado esperado: $esperado_hex\n";
echo "Coincidencias: $coincidencias de $longitud posiciones\n";
echo "Porcentaje de coincidencia: $porcentaje%\n";
?>