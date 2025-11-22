<?php
// Cargar variables de entorno
require_once __DIR__ . '/env.php';

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de Base de Datos
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'inventario_supermercado'));

// API Keys (ahora seguros)
define('GEMINI_API_KEY', env('GEMINI_API_KEY', ''));

// Configuración de aplicación
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', false));

// Configuración de seguridad
define('RATE_LIMIT_REQUESTS', (int)env('RATE_LIMIT_REQUESTS', 5));
define('RATE_LIMIT_WINDOW', (int)env('RATE_LIMIT_WINDOW', 60));

// Validar configuración crítica
if (empty(GEMINI_API_KEY)) {
    error_log("ADVERTENCIA: GEMINI_API_KEY no está configurada. El chatbot no funcionará.");
}
?>