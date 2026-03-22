<?php
function fcrypt($valor, $indice = 1) {
    // Inicializar variables
    $partes = array_fill(0, 65, 0); // Arreglo PARTES
    $vpartes = array_fill(0, 65, 0); // Arreglo VPARTES
    $res = array_fill(0, 65, 0); // Arreglo RES
    $resultado = '';

    // Generar la máscara base
    if ($indice === null || $indice > 64) {
        $indice = 32;
    }
    if ($indice == 0) {
        $indice = 1;
    }
    $x = 73353377;
    for ($i = 1; $i <= 64; $i++) {
        $partes[$i] = $x * (65 - $i);
    }

    // Convertir PARTES a una cadena binaria para BASE
    $base = '';
    for ($i = 1; $i <= 64; $i++) { 
        $base .= pack('V', $partes[$i]); // Little-endian para coincidir con Clarion
    }

    // Procesar el valor de entrada
    $valor = trim($valor); // Equivalente a CLIP(VALOR)
    $lon = strlen($valor);
    if ($lon) {
        $in = (int)($lon / 4);
        if ($lon % 4) {
            $in++;
        }

        for ($j = 1; $j <= $in; $j++) {
            // Extraer bloque de 4 caracteres de VALOR
            $sunit = substr($valor, ($j - 1) * 4, 4);
            $sunit = str_pad($sunit, 4, "\0", STR_PAD_RIGHT);
            $vpartes[$j] = unpack('V', $sunit)[1]; // Little-endian

            // Extraer bloque de 4 caracteres de BASE
            $sunit = substr($base, ($indice - 1) * 4, 4);
            $sunit = str_pad($sunit, 4, "\0", STR_PAD_RIGHT);
            $wunit = unpack('V', $sunit)[1]; // Little-endian

            // Realizar operación XOR
            $res[$j] = $vpartes[$j] ^ $wunit;

            // Agregar resultado como cadena binaria
            $resultado .= pack('V', $res[$j]);
        }
        $cresultado = $resultado; // No recortar
    } else {
        $cresultado = $valor;
    }

    return $cresultado;
}
?>