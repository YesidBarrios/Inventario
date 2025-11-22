<?php
/**
 * Optimizaciones de Queries Críticas
 * Cache y mejoras de rendimiento para consultas frecuentes
 */

class QueryOptimizer {
    private $conn;
    private $cacheDir;
    private $cacheTime;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->cacheDir = __DIR__ . '/../cache/queries/';
        $this->cacheTime = 300; // 5 minutos de cache

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Obtener estadísticas optimizadas del stock
     */
    public function getOptimizedStockStats() {
        $cacheKey = 'stock_stats';
        $cached = $this->getCache($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        // Query optimizada con índices
        $sql = "SELECT
                    COUNT(CASE WHEN deleted = 0 OR deleted IS NULL THEN 1 END) as total_productos,
                    SUM(CASE WHEN (deleted = 0 OR deleted IS NULL) THEN stock ELSE 0 END) as stock_total,
                    SUM(CASE WHEN (deleted = 0 OR deleted IS NULL) THEN precio * stock ELSE 0 END) as valor_total_inventario_precio,
                    COUNT(CASE WHEN (deleted = 0 OR deleted IS NULL) AND stock <= stock_minimo THEN 1 END) as productos_bajo_minimo
                FROM productos";

        $result = $this->conn->query($sql);
        if (!$result) {
            error_log("Error en query optimizada: " . $this->conn->error);
            return $this->getFallbackStockStats();
        }

        $stats = $result->fetch_assoc();

        // Asegurar valores numéricos
        $stats = array_map(function($value) {
            return is_numeric($value) ? (float)$value : 0;
        }, $stats);

        $this->setCache($cacheKey, $stats);
        return $stats;
    }

    /**
     * Fallback para estadísticas básicas
     */
    private function getFallbackStockStats() {
        return [
            'total_productos' => contarProductosActivos(),
            'stock_total' => $this->getTotalStock(),
            'valor_total_inventario_precio' => $this->getTotalValue(),
            'productos_bajo_minimo' => count(getLowStockProducts())
        ];
    }

    /**
     * Obtener productos con paginación optimizada
     */
    public function getOptimizedProducts($limit = 15, $offset = 0) {
        $cacheKey = "products_{$limit}_{$offset}";
        $cached = $this->getCache($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.stock_minimo,
                       p.fecha_creacion, u.nombre as nombre_unidad, u.abreviatura as abreviatura_unidad,
                       prov.nombre as nombre_proveedor
                FROM productos p
                LEFT JOIN unidades u ON p.unidad_id = u.id
                LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
                WHERE p.deleted = 0 OR p.deleted IS NULL
                ORDER BY p.id ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando query optimizada: " . $this->conn->error);
            return obtenerProductos($limit, $offset); // fallback
        }

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        $stmt->close();
        $this->setCache($cacheKey, $productos);
        return $productos;
    }

    /**
     * Búsqueda optimizada de productos
     */
    public function searchOptimizedProducts($searchTerm, $limit = 50) {
        $cacheKey = "search_" . md5($searchTerm) . "_{$limit}";
        $cached = $this->getCache($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        // Usar índice FULLTEXT si está disponible
        $sql = "SELECT p.*, u.nombre as nombre_unidad, u.abreviatura as abreviatura_unidad,
                       prov.nombre as nombre_proveedor,
                       MATCH(p.nombre, p.descripcion) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM productos p
                LEFT JOIN unidades u ON p.unidad_id = u.id
                LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
                WHERE (p.deleted = 0 OR p.deleted IS NULL)
                AND (MATCH(p.nombre, p.descripcion) AGAINST(? IN NATURAL LANGUAGE MODE)
                     OR p.nombre LIKE CONCAT('%', ?, '%')
                     OR p.descripcion LIKE CONCAT('%', ?, '%'))
                ORDER BY relevance DESC, p.nombre ASC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // Fallback a búsqueda básica
            return buscarProductos($searchTerm);
        }

        $searchParam = $searchTerm;
        $stmt->bind_param("sssi", $searchParam, $searchParam, $searchParam, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        $stmt->close();
        $this->setCache($cacheKey, $productos, 600); // Cache de 10 minutos para búsquedas
        return $productos;
    }

    /**
     * Obtener estadísticas del día optimizadas
     */
    public function getOptimizedDayStats() {
        $cacheKey = 'day_stats_' . date('Y-m-d');
        $cached = $this->getCache($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        $hoy = date('Y-m-d');

        // Query optimizada para estadísticas del día
        $sql = "SELECT
                    'ventas' as tipo,
                    COUNT(*) as cantidad,
                    COALESCE(SUM(precio_total), 0) as total
                FROM ventas
                WHERE DATE(fecha_venta) = ?
                UNION ALL
                SELECT
                    'compras' as tipo,
                    COUNT(*) as cantidad,
                    COALESCE(SUM(total_compra), 0) as total
                FROM compras
                WHERE DATE(fecha_compra) = ?
                UNION ALL
                SELECT
                    'productos_vendidos' as tipo,
                    COALESCE(SUM(cantidad_vendida), 0) as cantidad,
                    0 as total
                FROM ventas
                WHERE DATE(fecha_venta) = ?
                UNION ALL
                SELECT
                    'productos_comprados' as tipo,
                    COALESCE(SUM(cp.cantidad), 0) as cantidad,
                    0 as total
                FROM compras c
                JOIN compras_productos cp ON c.id = cp.compra_id
                WHERE DATE(c.fecha_compra) = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Error en estadísticas del día: " . $this->conn->error);
            return $this->getFallbackDayStats();
        }

        $stmt->bind_param("ssss", $hoy, $hoy, $hoy, $hoy);
        $stmt->execute();
        $result = $stmt->get_result();

        $stats = [
            'ventas_hoy' => ['cantidad' => 0, 'total' => 0],
            'compras_hoy' => ['cantidad' => 0, 'total' => 0],
            'productos_vendidos' => 0,
            'productos_comprados' => 0
        ];

        while ($row = $result->fetch_assoc()) {
            switch ($row['tipo']) {
                case 'ventas':
                    $stats['ventas_hoy'] = ['cantidad' => (int)$row['cantidad'], 'total' => (float)$row['total']];
                    break;
                case 'compras':
                    $stats['compras_hoy'] = ['cantidad' => (int)$row['cantidad'], 'total' => (float)$row['total']];
                    break;
                case 'productos_vendidos':
                    $stats['productos_vendidos'] = (int)$row['cantidad'];
                    break;
                case 'productos_comprados':
                    $stats['productos_comprados'] = (int)$row['cantidad'];
                    break;
            }
        }

        $stmt->close();
        $this->setCache($cacheKey, $stats, 1800); // Cache de 30 minutos
        return $stats;
    }

    /**
     * Fallback para estadísticas del día
     */
    private function getFallbackDayStats() {
        $hoy_inicio = date('Y-m-d 00:00:00');
        $hoy_fin = date('Y-m-d 23:59:59');

        $ventas_hoy = obtenerVentasPorPeriodo($hoy_inicio, $hoy_fin);
        $compras_hoy = obtenerComprasPorPeriodo($hoy_inicio, $hoy_fin);

        return [
            'ventas_hoy' => [
                'cantidad' => count($ventas_hoy),
                'total' => array_sum(array_column($ventas_hoy, 'precio_total'))
            ],
            'compras_hoy' => [
                'cantidad' => count($compras_hoy),
                'total' => array_sum(array_column($compras_hoy, 'total_compra'))
            ],
            'productos_vendidos' => array_sum(array_column($ventas_hoy, 'cantidad_vendida')),
            'productos_comprados' => 0 // Simplificado para fallback
        ];
    }

    /**
     * Métodos de cache
     */
    private function getCache($key) {
        $file = $this->cacheDir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return false;
        }

        if (time() - filemtime($file) > $this->cacheTime) {
            unlink($file);
            return false;
        }

        $data = unserialize(file_get_contents($file));
        return $data;
    }

    private function setCache($key, $data, $ttl = null) {
        $file = $this->cacheDir . md5($key) . '.cache';
        $ttl = $ttl ?? $this->cacheTime;

        file_put_contents($file, serialize($data));

        // Establecer tiempo de expiración
        touch($file, time() + $ttl);
    }

    /**
     * Limpiar cache
     */
    public function clearCache() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Helpers para estadísticas básicas
     */
    private function getTotalStock() {
        $result = $this->conn->query("SELECT SUM(stock) as total FROM productos WHERE deleted = 0 OR deleted IS NULL");
        return $result ? (int)$result->fetch_assoc()['total'] : 0;
    }

    private function getTotalValue() {
        $result = $this->conn->query("SELECT SUM(precio * stock) as total FROM productos WHERE deleted = 0 OR deleted IS NULL");
        return $result ? (float)$result->fetch_assoc()['total'] : 0.0;
    }
}

// Instancia global para uso fácil
$queryOptimizer = new QueryOptimizer();
?>