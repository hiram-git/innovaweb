<?php
session_start();
include_once "config/db.php";

function filtrarCadena($cadena) {
    $cadenaFiltrada = '';
    $longitud = strlen($cadena);
    $separar = false;
    
    for ($i = 0; $i < $longitud; $i++) {
        $caracter = $cadena[$i];
        
        if ($separar) {
            $cadenaFiltrada .= $caracter;
        } elseif ($caracter === '%' || $caracter === '.' || is_numeric($caracter)) {
            $cadenaFiltrada .= $caracter;
            
            if ($caracter === '%' || $caracter === '.') {
                $separar = true;
            }
        }
    }
    
    return $cadenaFiltrada;
}

ob_clean();
$desc_global = ($_GET['descglobal'] ?? 0);
$maxdesglo = ($_GET['maxdesglo'] ?? 0);
$monto_descuento_global = ($_SESSION['mtodesctoglo'] ?? 0);

if ($desc_global < 0) {
    echo "2|El monto del descuento global debe ser mayor que cero.";
    exit;
}

if ($desc_global > $monto_descuento_global) {
    echo "2|El monto del descuento no puede superar el descuento global asignado al usuario.";
    exit;
}

if ($monto_descuento_global > $maxdesglo) {
    $maxdesglo = $monto_descuento_global;
}

// Separar la cadena a partir del %
$cadenaFiltrada = filtrarCadena($desc_global);
$partes = explode('%', $cadenaFiltrada);

$findme = "%";
$pos = strpos($desc_global, $findme);

$subtotal_sum = 0;
$itbm_sum = 0;
$total_sum = 0;
$total_exento = $total_impuesto = 0;

// Validación para evitar división por cero
$denominador = ($total_impuesto + $itbm_sum);
$pocentaje_descuento = 0; // Valor por defecto

if ($denominador != 0) {
    $pocentaje_descuento = ((float)$desc_global / $denominador * 100);
} else {
    // Si el denominador es 0, no se puede calcular porcentaje sobre impuestos
    // Podrías usar el subtotal como alternativa o manejar este caso especial
    if ($subtotal_sum != 0) {
        $pocentaje_descuento = ((float)$desc_global / $subtotal_sum * 100);
    }
}

if (!$pos) {
    // Solo validar si hay impuestos para comparar
    if ($denominador != 0 && (float)$pocentaje_descuento > (float)$monto_descuento_global) {
        echo "2|El descuento $pocentaje_descuento supera el máximo permitido del %$monto_descuento_global.";
        exit;
    }
}

$max = sizeof($_SESSION['aDatos']);

for ($i = 0; $i < $max; $i++) {
    $k = 0; 
    foreach ($_SESSION['aDatos'][$i] as $key => $val) {
        $k++;
        if ($k == 1) { // codigo del producto
            $cod_prod = $val;
        } else if ($k == 2) { // nombre del producto
            $nom_prod = $val;
        } else if ($k == 3) { // precio del producto
            $precio = $val;
        } else if ($k == 4) { // descuento del producto
            $descuento = $val;
        } else if ($k == 5) { // cantidad
            $cantidad = $val;
        } else if ($k == 6) { // itbm
            $itbm = $val;
        }
    } // fin foreach
              
    $subtotal_sum += ($cantidad * $precio) - $descuento;
    $itbm_sum += (float)((($cantidad * $precio) - $descuento) * ($itbm / 100));
    
    if ($itbm > 0) {
        $total_impuesto += (float)((($cantidad * $precio) - $descuento));
    } else {
        $total_exento += (float)(($cantidad * $precio));
    }
}

// Recalcular el porcentaje después del bucle cuando ya tenemos los totales
$denominador = ($total_impuesto + $itbm_sum);
if ($denominador != 0) {
    $pocentaje_descuento = ((float)$desc_global / $denominador * 100);
} else if ($subtotal_sum != 0) {
    $pocentaje_descuento = ((float)$desc_global / $subtotal_sum * 100);
}

if ($pos === false) {
    $desc_global_result = (float)$desc_global;
    
    if ($desc_global_result < $subtotal_sum) {
        if ($desc_global_result <= $maxdesglo) {
            // Validación adicional para evitar división por cero en porcentaje
            $pordes_global = 0;
            if ($subtotal_sum != 0) {
                $pordes_global = ($desc_global * 100) / $subtotal_sum;
            }
            
            $subtotal_result = $subtotal_sum - (float)$desc_global;
            $desc_global_porc = (float)($subtotal_result * $pordes_global / 100);
            $montodes_ms = $montodes_ = (float)($subtotal_result * $pordes_global / 100);
            $subtotal_result = (float)$subtotal_result;
            $itbm_porc = ($itbm_sum * $pordes_global / 100);
            $itbm_sum_result = round((float)$itbm_sum - (float)$itbm_porc, 4);
            $total_sum_result = round(($itbm_sum_result) + $subtotal_result, 4);
            
            $array = array(
                "subtotal_sum"        => $subtotal_sum,
                "desc_global_result"  => $desc_global_result,
                "itbm_porc"           => $itbm_porc,
                "desc_global"         => $desc_global,
                "subtotal_sum_result" => $subtotal_result,
                "itbm_sum_result"     => $itbm_sum_result,
                "total_sum_result"    => $total_sum_result,
                "POS"                 => ""
            );
            echo "1|" . json_encode($array);
        } else {
            echo "2|% Dscto no puede ser mayor al asignado al cliente o al usuario especial";
        }
    } else {
        echo "0|Dscto no puede ser mayor al monto de la transacción.";
    }
} else {
    if ($pos === 0) {
        $desc_global = $partes[1];
        $cadena_porcentaje = "%" . $desc_global;
    } else {
        $desc_global = $partes[0];
        $cadena_porcentaje = $desc_global . "%";
    }
    
    $desc_global_porc = 0;
    if ($subtotal_sum != 0) {
        $desc_global_porc = (float)($subtotal_sum * $desc_global / 100);
    }
    
    $desc_global_result = (float)$desc_global_porc;
    
    if ($desc_global_result < $subtotal_sum) {
        if ($desc_global_result <= $maxdesglo) {
            $subtotal_sum_result = $subtotal_sum - (float)($desc_global_porc);
            $itbm_porc = round($itbm_sum * $desc_global / 100, 2);
            $itbm_sum_result = round($itbm_sum - $itbm_porc, 2);
            $total_sum_result = round(($itbm_sum_result) + $subtotal_sum_result, 2);
            
            $array = array(
                "subtotal_sum"        => $subtotal_sum,
                "desc_global"         => $desc_global_porc,
                "itbm_porc"           => $itbm_porc,
                "desc_global_result"  => $cadena_porcentaje,
                "subtotal_sum_result" => $subtotal_sum_result,
                "itbm_sum_result"     => $itbm_sum_result,
                "total_sum_result"    => $total_sum_result,
                "POS"                 => "%"
            );
            echo "1|" . json_encode($array);
        } else {
            echo "2|% Descuento no puede ser mayor al asignado al cliente o al usuario especial";
        }
    } else {
        echo "0|Descuento no puede ser mayor al monto de la transacción..";
    }
}
?>