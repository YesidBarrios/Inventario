<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventario_supermercado";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // Registrar el error en el log del servidor
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
    // Mostrar un mensaje de error genérico al usuario
    // Podrías redirigir a una página de error dedicada aquí
    die("Ocurrió un error al conectar con la base de datos. Por favor, inténtalo de nuevo más tarde.");
}

function obtenerProductos() {
    global $conn;
    
    $sql = "SELECT * FROM productos WHERE deleted = 0 OR deleted IS NULL ORDER BY id ASC";
    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        error_log("Error al obtener productos: " . mysqli_error($conn));
        return []; // Retorna un array vacío en caso de error
    }

    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
    
    mysqli_free_result($result); // Liberar memoria del resultado
    return $productos;
}

function agregarProducto($nombre, $descripcion, $precio, $stock, $stock_minimo) {
    global $conn;
    error_log("DEBUG: Intentando agregar producto: " . $nombre); // Log de inicio

    $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, stock_minimo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para agregar producto: " . $conn->error);
        return false;
    }

    error_log("DEBUG: Sentencia preparada correctamente."); // Log de preparación exitosa

    $stmt->bind_param("ssdii", $nombre, $descripcion, $precio, $stock, $stock_minimo);

    error_log("DEBUG: Parámetros bindeados: nombre=" . $nombre . ", precio=" . $precio . ", stock=" . $stock . ", stock_minimo=" . $stock_minimo); // Log de parámetros

    if ($stmt->execute()) {
        error_log("DEBUG: Ejecución de la consulta exitosa."); // Log de ejecución exitosa
        $stmt->close();
        return true;
    } else {
        error_log("Error al ejecutar la consulta para agregar producto: " . $stmt->error); // Log de error de ejecución
        $stmt->close();
        return false;
    }
}

function obtenerProductoPorId($id) {
    global $conn;
    $sql = "SELECT * FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para obtener producto por ID: " . $conn->error);
        return null;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("Error al obtener el resultado para obtener producto por ID: " . $stmt->error);
            $stmt->close();
            return null;
        }
        $producto = $result->fetch_assoc();
        $stmt->close();
        return $producto;
    } else {
        error_log("Error al ejecutar la consulta para obtener producto por ID: " . $stmt->error);
        $stmt->close();
        return null;
    }
}

function actualizarProducto($id, $nombre, $descripcion, $precio, $stock, $stock_minimo) {
    global $conn;
    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, stock_minimo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para actualizar producto: " . $conn->error);
        return false;
    }

    $stmt->bind_param("ssdiis", $nombre, $descripcion, $precio, $stock, $stock_minimo, $id);

    if ($stmt->execute()) {
        return true;
    } else {
        error_log("Error al ejecutar la consulta para actualizar producto: " . $stmt->error);
        return false;
    }
    $stmt->close();
}

function eliminarProducto($id) {
    global $conn;
    
    // Usar borrado lógico en lugar de DELETE
    $sql = "UPDATE productos SET
            deleted = 1,
            deleted_at = CURRENT_TIMESTAMP
            WHERE id = ?";
            
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
        error_log("Error al preparar la consulta para recuperar producto: " . mysqli_error($conn));
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
        return []; // Retorna un array vacío en caso de error
    }

    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
    
    mysqli_free_result($result); // Liberar memoria del resultado
    return $productos;
}

function obtenerHistorialProductos() {
    global $conn;
    
    $sql = "SELECT * FROM productos_historial ORDER BY fecha_accion DESC";
    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        error_log("Error al obtener historial de productos: " . mysqli_error($conn));
        return []; // Retorna un array vacío en caso de error
    }

    $historial = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $historial[] = $row;
    }
    
    mysqli_free_result($result); // Liberar memoria del resultado
    return $historial;
}

function obtenerStockTotal() {
    global $conn;
    
    $sql = "SELECT SUM(stock) as total FROM productos";
    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        error_log("Error al obtener stock total: " . mysqli_error($conn));
        return 0; // Retorna 0 en caso de error
    }

    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_free_result($result); // Liberar memoria del resultado
        return $row['total'] ?? 0;
    }
    
    mysqli_free_result($result); // Liberar memoria del resultado
    return 0;
}

function obtenerConfiguracion() {
    global $conn;
    
    $sql = "SELECT * FROM configuracion LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        error_log("Error al obtener configuración: " . mysqli_error($conn));
        // Retorna valores por defecto en caso de error
        return [
            'nombre_tienda' => 'Mi Tienda',
            'direccion' => '',
            'telefono' => '',
            'email' => '',
            // Agrega aquí otros valores por defecto que necesites
        ];
    }

    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_free_result($result); // Liberar memoria del resultado
        return $row;
    }
    
    // Valores por defecto si no hay configuración en la tabla
    return [
        'nombre_tienda' => 'Mi Tienda',
        'direccion' => '',
        'telefono' => '',
        'email' => '',
        // Agrega aquí otros valores por defecto que necesites
    ];
}

function guardarConfiguracion($nombre_tienda, $direccion, $telefono, $email) {
    global $conn;
    $sql = "UPDATE configuracion SET 
            nombre_tienda = ?, 
            direccion = ?, 
            telefono = ?, 
            email = ? 
            WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre_tienda, $direccion, $telefono, $email);
    return $stmt->execute();
}

// Función para verificar las credenciales del usuario
function verificarCredenciales($username, $password) {
    global $conn;
    $sql = "SELECT id, username, password FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para verificar credenciales: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("Error al obtener el resultado para verificar credenciales: " . $stmt->error);
            $stmt->close();
            return false;
        }
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stmt->close();
            if (password_verify($password, $user['password'])) {
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

function obtenerEstadisticasStock() {
    global $conn;
    
    try {
        if (!$conn) {
            throw new Exception("Error de conexión a la base de datos");
        }

        $sql = "SELECT 
                COUNT(*) as total_productos,
                COALESCE(SUM(stock), 0) as stock_total,
                COALESCE(SUM(CASE WHEN stock <= stock_minimo THEN 1 ELSE 0 END), 0) as productos_bajo_minimo,
                COALESCE(SUM(stock * precio), 0) as valor_total_inventario
                FROM productos 
                WHERE deleted = 0 OR deleted IS NULL";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            throw new Exception("Error en la consulta: " . mysqli_error($conn));
        }

        return mysqli_fetch_assoc($result);

    } catch (Exception $e) {
        error_log("Error al obtener estadísticas de stock: " . $e->getMessage());
        return [
            'total_productos' => 0,
            'stock_total' => 0,
            'productos_bajo_minimo' => 0,
            'valor_total_inventario' => 0
        ];
    }
}
function buscarProductos($searchTerm) {
    global $conn;
    $searchTerm = "%" . $searchTerm . "%"; // Añadir comodines para búsqueda parcial

    $sql = "SELECT * FROM productos WHERE (nombre LIKE ? OR descripcion LIKE ?) AND (deleted = 0 OR deleted IS NULL) ORDER BY id ASC";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta para buscar productos: " . $conn->error);
        return []; // Retorna un array vacío en caso de error
    }

    $stmt->bind_param("ss", $searchTerm, $searchTerm);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("Error al obtener el resultado para buscar productos: " . $stmt->error);
            $stmt->close();
            return [];
        }
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

function registrarVenta($producto_id, $cantidad_vendida, $usuario_id = null) {
    global $conn;

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // 1. Obtener el producto para verificar stock y precio
        $producto = obtenerProductoPorId($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado.");
        }

        // 2. Verificar si hay suficiente stock
        if ($producto['stock'] < $cantidad_vendida) {
            throw new Exception("Stock insuficiente para la venta.");
        }

        $precio_unitario = $producto['precio'];
        $precio_total = $precio_unitario * $cantidad_vendida;

        // 3. Insertar en la tabla `ventas`
        $sql_venta = "INSERT INTO ventas (producto_id, cantidad_vendida, precio_unitario, precio_total, usuario_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_venta = $conn->prepare($sql_venta);
        if ($stmt_venta === false) {
            throw new Exception("Error al preparar la consulta de venta: " . $conn->error);
        }
        $stmt_venta->bind_param("iiddd", $producto_id, $cantidad_vendida, $precio_unitario, $precio_total, $usuario_id);
        if (!$stmt_venta->execute()) {
            throw new Exception("Error al registrar la venta: " . $stmt_venta->error);
        }
        $stmt_venta->close();

        // 4. Actualizar el stock en la tabla `productos`
        $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
        $stmt_stock = $conn->prepare($sql_stock);
        if ($stmt_stock === false) {
            throw new Exception("Error al preparar la consulta de actualización de stock: " . $conn->error);
        }
        $stmt_stock->bind_param("ii", $cantidad_vendida, $producto_id);
        if (!$stmt_stock->execute()) {
            throw new Exception("Error al actualizar el stock: " . $stmt_stock->error);
        }
        $stmt_stock->close();

        // Si todo fue exitoso, confirmar la transacción
        $conn->commit();
        return true;

    } catch (Exception $e) {
        // Si algo falló, revertir la transacción
        $conn->rollback();
        error_log("Error al registrar venta: " . $e->getMessage());
        return false;
    }
}

function obtenerHistorialVentas() {
    global $conn;

    $sql = "SELECT
                v.id,
                v.producto_id,
                p.nombre as nombre_producto,
                v.cantidad_vendida,
                v.precio_unitario,
                v.precio_total,
                v.fecha_venta,
                v.usuario_id
            FROM ventas v
            JOIN productos p ON v.producto_id = p.id
            ORDER BY v.fecha_venta DESC";

    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        error_log("Error al obtener historial de ventas: " . mysqli_error($conn));
        return []; // Retorna un array vacío en caso de error
    }

    $historial_ventas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $historial_ventas[] = $row;
    }

    mysqli_free_result($result); // Liberar memoria del resultado
    return $historial_ventas;
}

?>