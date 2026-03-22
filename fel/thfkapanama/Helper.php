<?php
class Helper{

    public static function validarTelefono($cadena) {
        // Expresión regular para el formato requerido
        $patron = "/^\d{2}-\d{4}$|^\d{4}-\d{4}$/";

        // Verificar si la cadena coincide con el patrón
        if (preg_match($patron, $cadena)) {
            return true; // El formato es válido
        } else {
            return false; // El formato no es válido
        }
    }
    public static function validarCorreo($correo) {
        // Verificar si el correo tiene el formato correcto
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return true; // El formato es válido
        } else {
            return false; // El formato no es válido
        }
    }
}