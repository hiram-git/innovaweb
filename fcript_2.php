<?php
function fcrypt(string $valor, ?int $indice = 1): string {
    // Generar la máscara base
    $x = 73353377;
    $partes = [];
    for ($i = 1; $i <= 64; $i++) {
        $partes[$i] = $x * (65 - $i);
    }

    $resultado = '';
    $lon = strlen(trim($valor));

    if ($lon === 0) {
        return trim($valor);
    }

    // Ajustar el índice
    if ($indice === null || $indice === 0) {
        $indice = 1;
    } elseif ($indice > 64) {
        $indice = 32;
    }

    // Extraer la sección de la máscara
    $masc = '';
    for ($i = 0; $i < $lon; $i++) {
        $masc .= pack('N', $partes[($i % 64) + 1]); // Usar pack para convertir a bytes
    }

    // Dividir en bloques de 4 caracteres
    $in = (int)($lon / 4);
    if ($lon % 4) {
        $in++;
    }

    $vpartes = [];
    $res = [];
    for ($j = 1; $j <= $in; $j++) {
        // Extraer bloque de valor
        $sunit = substr($valor, ($j - 1) * 4, 4);
        // Convertir a entero (32 bits)
        $vpartes[$j] = unpack('N', str_pad($sunit, 4, "\0"))[1] ?? 0;

        // Extraer bloque de máscara
        $sunit = substr($masc, ($indice - 1) * 4, 4);
        $wunit = unpack('N', str_pad($sunit, 4, "\0"))[1] ?? 0;

        // Aplicar XOR
        $res[$j] = $vpartes[$j] ^ $wunit;
        $resultado .= pack('N', $res[$j]);
    }

    return trim($resultado);
}