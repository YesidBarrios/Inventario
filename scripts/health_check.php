<?php
/**
 * Health Check del Sistema
 * Verifica que todos los componentes estén funcionando correctamente
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security_headers.php';

class HealthCheck {
    private $results = [];
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            $this->addResult('database', false, 'Error de conexión: ' . $this->conn->connect_error);
        } else {
            $this->addResult('database', true, 'Conexión exitosa');
        }
    }

    private function addResult($component, $status, $message, $details = null) {
        $this->results[$component] = [
            'status' => $status,
            'message' => $message,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public function checkDatabase() {
        if (!$this->conn) return;

        // Verificar tablas críticas
        $tables = ['productos', 'ventas', 'compras', 'usuarios', 'proveedores'];
        foreach ($tables as $table) {
            $result = $this->conn->query("SELECT COUNT(*) as count FROM `$table`");
            if ($result) {
                $count = $result->fetch_assoc()['count'];
                $this->addResult("table_$table", true, "Tabla $table OK", "Registros: $count");
            } else {
                $this->addResult("table_$table", false, "Error en tabla $table", $this->conn->error);
            }
        }

        // Verificar índices
        $result = $this->conn->query("SHOW INDEX FROM productos");
        $indexCount = $result ? $result->num_rows : 0;
        $this->addResult('indexes', $indexCount > 5, "Índices en productos", "Total índices: $indexCount");

        // Verificar constraints
        $result = $this->conn->query("
            SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = 'inventario_supermercado'
            AND CONSTRAINT_TYPE IN ('FOREIGN KEY', 'CHECK')
        ");
        $constraintCount = $result ? $result->fetch_assoc()['count'] : 0;
        $this->addResult('constraints', $constraintCount > 5, "Constraints activas", "Total: $constraintCount");
    }

    public function checkSecurity() {
        // Verificar archivo .env
        $envExists = file_exists(__DIR__ . '/../.env');
        $this->addResult('env_file', $envExists, $envExists ? 'Archivo .env existe' : 'Archivo .env NO existe');

        // Verificar API key
        $apiKeySet = !empty(GEMINI_API_KEY) && GEMINI_API_KEY !== 'tu_clave_de_gemini_aqui';
        $this->addResult('api_key', $apiKeySet, $apiKeySet ? 'API Key configurada' : 'API Key NO configurada');

        // Verificar directorios sensibles
        $sensitiveDirs = ['cache', 'logs', 'backups'];
        foreach ($sensitiveDirs as $dir) {
            $path = __DIR__ . '/../' . $dir;
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            $this->addResult("dir_$dir", $exists && $writable,
                $exists ? ($writable ? "Directorio $dir OK" : "Directorio $dir no escribible") : "Directorio $dir no existe");
        }
    }

    public function checkPerformance() {
        if (!$this->conn) return;

        // Medir tiempo de consulta crítica
        $start = microtime(true);
        $result = $this->conn->query("
            SELECT COUNT(*) as total FROM productos p
            LEFT JOIN unidades u ON p.unidad_id = u.id
            WHERE p.deleted = 0 OR p.deleted IS NULL
        ");
        $end = microtime(true);
        $time = round(($end - $start) * 1000, 2); // ms

        $this->addResult('query_performance', $time < 100, "Tiempo de consulta", "{$time}ms");

        // Verificar cache
        $cacheDir = __DIR__ . '/../cache';
        if (is_dir($cacheDir)) {
            $cacheFiles = glob($cacheDir . '/*.cache');
            $this->addResult('cache_system', true, 'Sistema de cache activo', count($cacheFiles) . ' archivos en cache');
        }
    }

    public function checkBackups() {
        $backupDir = __DIR__ . '/../backups';
        if (!is_dir($backupDir)) {
            $this->addResult('backups', false, 'Directorio de backups no existe');
            return;
        }

        $backupFiles = glob($backupDir . '/backup_*.sql*');
        $latestBackup = null;
        $latestTime = 0;

        foreach ($backupFiles as $file) {
            $fileTime = filemtime($file);
            if ($fileTime > $latestTime) {
                $latestTime = $fileTime;
                $latestBackup = $file;
            }
        }

        if ($latestBackup) {
            $daysSinceBackup = floor((time() - $latestTime) / 86400);
            $status = $daysSinceBackup <= 1; // Backup reciente (últimas 24h)
            $this->addResult('backups', $status, 'Sistema de backups activo',
                "Último backup: " . date('Y-m-d H:i', $latestTime) . " ({$daysSinceBackup} días atrás)");
        } else {
            $this->addResult('backups', false, 'No hay backups encontrados');
        }
    }

    public function runAllChecks() {
        $this->checkDatabase();
        $this->checkSecurity();
        $this->checkPerformance();
        $this->checkBackups();

        return $this->results;
    }

    public function getSummary() {
        $total = count($this->results);
        $passed = count(array_filter($this->results, function($r) { return $r['status']; }));
        $failed = $total - $passed;

        return [
            'total_checks' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'health_score' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
            'overall_status' => $failed === 0 ? 'HEALTHY' : ($failed <= 2 ? 'WARNING' : 'CRITICAL')
        ];
    }

    public function printReport() {
        $results = $this->runAllChecks();
        $summary = $this->getSummary();

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🏥 HEALTH CHECK REPORT - " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat("=", 60) . "\n\n";

        echo "📊 RESUMEN GENERAL:\n";
        echo "✅ Checks exitosos: {$summary['passed']}\n";
        echo "❌ Checks fallidos: {$summary['failed']}\n";
        echo "📈 Puntaje de salud: {$summary['health_score']}%\n";
        echo "🏷️  Estado general: {$summary['overall_status']}\n\n";

        echo "🔍 DETALLE POR COMPONENTE:\n";
        echo str_repeat("-", 60) . "\n";

        foreach ($results as $component => $result) {
            $status = $result['status'] ? '✅' : '❌';
            echo sprintf("%s %-20s %s\n", $status, $component, $result['message']);

            if (!empty($result['details'])) {
                echo "    └─ {$result['details']}\n";
            }
        }

        echo "\n" . str_repeat("=", 60) . "\n";

        if ($summary['overall_status'] === 'HEALTHY') {
            echo "🎉 ¡Sistema completamente saludable!\n";
        } elseif ($summary['overall_status'] === 'WARNING') {
            echo "⚠️  Sistema con algunos problemas menores\n";
        } else {
            echo "🚨 ¡Atención requerida! Revisar problemas críticos\n";
        }

        echo str_repeat("=", 60) . "\n\n";
    }
}

// Ejecutar health check
$healthCheck = new HealthCheck();
$healthCheck->printReport();
?>