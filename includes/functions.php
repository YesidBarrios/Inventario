<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
    die("Ocurrió un error al conectar con la base de datos. Por favor, inténtalo de nuevo más tarde.");
}

function getAllUnidades() {
    global $conn;
    $sql = "SELECT * FROM unidades ORDER BY nombre ASC";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener unidades: " . mysqli_error($conn));
        return [];
    }
    $unidades = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $unidades[] = $row;
    }
    mysqli_free_result($result);
    return $unidades;
}

function contarProductosActivos() {
    global $conn;
    $sql = "SELECT COUNT(id) as total FROM productos WHERE deleted = 0 OR deleted IS NULL";
    $result = mysqli_query($conn, $sql);
    if ($result === false) return 0;
    $row = mysqli_fetch_assoc($result);
    return (int)$row['total'];
}

function obtenerProductos($limit = null, $offset = null) {
    global $conn;
    $sql = "SELECT p.*, u.nombre as nombre_unidad, u.abreviatura as abreviatura_unidad FROM productos p LEFT JOIN unidades u ON p.unidad_id = u.id WHERE p.deleted = 0 OR p.deleted IS NULL ORDER BY p.id ASC";
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    }
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener productos: " . mysqli_error($conn));
        return [];
    }
    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
    mysqli_free_result($result);
    return $productos;
}

function agregarProducto($nombre, $descripcion, $precio, $costo, $stock, $stock_minimo, $proveedor_id = null, $unidad_id = null) {
    global $conn;
    $sql = "INSERT INTO productos (nombre, descripcion, precio, costo, stock, stock_minimo, proveedor_id, unidad_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta para agregar producto: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ssddiiii", $nombre, $descripcion, $precio, $costo, $stock, $stock_minimo, $proveedor_id, $unidad_id);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Error al ejecutar la consulta para agregar producto: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function obtenerProductoPorId($id) {
    global $conn;
    $sql = "SELECT p.*, u.nombre as nombre_unidad, u.abreviatura as abreviatura_unidad FROM productos p LEFT JOIN unidades u ON p.unidad_id = u.id WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta para obtener producto por ID: " . $conn->error);
        return null;
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $producto = $result->fetch_assoc();
        $stmt->close();
        return $producto;
    } else {
        error_log("Error al ejecutar la consulta para obtener producto por ID: " . $stmt->error);
        $stmt->close();
        return null;
    }
}

function actualizarProducto($id, $nombre, $descripcion, $precio, $costo, $stock_minimo, $proveedor_id = null, $unidad_id = null) {
    global $conn;

    $sql_check = "SELECT id FROM productos WHERE nombre = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check);
    if ($stmt_check === false) {
        error_log("Error al preparar la consulta de verificación de nombre: " . $conn->error);
        return false;
    }
    $stmt_check->bind_param("si", $nombre, $id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        error_log("Intento de actualizar a un nombre de producto que ya existe: " . $nombre);
        return false;
    }
    $stmt_check->close();

    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, costo = ?, stock_minimo = ?, proveedor_id = ?, unidad_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta para actualizar producto: " . $conn->error);
        return false;
    }
    $stmt->bind_param("ssdddiii", $nombre, $descripcion, $precio, $costo, $stock_minimo, $proveedor_id, $unidad_id, $id);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Error al ejecutar la consulta para actualizar producto: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function eliminarProducto($id) {
    global $conn;
    $sql = "UPDATE productos SET deleted = 1, deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta para eliminar producto: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
    } else {
        error_log("Error al ejecutar la consulta para eliminar producto: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
}

function recuperarProductoEliminado($id) {
    global $conn;
    
    $sql = "UPDATE productos SET
            deleted = 0,
            deleted_at = NULL
            WHERE id = ?";
            
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para recuperar producto: " . $conn->error);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
    } else {
        error_log("Error al ejecutar la consulta para recuperar producto: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
}

function obtenerProductosEliminados() {
    global $conn;
    
    $sql = "SELECT * FROM productos WHERE deleted = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        error_log("Error al obtener productos eliminados: " . mysqli_error($conn));
        return [];
    }

    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
    
    mysqli_free_result($result);
    return $productos;
}

function obtenerConfiguracion() {
    global $conn;
    
    $sql = "SELECT * FROM configuracion LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        error_log("Error al obtener configuración: " . mysqli_error($conn));
        return ['nombre_tienda' => 'Mi Tienda', 'direccion' => '', 'telefono' => '', 'email' => ''];
    }

    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_free_result($result);
        return $row;
    }
    
    return ['nombre_tienda' => 'Mi Tienda', 'direccion' => '', 'telefono' => '', 'email' => ''];
}

function guardarConfiguracion($nombre_tienda, $direccion, $telefono, $email) {
    global $conn;
    $sql = "UPDATE configuracion SET nombre_tienda = ?, direccion = ?, telefono = ?, email = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre_tienda, $direccion, $telefono, $email);
    return $stmt->execute();
}

function verificarCredenciales($username, $password) {
    global $conn;
    
    $username = strtolower(trim($username));
    
    $sql = "SELECT id, username, password, rol FROM usuarios WHERE LOWER(username) = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para verificar credenciales: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $stmt->close();
                unset($user['password']);
                return $user;
            }
        }
        $stmt->close();
        return false;
    } else {
        error_log("Error al ejecutar la consulta para verificar credenciales: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function buscarProductos($searchTerm) {
    global $conn;
    $searchTerm = trim($searchTerm);

    $searchPattern = "%" . $searchTerm . "%";

    $palabras = explode(' ', $searchTerm);
    $condiciones = [];

    foreach ($palabras as $palabra) {
        if (strlen($palabra) > 2) {
            $condiciones[] = "(p.nombre LIKE '%" . $conn->real_escape_string($palabra) . "%' OR p.descripcion LIKE '%" . $conn->real_escape_string($palabra) . "%')";
        }
    }

    $condicion_adicional = "";
    if (!empty($condiciones)) {
        $condicion_adicional = " OR (" . implode(" AND ", $condiciones) . ")";
    }

    $sql = "SELECT p.*, u.nombre as nombre_unidad, u.abreviatura as abreviatura_unidad
            FROM productos p
            LEFT JOIN unidades u ON p.unidad_id = u.id
            WHERE ((p.nombre LIKE ? OR p.descripcion LIKE ?)" . $condicion_adicional . ")
            AND (p.deleted = 0 OR p.deleted IS NULL)
            ORDER BY
                CASE
                    WHEN LOWER(p.nombre) = LOWER(?) THEN 1
                    WHEN p.nombre LIKE ? THEN 2
                    ELSE 3
                END,
                p.nombre ASC";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para buscar productos: " . $conn->error);
        return [];
    }

    $exactMatch = $searchTerm;
    $startsWithPattern = $searchTerm . "%";

    $stmt->bind_param("ssss", $searchPattern, $searchPattern, $exactMatch, $startsWithPattern);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        $stmt->close();
        return $productos;
    } else {
        error_log("Error al ejecutar la consulta para buscar productos: " . $stmt->error);
        $stmt->close();
        return [];
    }
}

function getUnidadById($id) {
    global $conn;
    $sql = "SELECT * FROM unidades WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) return null;
    $result = $stmt->get_result();
    $unidad = $result->fetch_assoc();
    $stmt->close();
    return $unidad;
}

function getVentasByTransaccionId($transaccion_id) {
    global $conn;
    $sql = "SELECT v.*, p.nombre as nombre_producto FROM ventas v JOIN productos p ON v.producto_id = p.id WHERE v.transaccion_id = ? ORDER BY v.id ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("s", $transaccion_id);
    if (!$stmt->execute()) return [];
    $result = $stmt->get_result();
    $ventas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $ventas;
}

function getLowStockProducts() {
    global $conn;

    $sql = "SELECT p.*, prov.nombre as nombre_proveedor
            FROM productos p
            LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
            WHERE p.stock <= p.stock_minimo AND (p.deleted = 0 OR p.deleted IS NULL)
            ORDER BY p.nombre ASC";

    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        error_log("Error al obtener productos con stock bajo: " . mysqli_error($conn));
        return [];
    }

    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }

    mysqli_free_result($result);
    return $productos;
}

function getAllProveedores() {
    global $conn;

    $sql = "SELECT * FROM proveedores ORDER BY nombre ASC";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        error_log("Error al obtener proveedores: " . mysqli_error($conn));
        return [];
    }

    $proveedores = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $proveedores[] = $row;
    }

    mysqli_free_result($result);
    return $proveedores;
}

function addProveedor($nombre, $telefono, $email) {
    global $conn;

    $sql = "INSERT INTO proveedores (nombre, telefono, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para agregar proveedor: " . $conn->error);
        return false;
    }

    $stmt->bind_param("sss", $nombre, $telefono, $email);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Error al ejecutar la consulta para agregar proveedor: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function getProveedorById($id) {
    global $conn;
    $sql = "SELECT * FROM proveedores WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para obtener proveedor por ID: " . $conn->error);
        return null;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("Error al obtener el resultado para obtener proveedor por ID: " . $stmt->error);
            $stmt->close();
            return null;
        }
        $proveedor = $result->fetch_assoc();
        $stmt->close();
        return $proveedor;
    } else {
        error_log("Error al ejecutar la consulta para obtener proveedor por ID: " . $stmt->error);
        $stmt->close();
        return null;
    }
}

function updateProveedor($id, $nombre, $telefono, $email) {
    global $conn;
    $sql = "UPDATE proveedores SET nombre = ?, telefono = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para actualizar proveedor: " . $conn->error);
        return false;
    }

    $stmt->bind_param("sssi", $nombre, $telefono, $email, $id);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Error al ejecutar la consulta para actualizar proveedor: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function obtenerHistorialProductos() {
    global $conn;
    
    $sql = "SELECT * FROM productos_historial ORDER BY fecha_accion DESC";
    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        error_log("Error al obtener historial de productos: " . mysqli_error($conn));
        return [];
    }

    $historial = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $historial[] = $row;
    }
    
    mysqli_free_result($result);
    return $historial;
}

function finalizarVentaCarrito($carrito, $usuario_id) {
    global $conn;
    $conn->begin_transaction();
    $transaccion_id = "T-" . time();

    try {
        foreach ($carrito as $item) {
            $producto_id = $item['producto_id'];
            $cantidad_vendida = $item['cantidad'];
            $unidad_venta_id = $item['unidad_id'];

            $producto = obtenerProductoPorId($producto_id);
            if (!$producto) throw new Exception("Producto con ID {$producto_id} no encontrado.");

            $unidad_venta = getUnidadById($unidad_venta_id);
            $unidad_base = getUnidadById($producto['unidad_id']);
            if (!$unidad_venta || !$unidad_base) throw new Exception("Unidades de medida no válidas para el producto {$producto['nombre']}.");

            $factor_conversion_venta = (float)$unidad_venta['factor_conversion'];
            $factor_conversion_base = (float)$unidad_base['factor_conversion'];
            if ($factor_conversion_base == 0) throw new Exception("Factor de conversión base es cero para el producto {$producto['nombre']}.");

            $cantidad_a_descontar = ($cantidad_vendida * $factor_conversion_venta) / $factor_conversion_base;

            if ((float)$producto['stock'] < $cantidad_a_descontar) {
                throw new Exception("Stock insuficiente para {$producto['nombre']}.");
            }

            $precio_por_unidad_base = (float)$producto['precio'];
            $precio_total_item = ($precio_por_unidad_base / $factor_conversion_base) * $factor_conversion_venta * $cantidad_vendida;
            $precio_unitario_calculado = $precio_total_item / $cantidad_vendida;

            $costo_por_unidad_base = (float)$producto['costo'];
            $costo_total_item = ($costo_por_unidad_base / $factor_conversion_base) * $factor_conversion_venta * $cantidad_vendida;

            $sql_venta = "INSERT INTO ventas (producto_id, cantidad_vendida, unidad_vendida, precio_unitario, precio_total, costo_total, usuario_id, transaccion_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_venta = $conn->prepare($sql_venta);
            $nombre_unidad_venta = $unidad_venta['abreviatura'];
            $stmt_venta->bind_param("isssddis", $producto_id, $cantidad_vendida, $nombre_unidad_venta, $precio_unitario_calculado, $precio_total_item, $costo_total_item, $usuario_id, $transaccion_id);
            if (!$stmt_venta->execute()) throw new Exception("Error al registrar item de venta: " . $stmt_venta->error);
            $stmt_venta->close();

            $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
            $stmt_stock = $conn->prepare($sql_stock);
            $stmt_stock->bind_param("di", $cantidad_a_descontar, $producto_id);
            if (!$stmt_stock->execute()) throw new Exception("Error al actualizar stock: " . $stmt_stock->error);
            $stmt_stock->close();
        }

        $conn->commit();
        return ['success' => true, 'transaccion_id' => $transaccion_id];

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en transacción de carrito: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function finalizarCompraCarrito($carrito, $proveedor_id, $usuario_id) {
    global $conn;
    $conn->begin_transaction();

    try {
        $total_compra = 0;
        foreach ($carrito as $item) {
            $total_compra += $item['cantidad'] * $item['costo'];
        }

        $sql_compra = "INSERT INTO compras (proveedor_id, total_compra) VALUES (?, ?)";
        $stmt_compra = $conn->prepare($sql_compra);
        if ($stmt_compra === false) throw new Exception("Error al preparar la consulta de compra: " . $conn->error);
        $stmt_compra->bind_param("id", $proveedor_id, $total_compra);
        if (!$stmt_compra->execute()) throw new Exception("Error al registrar la compra: " . $stmt_compra->error);
        $compra_id = $stmt_compra->insert_id;
        $stmt_compra->close();

        foreach ($carrito as $item) {
            $producto_id = $item['producto_id'];
            $cantidad_comprada = $item['cantidad'];
            $costo_unitario_compra = $item['costo'];
            $unidad_compra_id = $item['unidad_id'];

            $producto = obtenerProductoPorId($producto_id);
            if (!$producto) throw new Exception("Producto con ID {$producto_id} no encontrado.");

            $unidad_compra = getUnidadById($unidad_compra_id);
            $unidad_base = getUnidadById($producto['unidad_id']);
            if (!$unidad_compra || !$unidad_base) throw new Exception("Unidades de medida no válidas para el producto {$producto['nombre']}.");

            $factor_conversion_compra = (float)$unidad_compra['factor_conversion'];
            $factor_conversion_base = (float)$unidad_base['factor_conversion'];
            if ($factor_conversion_base == 0) throw new Exception("Factor de conversión base es cero para el producto {$producto['nombre']}.");

            $cantidad_a_sumar = ($cantidad_comprada * $factor_conversion_compra) / $factor_conversion_base;
            $costo_normalizado = ($costo_unitario_compra / $factor_conversion_compra) * $factor_conversion_base;

            $sql_item = "INSERT INTO compras_productos (compra_id, producto_id, cantidad, costo_unitario) VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            if ($stmt_item === false) throw new Exception("Error al preparar la consulta de item de compra: " . $conn->error);
            $stmt_item->bind_param("iidd", $compra_id, $producto_id, $cantidad_comprada, $costo_unitario_compra);
            if (!$stmt_item->execute()) throw new Exception("Error al registrar item de compra: " . $stmt_item->error);
            $stmt_item->close();

            $sql_stock = "UPDATE productos SET stock = stock + ?, costo = ? WHERE id = ?";
            $stmt_stock = $conn->prepare($sql_stock);
            if ($stmt_stock === false) throw new Exception("Error al preparar la consulta de actualización de stock: " . $conn->error);
            $stmt_stock->bind_param("ddi", $cantidad_a_sumar, $costo_normalizado, $producto_id);
            if (!$stmt_stock->execute()) throw new Exception("Error al actualizar stock y costo: " . $stmt_stock->error);
            $stmt_stock->close();
        }

        $conn->commit();
        return ['success' => true, 'compra_id' => $compra_id];

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en transacción de compra de carrito: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function getCompraById($id) {
    global $conn;
    $sql = "SELECT c.*, p.nombre as nombre_proveedor FROM compras c LEFT JOIN proveedores p ON c.proveedor_id = p.id WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) return null;
    $result = $stmt->get_result();
    $compra = $result->fetch_assoc();
    $stmt->close();
    return $compra;
}

function getCompraProductosByCompraId($compra_id) {
    global $conn;
    $sql = "SELECT cp.*, p.nombre as nombre_producto FROM compras_productos cp JOIN productos p ON cp.producto_id = p.id WHERE cp.compra_id = ? ORDER BY cp.id ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $compra_id);
    if (!$stmt->execute()) return [];
    $result = $stmt->get_result();
    $productos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $productos;
}

function obtenerHistorialCompras() {
    global $conn;
    $sql = "SELECT c.id, c.fecha_compra, c.total_compra, p.nombre as nombre_proveedor
            FROM compras c
            LEFT JOIN proveedores p ON c.proveedor_id = p.id
            ORDER BY c.fecha_compra DESC";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener historial de compras: " . mysqli_error($conn));
        return [];
    }
    $historial = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $historial[] = $row;
    }
    mysqli_free_result($result);
    return $historial;
}

function obtenerEstadisticasStock() {
    global $conn;
    $sql = "SELECT 
            COUNT(*) as total_productos,
            COALESCE(SUM(stock), 0) as stock_total,
            COALESCE(SUM(CASE WHEN stock <= stock_minimo THEN 1 ELSE 0 END), 0) as productos_bajo_minimo,
            COALESCE(SUM(stock * costo), 0) as valor_total_inventario_costo,
            COALESCE(SUM(stock * precio), 0) as valor_total_inventario_precio
            FROM productos 
            WHERE deleted = 0 OR deleted IS NULL";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener estadísticas de stock: " . mysqli_error($conn));
        return ['total_productos' => 0, 'stock_total' => 0, 'productos_bajo_minimo' => 0, 'valor_total_inventario_costo' => 0, 'valor_total_inventario_precio' => 0];
    }
    return mysqli_fetch_assoc($result);
}

function obtenerReporteFinanciero($fecha_inicio, $fecha_fin) {
    global $conn;
    $reporte = [
        'ingresos_brutos' => 0,
        'costo_mercancia' => 0,
        'total_compras' => 0,
        'ganancia_bruta' => 0
    ];

    $sql_ventas = "SELECT SUM(precio_total) as ingresos, SUM(costo_total) as costos FROM ventas WHERE fecha_venta BETWEEN ? AND ?";
    $stmt_ventas = $conn->prepare($sql_ventas);
    $stmt_ventas->bind_param("ss", $fecha_inicio, $fecha_fin);
    if ($stmt_ventas->execute()) {
        $resultado = $stmt_ventas->get_result()->fetch_assoc();
        $reporte['ingresos_brutos'] = $resultado['ingresos'] ?? 0;
        $reporte['costo_mercancia'] = $resultado['costos'] ?? 0;
    }
    $stmt_ventas->close();

    $sql_compras = "SELECT SUM(total_compra) as gastos FROM compras WHERE fecha_compra BETWEEN ? AND ?";
    $stmt_compras = $conn->prepare($sql_compras);
    $stmt_compras->bind_param("ss", $fecha_inicio, $fecha_fin);
    if ($stmt_compras->execute()) {
        $resultado = $stmt_compras->get_result()->fetch_assoc();
        $reporte['total_compras'] = $resultado['gastos'] ?? 0;
    }
    $stmt_compras->close();

    $reporte['ganancia_bruta'] = $reporte['ingresos_brutos'] - $reporte['costo_mercancia'];

    return $reporte;
}

function obtenerVentasPorPeriodo($fecha_inicio, $fecha_fin) {
    global $conn;
    $sql = "SELECT v.*, p.nombre as nombre_producto, u.username as nombre_usuario 
            FROM ventas v 
            JOIN productos p ON v.producto_id = p.id
            LEFT JOIN usuarios u ON v.usuario_id = u.id
            WHERE v.fecha_venta BETWEEN ? AND ? 
            ORDER BY v.fecha_venta DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $ventas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $ventas;
}

function obtenerHistorialVentas() {
    global $conn;
    $sql = "SELECT v.*, p.nombre as nombre_producto, u.username as nombre_usuario 
            FROM ventas v 
            JOIN productos p ON v.producto_id = p.id
            LEFT JOIN usuarios u ON v.usuario_id = u.id
            ORDER BY v.fecha_venta DESC";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener historial de ventas: " . mysqli_error($conn));
        return [];
    }
    $ventas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ventas[] = $row;
    }
    mysqli_free_result($result);
    return $ventas;
}

function obtenerComprasPorPeriodo($fecha_inicio, $fecha_fin) {
    global $conn;
    $sql = "SELECT c.*, p.nombre as nombre_proveedor
            FROM compras c
            LEFT JOIN proveedores p ON c.proveedor_id = p.id
            WHERE c.fecha_compra BETWEEN ? AND ?
            ORDER BY c.fecha_compra DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $compras = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $compras;
}

function obtenerProductoConMenorStock() {
    global $conn;
    $sql = "SELECT p.*, u.nombre as nombre_unidad 
            FROM productos p 
            LEFT JOIN unidades u ON p.unidad_id = u.id
            WHERE p.deleted = 0 OR p.deleted IS NULL 
            ORDER BY p.stock ASC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener producto con menor stock: " . mysqli_error($conn));
        return null;
    }
    return mysqli_fetch_assoc($result);
}

function obtenerDetalleVenta($transaccion_id) {
    // Wrapper amigable para el chatbot
    $ventas = getVentasByTransaccionId($transaccion_id);
    if (empty($ventas)) return null;
    
    $detalle = [
        'transaccion_id' => $transaccion_id,
        'fecha' => $ventas[0]['fecha_venta'],
        'items' => [],
        'total_transaccion' => 0
    ];
    
    foreach ($ventas as $v) {
        $detalle['items'][] = [
            'producto' => $v['nombre_producto'],
            'cantidad' => $v['cantidad_vendida'],
            'unidad' => $v['unidad_vendida'],
            'precio_unitario' => $v['precio_unitario'],
            'total' => $v['precio_total']
        ];
        $detalle['total_transaccion'] += $v['precio_total'];
    }
    return $detalle;
}

function obtenerDetalleCompra($compra_id) {
    // Wrapper amigable para el chatbot
    $compra = getCompraById($compra_id);
    if (!$compra) return null;
    
    $productos = getCompraProductosByCompraId($compra_id);
    
    return [
        'compra_id' => $compra['id'],
        'proveedor' => $compra['nombre_proveedor'],
        'fecha' => $compra['fecha_compra'],
        'total' => $compra['total_compra'],
        'items' => array_map(function($p) {
            return [
                'producto' => $p['nombre_producto'],
                'cantidad' => $p['cantidad'],
                'costo_unitario' => $p['costo_unitario']
            ];
        }, $productos)
    ];
}

function obtenerUltimasVentas($limite = 5) {
    global $conn;
    $limite = (int)$limite;
    $sql = "SELECT v.transaccion_id, v.fecha_venta, SUM(v.precio_total) as total, COUNT(v.id) as items
            FROM ventas v
            GROUP BY v.transaccion_id, v.fecha_venta
            ORDER BY v.fecha_venta DESC
            LIMIT $limite";
            
    $result = mysqli_query($conn, $sql);
    if ($result === false) return [];
    
    $ventas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ventas[] = $row;
    }
    return $ventas;
}


// --- FUNCIONES ADICIONALES PARA EL CHATBOT ---

// Función para explicar procesos paso a paso
function explicarProceso($proceso) {
    $explicaciones = [
        'agregar_producto' => [
            'titulo' => 'Cómo agregar un producto',
            'pasos' => [
                '1. Ve al menú principal y haz clic en "Nuevo Producto"',
                '2. Completa el nombre del producto',
                '3. Agrega una descripción detallada',
                '4. Establece el costo de compra y precio de venta',
                '5. Selecciona la unidad de medida apropiada',
                '6. Define el stock mínimo para alertas',
                '7. Opcionalmente, asigna un proveedor',
                '8. Haz clic en "Registrar Producto"'
            ],
            'nota' => 'El stock inicial se establece en 0. Para agregar stock, ve al módulo de compras.'
        ],
        'editar_producto' => [
            'titulo' => 'Cómo editar un producto',
            'pasos' => [
                '1. En la lista de productos, busca el producto que quieres editar',
                '2. Haz clic en el ícono de edición (lápiz) en la columna "Acciones"',
                '3. Modifica los campos necesarios (nombre, precio, descripción, etc.)',
                '4. Asegúrate de que el nombre no se repita con otro producto',
                '5. Solo los administradores pueden editar productos',
                '6. Haz clic en "Guardar Cambios"'
            ],
            'nota' => 'El stock no se puede editar directamente desde aquí. Usa el módulo de compras o ventas.'
        ],
        'cambiar_configuracion' => [
            'titulo' => 'Cómo cambiar la configuración del sistema',
            'pasos' => [
                '1. Ve al menú principal y haz clic en el ícono de configuración (engranaje)',
                '2. Modifica el nombre de la tienda',
                '3. Actualiza la dirección',
                '4. Cambia el teléfono de contacto',
                '5. Actualiza el email de contacto',
                '6. Haz clic en "Guardar Configuración"'
            ],
            'nota' => 'Estos cambios afectan cómo se muestra la información en todo el sistema.'
        ],
        'reabastecer_producto' => [
            'titulo' => 'Cómo reabastecer un producto',
            'pasos' => [
                '1. Ve al menú principal y haz clic en "Registrar Compra"',
                '2. Selecciona el producto que quieres reabastecer',
                '3. Elige la cantidad que vas a comprar',
                '4. Selecciona la unidad de medida',
                '5. Establece el costo unitario de compra',
                '6. Selecciona o agrega un proveedor',
                '7. Agrega el producto al carrito',
                '8. Finaliza la compra para actualizar el stock'
            ],
            'nota' => 'También puedes hacer clic en "Reabastecer" desde la página de productos con stock bajo.'
        ],
        'registrar_venta' => [
            'titulo' => 'Cómo registrar una venta',
            'pasos' => [
                '1. Ve al menú principal y haz clic en "Nueva Venta"',
                '2. Busca y selecciona el producto que vas a vender',
                '3. Especifica la cantidad a vender',
                '4. Selecciona la unidad de medida',
                '5. Agrega el producto al carrito',
                '6. Repite para más productos si es necesario',
                '7. Revisa el carrito y haz clic en "Finalizar Venta"',
                '8. El sistema generará automáticamente un recibo'
            ],
            'nota' => 'El stock se actualizará automáticamente después de confirmar la venta.'
        ]
    ];

    return $explicaciones[$proceso] ?? [
        'titulo' => 'Proceso no encontrado',
        'pasos' => ['Lo siento, no tengo información detallada sobre este proceso.'],
        'nota' => 'Intenta con una pregunta más específica.'
    ];
}

// Función para calcular precios con conversiones de unidades
function calcularPrecioConConversion($producto_id, $cantidad, $unidad_solicitada_id) {
    $producto = obtenerProductoPorId($producto_id);
    if (!$producto) return ['error' => 'Producto no encontrado'];

    $unidad_base = getUnidadById($producto['unidad_id']);
    $unidad_solicitada = getUnidadById($unidad_solicitada_id);

    if (!$unidad_base || !$unidad_solicitada) {
        return ['error' => 'Unidad de medida no válida'];
    }

    $factor_base = (float)$unidad_base['factor_conversion'];
    $factor_solicitado = (float)$unidad_solicitada['factor_conversion'];

    if ($factor_base == 0) {
        return ['error' => 'Error en la configuración de unidades'];
    }

    // Calcular el precio por unidad solicitada
    $precio_por_unidad_base = (float)$producto['precio'];
    $precio_unitario_solicitado = ($precio_por_unidad_base / $factor_base) * $factor_solicitado;
    $precio_total = $precio_unitario_solicitado * $cantidad;

    return [
        'producto' => $producto['nombre'],
        'cantidad_solicitada' => $cantidad,
        'unidad_solicitada' => $unidad_solicitada['abreviatura'],
        'precio_unitario' => $precio_unitario_solicitado,
        'precio_total' => $precio_total,
        'unidad_base' => $unidad_base['abreviatura'],
        'precio_base' => $precio_base
    ];
}

// Nueva función mejorada para calcular precios por nombre de producto
function calcularPrecioPorNombre($nombre_producto, $cantidad, $unidad_solicitada = null) {
    // Normalizar el nombre del producto para búsqueda
    $nombre_normalizado = normalizarTexto($nombre_producto);

    // Primero intentar búsqueda exacta
    $productos = buscarProductosExactos($nombre_normalizado);

    // Si no encuentra con búsqueda exacta, usar búsqueda aproximada
    if (empty($productos)) {
        $productos = buscarProductos($nombre_normalizado);
    }

    // Si aún no encuentra, intentar con el nombre original
    if (empty($productos)) {
        $productos = buscarProductos($nombre_producto);
    }

    if (empty($productos)) {
        // Obtener algunos productos disponibles para sugerir
        $productos_disponibles = obtenerProductos(0, 10); // Primeros 10 productos
        $sugerencias = array_slice(array_map(function($p) { return $p['nombre']; }, $productos_disponibles), 0, 5);

        return [
            'error' => 'Producto no encontrado',
            'mensaje' => "No encontré ningún producto llamado '{$nombre_producto}'",
            'sugerencias' => $sugerencias,
            'ayuda' => 'Puedes intentar con uno de estos productos o verificar el nombre exacto'
        ];
    }

    if (count($productos) > 1) {
        $nombres = array_map(function($p) { return $p['nombre']; }, $productos);
        return [
            'error' => 'Múltiples productos encontrados',
            'mensaje' => "Encontré " . count($productos) . " productos que coinciden con '{$nombre_producto}'",
            'productos' => $nombres,
            'sugerencia' => 'Por favor, sé más específico con el nombre del producto'
        ];
    }

    $producto = $productos[0];

    // Si no se especifica unidad, usar la unidad base del producto
    if ($unidad_solicitada === null) {
        $unidad_solicitada_id = $producto['unidad_id'];
    } else {
        // Buscar la unidad por nombre o abreviatura con más flexibilidad
        $unidades = getAllUnidades();
        $unidad_normalizada = normalizarTexto($unidad_solicitada);
        $unidad_encontrada = null;

        // Mapa de variaciones comunes de unidades
        $variaciones_unidades = [
            'kilo' => 'kg',
            'kilos' => 'kg',
            'kilogramo' => 'kg',
            'kilogramos' => 'kg',
            'gramo' => 'g',
            'gramos' => 'g',
            'libra' => 'lb',
            'libras' => 'lb',
            'litro' => 'l',
            'litros' => 'l',
            'mililitro' => 'ml',
            'mililitros' => 'ml',
            'unidad' => 'un',
            'unidades' => 'un',
            'pieza' => 'un',
            'piezas' => 'un'
        ];

        // Primero buscar si hay una variación conocida
        if (isset($variaciones_unidades[$unidad_normalizada])) {
            $abreviatura_equivalente = $variaciones_unidades[$unidad_normalizada];
            foreach ($unidades as $unidad) {
                if (strtolower($unidad['abreviatura']) === $abreviatura_equivalente) {
                    $unidad_encontrada = $unidad;
                    break;
                }
            }
        }

        // Si no encontró con variaciones, buscar normalmente
        if (!$unidad_encontrada) {
            foreach ($unidades as $unidad) {
                if (strtolower($unidad['nombre']) === strtolower($unidad_solicitada) ||
                    strtolower($unidad['abreviatura']) === strtolower($unidad_solicitada) ||
                    normalizarTexto($unidad['nombre']) === $unidad_normalizada ||
                    normalizarTexto($unidad['abreviatura']) === $unidad_normalizada) {
                    $unidad_encontrada = $unidad;
                    break;
                }
            }
        }

        if (!$unidad_encontrada) {
            // Sugerir unidades disponibles
            $unidades_disponibles = array_map(function($u) {
                return $u['nombre'] . ' (' . $u['abreviatura'] . ')';
            }, $unidades);

            return [
                'error' => "Unidad '{$unidad_solicitada}' no encontrada",
                'mensaje' => "No reconozco la unidad '{$unidad_solicitada}'",
                'unidades_disponibles' => $unidades_disponibles,
                'ayuda' => 'Puedes usar: kg, g, lb, l, ml, un'
            ];
        }

        $unidad_solicitada_id = $unidad_encontrada['id'];
    }

    // Usar la función existente para calcular
    $resultado = calcularPrecioConConversion($producto['id'], $cantidad, $unidad_solicitada_id);

    if (isset($resultado['error'])) {
        return $resultado;
    }

    return [
        'producto' => $resultado['producto'],
        'cantidad_solicitada' => $resultado['cantidad_solicitada'],
        'unidad_solicitada' => $resultado['unidad_solicitada'],
        'precio_unitario' => $resultado['precio_unitario'],
        'precio_total' => $resultado['precio_total']
    ];
}

// Nueva función para consultas simples de precio (sin cantidad específica)
function obtenerPrecioSimple($nombre_producto) {
    $resultado = calcularPrecioPorNombre($nombre_producto, 1);

    if (isset($resultado['error'])) {
        return $resultado;
    }

    return [
        'producto' => $resultado['producto'],
        'precio_unitario' => $resultado['precio_unitario'],
        'unidad' => $resultado['unidad_solicitada'],
        'mensaje' => "El precio de {$resultado['producto']} es de $" . number_format($resultado['precio_unitario'], 2) . " por {$resultado['unidad_solicitada']}."
    ];
}

// Función de prueba para verificar funcionamiento básico
function probarFuncionesChatbot() {
    $resultados = [];

    // Probar búsqueda de productos
    $productos = obtenerProductos(0, 5);
    $resultados['productos_encontrados'] = count($productos);

    // Probar búsqueda exacta
    if (!empty($productos)) {
        $primer_producto = $productos[0]['nombre'];
        $busqueda_exacta = buscarProductosExactos($primer_producto);
        $resultados['busqueda_exacta_funciona'] = count($busqueda_exacta) > 0;
    }

    // Probar normalización de texto
    $texto_normalizado = normalizarTexto('Azúcar');
    $resultados['normalizacion_funciona'] = $texto_normalizado === 'azucar';

    // Probar cálculo de precio (si hay productos)
    if (!empty($productos)) {
        $primer_producto = $productos[0];
        $precio = calcularPrecioConConversion($primer_producto['id'], 1, $primer_producto['unidad_id']);
        $resultados['calculo_precio_funciona'] = !isset($precio['error']);
    }

    return $resultados;
}

// Nueva función para búsqueda exacta de productos (más flexible con tildes)
function buscarProductosExactos($nombre) {
    global $conn;

    // Normalizar el nombre de búsqueda (quitar tildes y caracteres especiales)
    $nombre_normalizado = normalizarTexto($nombre);

    $sql = "SELECT p.*, u.nombre as nombre_unidad, u.abreviatura as abreviatura_unidad,
                   LOWER(TRIM(p.nombre)) as nombre_lower
            FROM productos p
            LEFT JOIN unidades u ON p.unidad_id = u.id
            WHERE (p.deleted = 0 OR p.deleted IS NULL)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta de búsqueda exacta: " . $conn->error);
        return [];
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            // Comparar tanto con tildes como sin tildes
            $nombre_bd_normalizado = normalizarTexto($row['nombre']);
            if (strtolower(trim($row['nombre'])) === strtolower(trim($nombre)) ||
                $nombre_bd_normalizado === $nombre_normalizado) {
                $productos[] = $row;
            }
        }
        $stmt->close();
        return $productos;
    } else {
        error_log("Error al ejecutar la consulta de búsqueda exacta: " . $stmt->error);
        $stmt->close();
        return [];
    }
}

// Función para normalizar texto (quitar tildes y caracteres especiales)
function normalizarTexto($texto) {
    $texto = strtolower(trim($texto));

    // Reemplazar caracteres con tildes
    $reemplazos = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
        'ã' => 'a', 'õ' => 'o', 'ñ' => 'n', 'ç' => 'c'
    ];

    return strtr($texto, $reemplazos);
}

// Función para obtener recomendaciones basadas en el estado del inventario
function obtenerRecomendacionesInventario() {
    $productos_bajo_stock = getLowStockProducts();
    $estadisticas = obtenerEstadisticasStock();

    $recomendaciones = [];

    // Recomendaciones por stock bajo
    if (!empty($productos_bajo_stock)) {
        $recomendaciones[] = [
            'tipo' => 'alerta',
            'titulo' => 'Productos con stock bajo',
            'descripcion' => "Tienes " . count($productos_bajo_stock) . " productos que necesitan reabastecimiento",
            'productos' => array_map(function($p) {
                return $p['nombre'] . " (Stock: " . $p['stock'] . ")";
            }, $productos_bajo_stock),
            'accion' => 'Ve a "Stock Bajo" en el menú principal para reabastecer'
        ];
    }

    // Recomendaciones por estadísticas
    if ($estadisticas['productos_bajo_minimo'] > 0) {
        $porcentaje_bajo = round(($estadisticas['productos_bajo_minimo'] / $estadisticas['total_productos']) * 100, 1);
        $recomendaciones[] = [
            'tipo' => 'advertencia',
            'titulo' => 'Análisis de stock',
            'descripcion' => "{$porcentaje_bajo}% de tus productos están por debajo del stock mínimo",
            'productos' => [],
            'accion' => 'Revisa el módulo de reportes para más detalles'
        ];
    }

    // Recomendaciones generales
    if ($estadisticas['total_productos'] < 5) {
        $recomendaciones[] = [
            'tipo' => 'sugerencia',
            'titulo' => 'Catálogo pequeño',
            'descripcion' => 'Tu inventario tiene pocos productos. Considera agregar más variedad',
            'productos' => [],
            'accion' => 'Usa "Nuevo Producto" para expandir tu catálogo'
        ];
    }

    return $recomendaciones;
}

// Función para obtener estadísticas rápidas del día
function obtenerEstadisticasDia() {
    $hoy = date('Y-m-d');
    $ayer = date('Y-m-d', strtotime('-1 day'));

    $ventas_hoy = obtenerVentasPorPeriodo($hoy . ' 00:00:00', $hoy . ' 23:59:59');
    $compras_hoy = obtenerComprasPorPeriodo($hoy . ' 00:00:00', $hoy . ' 23:59:59');

    $total_ventas_hoy = array_sum(array_column($ventas_hoy, 'precio_total'));
    $total_compras_hoy = array_sum(array_column($compras_hoy, 'total_compra'));

    return [
        'ventas_hoy' => [
            'cantidad' => count($ventas_hoy),
            'total' => $total_ventas_hoy
        ],
        'compras_hoy' => [
            'cantidad' => count($compras_hoy),
            'total' => $total_compras_hoy
        ],
        'productos_vendidos' => count($ventas_hoy),
        'productos_comprados' => array_sum(array_map(function($compra) {
            return count(getCompraProductosByCompraId($compra['id']));
        }, $compras_hoy))
    ];
}

// Nueva función para obtener el producto con más stock
function obtenerProductoConMasStock() {
    global $conn;
    $sql = "SELECT * FROM productos WHERE (deleted = 0 OR deleted IS NULL) ORDER BY stock DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener producto con más stock: " . mysqli_error($conn));
        return ['error' => 'No se pudo obtener el producto con más stock.'];
    }
    $producto = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $producto;
}

// Nueva función para obtener el producto más vendido
function obtenerProductoMasVendido() {
    global $conn;
    $sql = "SELECT p.nombre, SUM(v.cantidad_vendida) as total_vendido
            FROM ventas v
            JOIN productos p ON v.producto_id = p.id
            GROUP BY v.producto_id, p.nombre
            ORDER BY total_vendido DESC
            LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        error_log("Error al obtener producto más vendido: " . mysqli_error($conn));
        return ['error' => 'No se pudo obtener el producto más vendido.'];
    }
    $producto = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $producto;
}

// Nueva función para analizar tendencias de ventas
function analizarTendenciasVentas() {
    global $conn;

    // Obtener ventas de los últimos 30 días
    $sql_ventas = "SELECT DATE(fecha_venta) as fecha, SUM(precio_total) as total_ventas, COUNT(*) as num_ventas
                   FROM ventas
                   WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                   GROUP BY DATE(fecha_venta)
                   ORDER BY fecha DESC";

    $result_ventas = mysqli_query($conn, $sql_ventas);
    if ($result_ventas === false) {
        return ['error' => 'No se pudieron obtener las tendencias de ventas.'];
    }

    $ventas_por_dia = [];
    while ($row = mysqli_fetch_assoc($result_ventas)) {
        $ventas_por_dia[] = $row;
    }
    mysqli_free_result($result_ventas);

    // Calcular promedio semanal
    $total_ventas_mes = array_sum(array_column($ventas_por_dia, 'total_ventas'));
    $dias_con_ventas = count($ventas_por_dia);
    $promedio_diario = $dias_con_ventas > 0 ? $total_ventas_mes / $dias_con_ventas : 0;

    // Obtener productos más vendidos del mes
    $sql_top_productos = "SELECT p.nombre, SUM(v.cantidad_vendida) as cantidad_total, SUM(v.precio_total) as ingresos_total
                          FROM ventas v
                          JOIN productos p ON v.producto_id = p.id
                          WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                          GROUP BY v.producto_id, p.nombre
                          ORDER BY cantidad_total DESC
                          LIMIT 5";

    $result_top = mysqli_query($conn, $sql_top_productos);
    $top_productos = [];
    if ($result_top) {
        while ($row = mysqli_fetch_assoc($result_top)) {
            $top_productos[] = $row;
        }
        mysqli_free_result($result_top);
    }

    // Calcular tendencia (comparar con mes anterior)
    $sql_mes_anterior = "SELECT SUM(precio_total) as ventas_mes_anterior
                         FROM ventas
                         WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                         AND fecha_venta < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

    $result_anterior = mysqli_query($conn, $sql_mes_anterior);
    $ventas_mes_anterior = 0;
    if ($result_anterior) {
        $row = mysqli_fetch_assoc($result_anterior);
        $ventas_mes_anterior = $row['ventas_mes_anterior'] ?? 0;
        mysqli_free_result($result_anterior);
    }

    $tendencia_porcentaje = $ventas_mes_anterior > 0 ?
        (($total_ventas_mes - $ventas_mes_anterior) / $ventas_mes_anterior) * 100 : 0;

    return [
        'periodo_analizado' => 'Últimos 30 días',
        'total_ventas_mes' => $total_ventas_mes,
        'promedio_diario' => $promedio_diario,
        'dias_con_ventas' => $dias_con_ventas,
        'tendencia_vs_mes_anterior' => $tendencia_porcentaje,
        'top_productos' => $top_productos,
        'ventas_por_dia' => $ventas_por_dia
    ];
}

// Nueva función para generar alertas inteligentes
function generarAlertasInteligentes() {
    global $conn;
    $alertas = [];

    // Alerta 1: Productos sin ventas en los últimos 30 días
    $sql_sin_ventas = "SELECT p.nombre, p.stock, p.stock_minimo
                       FROM productos p
                       WHERE p.deleted = 0
                       AND p.id NOT IN (
                           SELECT DISTINCT v.producto_id
                           FROM ventas v
                           WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                       )
                       AND p.stock > 0
                       ORDER BY p.stock DESC";

    $result_sin_ventas = mysqli_query($conn, $sql_sin_ventas);
    if ($result_sin_ventas) {
        $productos_sin_ventas = [];
        while ($row = mysqli_fetch_assoc($result_sin_ventas)) {
            $productos_sin_ventas[] = $row;
        }
        mysqli_free_result($result_sin_ventas);

        if (!empty($productos_sin_ventas)) {
            $alertas[] = [
                'tipo' => 'advertencia',
                'titulo' => 'Productos sin movimiento',
                'descripcion' => "Tienes " . count($productos_sin_ventas) . " productos que no se han vendido en los últimos 30 días",
                'productos_afectados' => array_slice(array_map(function($p) { return $p['nombre']; }, $productos_sin_ventas), 0, 3),
                'recomendacion' => 'Considera promociones o revisar precios'
            ];
        }
    }

    // Alerta 2: Productos con rotación muy rápida (posible especulación)
    $sql_rotacion_rapida = "SELECT p.nombre, SUM(v.cantidad_vendida) as vendido_30_dias, p.stock
                            FROM productos p
                            JOIN ventas v ON p.id = v.producto_id
                            WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                            GROUP BY p.id, p.nombre, p.stock
                            HAVING vendido_30_dias > (p.stock * 0.8)
                            ORDER BY vendido_30_dias DESC";

    $result_rotacion = mysqli_query($conn, $sql_rotacion_rapida);
    if ($result_rotacion) {
        $productos_rotacion_rapida = [];
        while ($row = mysqli_fetch_assoc($result_rotacion)) {
            $productos_rotacion_rapida[] = $row;
        }
        mysqli_free_result($result_rotacion);

        if (!empty($productos_rotacion_rapida)) {
            $alertas[] = [
                'tipo' => 'alerta',
                'titulo' => 'Rotación muy rápida',
                'descripcion' => count($productos_rotacion_rapida) . " productos se están vendiendo muy rápido",
                'productos_afectados' => array_slice(array_map(function($p) { return $p['nombre']; }, $productos_rotacion_rapida), 0, 3),
                'recomendacion' => 'Reabastecer pronto para evitar faltantes'
            ];
        }
    }

    // Alerta 3: Análisis de rentabilidad
    $sql_rentabilidad = "SELECT
                            (SUM(v.precio_total) - SUM(v.costo_total)) / SUM(v.precio_total) * 100 as margen_promedio
                         FROM ventas v
                         WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

    $result_rentabilidad = mysqli_query($conn, $sql_rentabilidad);
    if ($result_rentabilidad) {
        $row = mysqli_fetch_assoc($result_rentabilidad);
        $margen_promedio = $row['margen_promedio'] ?? 0;
        mysqli_free_result($result_rentabilidad);

        if ($margen_promedio < 10) {
            $alertas[] = [
                'tipo' => 'advertencia',
                'titulo' => 'Margen de ganancia bajo',
                'descripcion' => "Tu margen promedio es del " . round($margen_promedio, 2) . "%",
                'productos_afectados' => [],
                'recomendacion' => 'Considera revisar precios de venta o costos de compra'
            ];
        }
    }

    return $alertas;
}

// Nueva función para sugerencias de optimización
function obtenerSugerenciasOptimizacion() {
    global $conn;
    $sugerencias = [];

    // Sugerencia 1: Productos con bajo margen pero alta rotación
    $sql_bajo_margen = "SELECT p.nombre,
                               AVG((v.precio_unitario - (v.costo_total / v.cantidad_vendida))) as margen_unitario,
                               SUM(v.cantidad_vendida) as total_vendido
                        FROM productos p
                        JOIN ventas v ON p.id = v.producto_id
                        WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        GROUP BY p.id, p.nombre
                        HAVING margen_unitario > 0 AND margen_unitario < 1000
                        ORDER BY total_vendido DESC
                        LIMIT 5";

    $result_margen = mysqli_query($conn, $sql_bajo_margen);
    if ($result_margen) {
        $productos_bajo_margen = [];
        while ($row = mysqli_fetch_assoc($result_margen)) {
            $productos_bajo_margen[] = $row;
        }
        mysqli_free_result($result_margen);

        if (!empty($productos_bajo_margen)) {
            $sugerencias[] = [
                'tipo' => 'optimizacion',
                'titulo' => 'Oportunidad de precios',
                'descripcion' => 'Productos con buen volumen pero margen ajustable',
                'productos' => array_slice(array_map(function($p) {
                    return $p['nombre'] . " (Margen: $" . round($p['margen_unitario'], 2) . ")";
                }, $productos_bajo_margen), 0, 3),
                'accion' => 'Evaluar aumento de precios o negociación con proveedores'
            ];
        }
    }

    // Sugerencia 2: Análisis de estacionalidad
    $sql_estacionalidad = "SELECT
                              MONTH(fecha_venta) as mes,
                              SUM(precio_total) as ventas_mes
                           FROM ventas
                           WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                           GROUP BY MONTH(fecha_venta)
                           ORDER BY mes";

    $result_estacionalidad = mysqli_query($conn, $sql_estacionalidad);
    if ($result_estacionalidad) {
        $ventas_por_mes = [];
        while ($row = mysqli_fetch_assoc($result_estacionalidad)) {
            $ventas_por_mes[$row['mes']] = $row['ventas_mes'];
        }
        mysqli_free_result($result_estacionalidad);

        if (count($ventas_por_mes) >= 3) {
            $mes_actual = date('n');
            $ventas_actual = $ventas_por_mes[$mes_actual] ?? 0;
            $ventas_promedio = array_sum($ventas_por_mes) / count($ventas_por_mes);

            if ($ventas_actual < $ventas_promedio * 0.7) {
                $sugerencias[] = [
                    'tipo' => 'estacionalidad',
                    'titulo' => 'Baja temporada detectada',
                    'descripcion' => 'Las ventas de este mes están ' . round((1 - $ventas_actual/$ventas_promedio) * 100, 1) . '% por debajo del promedio',
                    'productos' => [],
                    'accion' => 'Considerar promociones o marketing adicional'
                ];
            }
        }
    }

    // Sugerencia 3: Optimización de stock
    $sql_stock_optimo = "SELECT p.nombre, p.stock, p.stock_minimo,
                                AVG(v.cantidad_vendida) as venta_promedio_diaria
                         FROM productos p
                         LEFT JOIN ventas v ON p.id = v.producto_id
                         WHERE p.deleted = 0
                         GROUP BY p.id, p.nombre, p.stock, p.stock_minimo
                         HAVING venta_promedio_diaria > 0";

    $result_stock = mysqli_query($conn, $sql_stock_optimo);
    if ($result_stock) {
        $productos_stock = [];
        while ($row = mysqli_fetch_assoc($result_stock)) {
            $productos_stock[] = $row;
        }
        mysqli_free_result($result_stock);

        $productos_sobre_stock = array_filter($productos_stock, function($p) {
            return $p['stock'] > ($p['venta_promedio_diaria'] * 60); // Más de 60 días de stock
        });

        if (!empty($productos_sobre_stock)) {
            $sugerencias[] = [
                'tipo' => 'inventario',
                'titulo' => 'Posible sobre-stock',
                'descripcion' => count($productos_sobre_stock) . ' productos tienen más de 60 días de stock',
                'productos' => array_slice(array_map(function($p) {
                    return $p['nombre'] . " (" . round($p['stock'] / $p['venta_promedio_diaria'], 0) . " días)";
                }, $productos_sobre_stock), 0, 3),
                'accion' => 'Evaluar promociones o reducción de compras'
            ];
        }
    }

    return $sugerencias;
}

// Nueva función para procesar venta rápida desde chatbot
function procesarVentaRapida($producto_nombre, $cantidad, $unidad_solicitada = null) {
    global $conn;

    // Buscar el producto
    $productos = buscarProductos($producto_nombre);
    if (empty($productos)) {
        return ['error' => 'Producto no encontrado', 'sugerencias' => 'Verifica el nombre del producto'];
    }

    if (count($productos) > 1) {
        $nombres = array_map(function($p) { return $p['nombre']; }, $productos);
        return ['error' => 'Múltiples productos encontrados', 'productos' => $nombres];
    }

    $producto = $productos[0];

    // Verificar stock disponible
    if ($producto['stock'] < $cantidad) {
        return ['error' => 'Stock insuficiente', 'stock_disponible' => $producto['stock']];
    }

    // Si se especifica unidad, convertir
    if ($unidad_solicitada !== null) {
        $unidades = getAllUnidades();
        $unidad_encontrada = null;

        foreach ($unidades as $unidad) {
            if (strtolower($unidad['nombre']) === strtolower($unidad_solicitada) ||
                strtolower($unidad['abreviatura']) === strtolower($unidad_solicitada)) {
                $unidad_encontrada = $unidad;
                break;
            }
        }

        if (!$unidad_encontrada) {
            return ['error' => 'Unidad de medida no encontrada'];
        }

        // Calcular conversión
        $factor_base = (float)$producto['unidad_id']; // Esto debería ser el factor de conversión base
        $factor_solicitado = (float)$unidad_encontrada['factor_conversion'];

        if ($factor_base == 0) {
            return ['error' => 'Error en configuración de unidades'];
        }

        $cantidad_base = ($cantidad * $factor_solicitado) / $factor_base;
        $precio_unitario = $producto['precio'] * ($factor_base / $factor_solicitado);
    } else {
        $cantidad_base = $cantidad;
        $precio_unitario = $producto['precio'];
    }

    // Calcular total
    $total_venta = $precio_unitario * $cantidad;
    $costo_total = $producto['costo'] * $cantidad_base;

    // Simular la venta (en un entorno real, esto debería usar transacciones)
    $conn->begin_transaction();

    try {
        // Insertar venta
        $sql_venta = "INSERT INTO ventas (producto_id, cantidad_vendida, unidad_vendida, precio_unitario, precio_total, costo_total, usuario_id, transaccion_id)
                      VALUES (?, ?, ?, ?, ?, ?, 1, ?)"; // usuario_id = 1 por defecto

        $transaccion_id = "CHAT-" . time() . "-" . rand(100, 999);
        $unidad_vendida = $unidad_solicitada ?: 'unidad';

        $stmt_venta = $conn->prepare($sql_venta);
        $stmt_venta->bind_param("iissddis", $producto['id'], $cantidad, $unidad_vendida, $precio_unitario, $total_venta, $costo_total, $transaccion_id);
        $stmt_venta->execute();
        $stmt_venta->close();

        // Actualizar stock
        $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
        $stmt_stock = $conn->prepare($sql_stock);
        $stmt_stock->bind_param("di", $cantidad_base, $producto['id']);
        $stmt_stock->execute();
        $stmt_stock->close();

        $conn->commit();

        return [
            'success' => true,
            'producto' => $producto['nombre'],
            'cantidad_vendida' => $cantidad,
            'unidad' => $unidad_vendida,
            'precio_unitario' => $precio_unitario,
            'total' => $total_venta,
            'transaccion_id' => $transaccion_id,
            'stock_restante' => $producto['stock'] - $cantidad_base
        ];

    } catch (Exception $e) {
        $conn->rollback();
        return ['error' => 'Error al procesar la venta: ' . $e->getMessage()];
    }
}

// Nueva función para procesar compra rápida desde chatbot
function procesarCompraRapida($producto_nombre, $cantidad, $costo_unitario, $proveedor_nombre) {
    global $conn;

    // Buscar el producto
    $productos = buscarProductos($producto_nombre);
    if (empty($productos)) {
        return ['error' => 'Producto no encontrado', 'sugerencias' => 'Verifica el nombre del producto'];
    }

    if (count($productos) > 1) {
        $nombres = array_map(function($p) { return $p['nombre']; }, $productos);
        return ['error' => 'Múltiples productos encontrados', 'productos' => $nombres];
    }

    $producto = $productos[0];

    // Buscar o crear proveedor
    $proveedores = getAllProveedores();
    $proveedor_encontrado = null;

    foreach ($proveedores as $prov) {
        if (strtolower($prov['nombre']) === strtolower($proveedor_nombre)) {
            $proveedor_encontrado = $prov;
            break;
        }
    }

    if (!$proveedor_encontrado) {
        // Crear proveedor si no existe
        $nuevo_proveedor_id = addProveedor($proveedor_nombre, '', '');
        if (!$nuevo_proveedor_id) {
            return ['error' => 'No se pudo crear el proveedor'];
        }
        $proveedor_encontrado = ['id' => $nuevo_proveedor_id, 'nombre' => $proveedor_nombre];
    }

    // Calcular total de compra
    $total_compra = $cantidad * $costo_unitario;

    // Simular la compra
    $conn->begin_transaction();

    try {
        // Insertar compra
        $sql_compra = "INSERT INTO compras (proveedor_id, total_compra) VALUES (?, ?)";
        $stmt_compra = $conn->prepare($sql_compra);
        $stmt_compra->bind_param("id", $proveedor_encontrado['id'], $total_compra);
        $stmt_compra->execute();
        $compra_id = $stmt_compra->insert_id;
        $stmt_compra->close();

        // Insertar detalle de compra
        $sql_detalle = "INSERT INTO compras_productos (compra_id, producto_id, cantidad, costo_unitario) VALUES (?, ?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->bind_param("iidd", $compra_id, $producto['id'], $cantidad, $costo_unitario);
        $stmt_detalle->execute();
        $stmt_detalle->close();

        // Actualizar stock y costo del producto
        $sql_stock = "UPDATE productos SET stock = stock + ?, costo = ? WHERE id = ?";
        $stmt_stock = $conn->prepare($sql_stock);
        $stmt_stock->bind_param("ddi", $cantidad, $costo_unitario, $producto['id']);
        $stmt_stock->execute();
        $stmt_stock->close();

        $conn->commit();

        return [
            'success' => true,
            'producto' => $producto['nombre'],
            'proveedor' => $proveedor_encontrado['nombre'],
            'cantidad_comprada' => $cantidad,
            'costo_unitario' => $costo_unitario,
            'total' => $total_compra,
            'compra_id' => $compra_id,
            'stock_actualizado' => $producto['stock'] + $cantidad
        ];

    } catch (Exception $e) {
        $conn->rollback();
        return ['error' => 'Error al procesar la compra: ' . $e->getMessage()];
    }
}



// Funciones de Autorización y Roles

/**
 * Verifica si el usuario actual tiene rol de administrador.
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

/**
 * Requiere que el usuario sea administrador. Si no lo es, redirige al index.
 */
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php?error=acceso_denegado");
        exit;
    }
}

?>