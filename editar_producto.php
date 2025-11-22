<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';
requireAdmin();

$error = '';
$producto = null;
$proveedores = getAllProveedores();
$unidades = getAllUnidades();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $producto = obtenerProductoPorId($_GET['id']);
    if (!$producto) {
        $_SESSION['error'] = "Producto no encontrado.";
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['error'] = "ID de producto no proporcionado.";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = trim($_POST['precio']);
    $costo = trim($_POST['costo']);
    $stock_minimo = trim($_POST['stock_minimo'] ?? '0');
    $proveedor_id = !empty($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
    $unidad_id = !empty($_POST['unidad_id']) ? (int)$_POST['unidad_id'] : null;

    if (empty($nombre)) {
        $error = "El nombre del producto es obligatorio.";
    } elseif (empty($unidad_id)) {
        $error = "La unidad de medida es obligatoria.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $error = "El precio debe ser un número positivo.";
    } elseif (!is_numeric($costo) || $costo < 0) {
        $error = "El costo debe ser un número positivo.";
    } elseif (!is_numeric($stock_minimo) || $stock_minimo < 0) {
        $error = "El stock mínimo debe ser un número no negativo.";
    } else {
        $id = (int)$id;
        $precio = (float)$precio;
        $costo = (float)$costo;
        $stock_minimo = (float)$stock_minimo;

        if (actualizarProducto($id, $nombre, $descripcion, $precio, $costo, $stock_minimo, $proveedor_id, $unidad_id)) {
            $_SESSION['success'] = "Producto actualizado exitosamente.";
            header("Location: index.php");
            exit;
        } else {
            $error = "Error al actualizar el producto. Es posible que el nombre ya exista.";
            $producto = obtenerProductoPorId($id);
        }
    }
}

if (!$producto) {
    $_SESSION['error'] = "No se pudo cargar la información del producto para editar.";
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Incluir el sistema de tema -->
    <script src="theme.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        /* Estilos del fondo oscuro y animación */
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
    </style>
</head>
<!-- Actualizado: bg claro por defecto, transparente en dark mode -->
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200 min-h-screen">
<!-- Fondo de gradiente solo visible en modo oscuro -->
<div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

<header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
    <div class="container mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Editar Producto</h1>
                <p class="text-sm text-cyan-100 mt-1">Modifica la información del producto</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Agregado: botón de cambio de tema -->
            <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
            <a href="index.php" class="flex items-center space-x-1 bg-slate-200 dark:bg-slate-600/90 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-300 dark:border-slate-400/20">
                <svg class="w-4 h-4 text-slate-700 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-sm text-slate-700 dark:text-slate-50">Volver al Inicio</span>
            </a>
        </div>
    </div>
</header>

<main class="container mx-auto px-6 py-8">
    <div class="max-w-4xl mx-auto">
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300 rounded-lg shadow-md">
                <p class="font-bold">Error de Validación</p>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form action="editar_producto.php?id=<?= htmlspecialchars($producto['id']) ?>" method="POST" class="space-y-8">
            <input type="hidden" name="id" value="<?= htmlspecialchars($producto['id']) ?>">
            
            <div class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <h2 class="text-xl font-bold text-white">Información del Producto</h2>
                    </div>
                    <div class="text-white text-sm">ID: <?= htmlspecialchars($producto['id']) ?></div>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required class="w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Unidad de Medida</label>
                            <select name="unidad_id" required class="w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                                <option value="" class="bg-white dark:bg-slate-800">Seleccionar...</option>
                                <?php foreach ($unidades as $unidad): ?>
                                    <option value="<?= htmlspecialchars($unidad['id']) ?>" <?= ($producto['unidad_id'] == $unidad['id']) ? 'selected' : '' ?> class="bg-white dark:bg-slate-800"><?= htmlspecialchars($unidad['nombre']) ?> (<?= htmlspecialchars($unidad['abreviatura']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white resize-none focus:ring-2 focus:ring-cyan-500"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700 flex items-center space-x-3">
                     <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                    <h2 class="text-xl font-bold text-white">Precio y Stock</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Costo de Compra</label>
                        <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-slate-500 dark:text-slate-400">$</span></div><input type="number" step="0.01" name="costo" value="<?= htmlspecialchars($producto['costo']) ?>" required class="w-full pl-7 p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Precio de Venta</label>
                        <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-slate-500 dark:text-slate-400">$</span></div><input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($producto['precio']) ?>" required class="w-full pl-7 p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500"></div>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Stock Mínimo</label>
                        <input type="number" step="any" name="stock_minimo" value="<?= htmlspecialchars($producto['stock_minimo']) ?>" required class="w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Stock Actual (Solo lectura)</label>
                        <input type="text" value="<?= htmlspecialchars($producto['stock']) ?>" class="w-full p-3 bg-slate-200 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-500 dark:text-slate-400 cursor-not-allowed" readonly>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700 flex items-center space-x-3">
                      <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                    <h2 class="text-xl font-bold text-white">Proveedor</h2>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Proveedor Asignado</label>
                    <select name="proveedor_id" class="w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                        <option value="" class="bg-white dark:bg-slate-800">Sin proveedor</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?= htmlspecialchars($proveedor['id']) ?>" <?= ($producto['proveedor_id'] == $proveedor['id']) ? 'selected' : '' ?> class="bg-white dark:bg-slate-800"><?= htmlspecialchars($proveedor['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg hover:shadow-cyan-500/30 transition-all transform hover:-translate-y-1 flex items-center justify-center space-x-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Guardar Cambios</span></button>
                <a href="index.php" class="flex-1 text-center bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white font-semibold py-3 px-6 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-all transform hover:-translate-y-1 shadow-lg border border-slate-300 dark:border-slate-600 flex items-center justify-center space-x-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>Cancelar</span></a>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>
