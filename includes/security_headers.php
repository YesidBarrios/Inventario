<?php
/**
 * Headers de seguridad para protección básica
 * Implementa mejores prácticas de seguridad web
 */

function setSecurityHeaders() {
    // Prevenir clickjacking
    header("X-Frame-Options: DENY");

    // Prevenir MIME type sniffing
    header("X-Content-Type-Options: nosniff");

    // Habilitar HSTS (HTTP Strict Transport Security) - solo en producción
    if (APP_ENV === 'production' && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }

    // Content Security Policy 
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; ";
    $csp .= "img-src 'self' data: https:; ";
    $csp .= "font-src 'self' https://fonts.gstatic.com; ";
    $csp .= "connect-src 'self'; ";
    $csp .= "frame-ancestors 'none';";

    header("Content-Security-Policy: $csp");

    // Referrer Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");

    // Permissions Policy (anteriormente Feature Policy)
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

    // Remover headers innecesarios
    header_remove("X-Powered-By");
    header_remove("Server");
}

/**
 * Función para sanitizar input
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar que sea una solicitud POST válida
 */
function validatePostRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die(json_encode(['error' => 'Método no permitido']));
    }

    // Verificar Content-Type para APIs
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        // Validar JSON
        $input = file_get_contents('php://input');
        json_decode($input);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            die(json_encode(['error' => 'JSON inválido']));
        }
    }
}

/**
 * Logging de seguridad básico
 */
function logSecurityEvent($event, $details = []) {
    $logEntry = sprintf(
        "[%s] SECURITY: %s | IP: %s | User-Agent: %s | Details: %s\n",
        date('Y-m-d H:i:s'),
        $event,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        json_encode($details)
    );

    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Detectar intentos de ataque básico
 */
function detectBasicAttacks() {
    $suspiciousPatterns = [
        '/<script/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/union\s+select/i',
        '/drop\s+table/i',
        '/--/',
        '/#/i',
        '/\*/i'
    ];

    $checkData = [
        $_GET,
        $_POST,
        $_SERVER['QUERY_STRING'] ?? '',
        $_SERVER['REQUEST_URI'] ?? ''
    ];

    foreach ($checkData as $data) {
        if (is_array($data)) {
            $data = implode(' ', $data);
        }

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $data)) {
                logSecurityEvent('POTENTIAL_ATTACK_DETECTED', [
                    'pattern' => $pattern,
                    'data' => substr($data, 0, 100)
                ]);
                break;
            }
        }
    }
}

// Aplicar medidas de seguridad automáticamente
setSecurityHeaders();
detectBasicAttacks();
?>