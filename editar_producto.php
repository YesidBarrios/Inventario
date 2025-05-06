<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$error = '';
$producto = null;

if (isset($_GET['id'])) {
    $producto = obtenerProductoPorId($_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = trim($_POST['precio']);
    $stock = trim($_POST['stock']);
    $stock_minimo = trim($_POST['stock_minimo'] ?? '0'); // Usa el valor existente si no se proporciona uno nuevo

    // Validación básica
    if (!isset($id) || !is_numeric($id)) {
        $error = "ID de producto no válido.";
    } elseif (empty($nombre)) {
        $error = "El nombre del producto es obligatorio.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $error = "El precio debe ser un número positivo.";
    } elseif (filter_var($stock, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false) {
        $error = "El stock debe ser un número entero no negativo.";
    } elseif (filter_var($stock_minimo, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false) {
        $error = "El stock mínimo debe ser un número entero no negativo.";
    } else {
        // Convertir a tipos correctos después de la validación
        $id = (int)$id;
        $precio = (float)$precio;
        $stock = (int)$stock;
        $stock_minimo = (int)$stock_minimo;

        if (actualizarProducto($id, $nombre, $descripcion, $precio, $stock, $stock_minimo)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Error al actualizar el producto. Inténtalo de nuevo.";
            // Re-obtener el producto para mostrar el formulario con los datos actuales y el error
            $producto = obtenerProductoPorId($id);
        }
    }
} elseif (isset($_GET['id'])) {
    // Si es una solicitud GET y hay un ID, obtener el producto
    $producto = obtenerProductoPorId($_GET['id']);
    if (!$producto) {
        $error = "Producto no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- Encabezado con más contraste -->
        <div class="flex items-center justify-between mb-6 border-b border-gray-200 pb-4">
            <h1 class="text-2xl font-bold text-gray-800">Editar Producto <span class="text-gray-500 font-normal">#<?php echo $producto['id']; ?></span></h1>
            <a href="index.php" class="text-gray-600 hover:text-gray-800 transition-colors font-medium">
                ← Volver
            </a>
        </div>

        <?php if ($error): ?>
            <!-- Mensaje de error con más énfasis -->
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($producto): ?>
            <form action="editar_producto.php" method="POST" class="bg-white shadow-lg rounded-lg p-6 border border-gray-200">
                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                
                <!-- Campos con bordes más definidos -->
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                        <textarea name="descripcion" rows="3"
                                  class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                    </div>

                    <!-- Grid con mejor definición -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Precio (USD)</label>
                            <input type="number" step="0.01" name="precio" value="<?php echo $producto['precio']; ?>"
                                   class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   required>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Stock</label>
                            <input type="number" name="stock" value="<?php echo $producto['stock']; ?>"
                                   class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   required>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Mínimo</label>
                            <input type="number" name="stock_minimo" value="<?php echo $producto['stock_minimo']; ?>"
                                   class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Botones con más contraste -->
                <div class="mt-8 flex justify-end gap-4">
                    <a href="index.php" class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-700 text-white rounded-lg font-semibold hover:bg-blue-800 transition-colors shadow-sm">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="bg-white shadow-lg rounded-lg p-6 text-center border border-red-200">
                <p class="text-red-600 font-semibold">⚠️ Producto no encontrado</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>