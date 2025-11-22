<?php
/**
 * Sistema básico de Rate Limiting
 * Protege contra abuso de la API del chatbot
 */

class RateLimiter {
    private $maxRequests;
    private $windowSeconds;
    private $storagePath;

    public function __construct($maxRequests = 5, $windowSeconds = 60) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->storagePath = __DIR__ . '/../cache/rate_limit/';

        // Crear directorio si no existe
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Verifica si una IP puede hacer una solicitud
     */
    public function checkLimit($identifier = null) {
        if ($identifier === null) {
            $identifier = $this->getClientIP();
        }

        $filePath = $this->storagePath . md5($identifier) . '.json';

        $currentTime = time();
        $windowStart = $currentTime - $this->windowSeconds;

        // Leer datos existentes
        $data = $this->readData($filePath);

        // Limpiar solicitudes antiguas
        $data['requests'] = array_filter($data['requests'], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });

        // Verificar límite
        if (count($data['requests']) >= $this->maxRequests) {
            $data['blocked_until'] = $currentTime + $this->windowSeconds;
            $this->writeData($filePath, $data);
            return false;
        }

        // Agregar nueva solicitud
        $data['requests'][] = $currentTime;
        $this->writeData($filePath, $data);

        return true;
    }

    /**
     * Obtiene tiempo restante de bloqueo
     */
    public function getRemainingTime($identifier = null) {
        if ($identifier === null) {
            $identifier = $this->getClientIP();
        }

        $filePath = $this->storagePath . md5($identifier) . '.json';
        $data = $this->readData($filePath);

        if (isset($data['blocked_until']) && $data['blocked_until'] > time()) {
            return $data['blocked_until'] - time();
        }

        return 0;
    }

    /**
     * Obtiene la IP del cliente
     */
    private function getClientIP() {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Si hay múltiples IPs (X-Forwarded-For), tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validar que sea una IP válida
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1'; // fallback
    }

    /**
     * Lee datos del archivo
     */
    private function readData($filePath) {
        if (!file_exists($filePath)) {
            return ['requests' => [], 'blocked_until' => 0];
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if ($data === null) {
            return ['requests' => [], 'blocked_until' => 0];
        }

        return $data;
    }

    /**
     * Escribe datos al archivo
     */
    private function writeData($filePath, $data) {
        file_put_contents($filePath, json_encode($data));
    }

    /**
     * Limpia archivos antiguos (mantenimiento)
     */
    public function cleanup() {
        $files = glob($this->storagePath . '*.json');
        $cutoffTime = time() - ($this->windowSeconds * 2);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
}

// Función helper para uso rápido
function checkRateLimit() {
    static $limiter = null;

    if ($limiter === null) {
        $limiter = new RateLimiter(RATE_LIMIT_REQUESTS, RATE_LIMIT_WINDOW);
    }

    return $limiter->checkLimit();
}

function getRateLimitRemainingTime() {
    static $limiter = null;

    if ($limiter === null) {
        $limiter = new RateLimiter(RATE_LIMIT_REQUESTS, RATE_LIMIT_WINDOW);
    }

    return $limiter->getRateLimitRemainingTime();
}
?>