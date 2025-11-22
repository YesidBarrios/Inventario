<?php
/**
 * Sistema de carga de variables de entorno
 * Compatible con archivos .env para desarrollo
 */

function loadEnv($path = null) {
    if ($path === null) {
        $path = __DIR__ . '/../.env';
    }

    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remover comillas si existen
            $value = trim($value, '"\'');

            // Solo establecer si no existe ya (prioridad: variables del sistema)
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

// Cargar variables de entorno al incluir este archivo
loadEnv();
?>