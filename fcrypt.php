<?php
class FCrypt 
{
    function fcrypt($valor, $indice = 1) {
        // Definición de variables
        $base = '';
        $partes = array_fill(0, 64, 0);
        $resultado = '';
        
        // Generar la máscara base (igual que antes)
        if ($indice > 64) {
            $indice = 32;
        }
        
        $x = 73353377;
        for ($i = 0; $i < 64; $i++) {
            $partes[$i] = $x * (65 - ($i + 1));
            $base .= pack('V', $partes[$i]);
        }
        
        $valor = trim($valor);
        $lon = strlen($valor);
        
        if ($lon > 0) {
            if ($indice === 0) {
                $indice = 1;
            }
            
            // Ajuste importante: Usar el índice como desplazamiento rotativo
            $current_pos = $indice - 1; // Convertir a base 0
            
            $in = ceil($lon / 4);
            
            for ($j = 0; $j < $in; $j++) {
                // Obtener 4 bytes del valor (rellenar con nulls si es necesario)
                $sunit = substr($valor, $j * 4, 4);
                $wunit = unpack('V', str_pad($sunit, 4, "\0"))[1];
                
                // Obtener 4 bytes de la base (rotando circularmente)
                $base_pos = ($current_pos + $j) % 64;
                $sunit_base = substr($base, $base_pos * 4, 4);
                $wunit_base = unpack('V', $sunit_base)[1];
                
                // Operación XOR
                $res = $wunit ^ $wunit_base;
                
                // Agregar al resultado
                $resultado .= pack('V', $res);
            }
        } else {
            $resultado = $valor;
        }
        
        return $resultado;
    }
    function coincidenias($resultado_hex, $esperado_hex) {
        $coincidencias = 0;
        $longitud = strlen($esperado_hex);
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

        return ($coincidencias / $longitud) * 100;
    }

}
?>