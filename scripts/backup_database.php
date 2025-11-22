<?php
/**
 * Script de Backup Automático de Base de Datos
 * Uso: php backup_database.php [tipo]
 * Tipos: daily, weekly, monthly
 */

require_once __DIR__ . '/../includes/config.php';

class DatabaseBackup {
    private $conn;
    private $backupDir;
    private $maxBackups;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }

        $this->backupDir = __DIR__ . '/../backups/';
        $this->maxBackups = [
            'daily' => 7,    // Mantener 7 días
            'weekly' => 4,   // Mantener 4 semanas
            'monthly' => 12  // Mantener 12 meses
        ];

        // Crear directorio si no existe
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function createBackup($type = 'daily') {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$type}_{$timestamp}.sql";
        $filepath = $this->backupDir . $filename;

        try {
            // Obtener todas las tablas
            $tables = $this->getTables();

            $sql = "-- Backup Automático - Tipo: {$type}\n";
            $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Base de datos: " . DB_NAME . "\n\n";

            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $table) {
                $sql .= $this->getTableStructure($table);
                $sql .= $this->getTableData($table);
            }

            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

            // Guardar archivo
            file_put_contents($filepath, $sql);

            // Comprimir si es grande
            if (filesize($filepath) > 1024 * 1024) { // > 1MB
                $this->compressBackup($filepath);
            }

            // Limpiar backups antiguos
            $this->cleanupOldBackups($type);

            echo "✅ Backup creado exitosamente: {$filename}\n";
            echo "📁 Ubicación: {$filepath}\n";
            echo "📊 Tamaño: " . $this->formatBytes(filesize($filepath)) . "\n";

            return true;

        } catch (Exception $e) {
            echo "❌ Error al crear backup: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function getTables() {
        $result = $this->conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    private function getTableStructure($table) {
        $result = $this->conn->query("SHOW CREATE TABLE `{$table}`");
        $row = $result->fetch_assoc();

        $sql = "-- Estructura de tabla `{$table}`\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sql .= $row['Create Table'] . ";\n\n";

        return $sql;
    }

    private function getTableData($table) {
        $result = $this->conn->query("SELECT * FROM `{$table}`");

        if ($result->num_rows == 0) {
            return "-- No hay datos en `{$table}`\n\n";
        }

        $sql = "-- Datos de tabla `{$table}`\n";
        $columns = $this->getTableColumns($table);
        $columnNames = array_keys($columns);

        while ($row = $result->fetch_assoc()) {
            $values = [];
            foreach ($columnNames as $column) {
                $value = $row[$column];

                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (is_numeric($value) && !is_string($value)) {
                    $values[] = $value;
                } else {
                    $values[] = "'" . $this->conn->real_escape_string($value) . "'";
                }
            }

            $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columnNames) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }

        $sql .= "\n";
        return $sql;
    }

    private function getTableColumns($table) {
        $result = $this->conn->query("DESCRIBE `{$table}`");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row['Type'];
        }
        return $columns;
    }

    private function compressBackup($filepath) {
        $gzFile = $filepath . '.gz';
        $content = file_get_contents($filepath);

        // Comprimir con gzencode
        $compressed = gzencode($content, 9);

        if ($compressed !== false) {
            file_put_contents($gzFile, $compressed);
            unlink($filepath); // Eliminar archivo original
            echo "🗜️ Backup comprimido: " . basename($gzFile) . "\n";
        }
    }

    private function cleanupOldBackups($type) {
        $pattern = $this->backupDir . "backup_{$type}_*.sql*";
        $files = glob($pattern);

        if (count($files) > $this->maxBackups[$type]) {
            // Ordenar por fecha (más antiguos primero)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Eliminar archivos excedentes
            $toDelete = array_slice($files, 0, count($files) - $this->maxBackups[$type]);
            foreach ($toDelete as $file) {
                unlink($file);
                echo "🗑️ Backup antiguo eliminado: " . basename($file) . "\n";
            }
        }
    }

    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function listBackups($type = null) {
        $pattern = $type ? "backup_{$type}_*.sql*" : "backup_*.sql*";
        $files = glob($this->backupDir . $pattern);

        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a); // Más recientes primero
        });

        echo "📋 Lista de backups:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-40s %-12s %-10s\n", "Archivo", "Fecha", "Tamaño");
        echo str_repeat("-", 80) . "\n";

        foreach ($files as $file) {
            $filename = basename($file);
            $date = date('Y-m-d H:i', filemtime($file));
            $size = $this->formatBytes(filesize($file));

            printf("%-40s %-12s %-10s\n", $filename, $date, $size);
        }
    }
}

// Ejecutar backup
$type = $argv[1] ?? 'daily';
$validTypes = ['daily', 'weekly', 'monthly'];

if (!in_array($type, $validTypes)) {
    echo "❌ Tipo de backup inválido. Use: daily, weekly, o monthly\n";
    exit(1);
}

$backup = new DatabaseBackup();

// Si se pasa 'list' como argumento, mostrar lista
if (isset($argv[1]) && $argv[1] === 'list') {
    $backup->listBackups($argv[2] ?? null);
} else {
    $success = $backup->createBackup($type);
    exit($success ? 0 : 1);
}
?>