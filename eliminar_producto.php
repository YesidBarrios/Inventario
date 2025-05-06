<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';

// Verificar token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Error de seguridad. Intente nuevamente.";
        header('Location: index.php');
        exit;
    }

    if (!isset($_POST['id'])) {
        $_SESSION['error'] = "Producto no especificado";
        header('Location: index.php');
        exit;
    }

    $id = (int)$_POST['id'];
    
    if (eliminarProducto($id)) {
        $_SESSION['success'] = "Producto eliminado correctamente";
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = "Error al eliminar el producto";
        header("Location: ver_producto.php?id=$id");
        exit;
    }
}

// Si es GET, mostrar página de confirmación
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$producto = obtenerProductoPorId($_GET['id']);
if (!$producto) {
    $_SESSION['error'] = "Producto no encontrado";
    header('Location: index.php');
    exit;
}

// Generar token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Eliminación</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="flex items-center justify-between mb-6 border-b border-gray-200 pb-4">
            <h1 class="text-2xl font-bold text-gray-800">Confirmar Eliminación</h1>
            <a href="index.php" class="text-gray-600 hover:text-gray-800 transition-colors font-medium">
                ← Volver
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6 border border-red-200">
            <div class="text-center mb-6">
                <!-- Icono de advertencia -->
                <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                
                <h2 class="text-xl font-semibold text-gray-800 mb-2">¿Estás seguro de eliminar este producto?</h2>
                <p class="text-gray-600 mb-4"><span class="font-medium">ID:</span> <?= $producto['id'] ?> | <span class="font-medium">Nombre:</span> <?= htmlspecialchars($producto['nombre']) ?></p>
                
                <form action="eliminar_producto.php" method="POST" class="flex justify-center gap-4">
                    <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <button type="button" onclick="window.location.href='index.php'" 
                            class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        Cancelar
                    </button>
                    
                    <button type="submit" 
                            class="px-5 py-2.5 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors shadow-sm">
                        Confirmar Eliminación
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>