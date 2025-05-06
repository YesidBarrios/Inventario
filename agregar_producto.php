<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = trim($_POST['precio']);
    $stock = trim($_POST['stock']);
    $stock_minimo = trim($_POST['stock_minimo'] ?? '0'); // Valor predeterminado si no se proporciona

    // Validación básica
    if (empty($nombre)) {
        $error = "El nombre del producto es obligatorio.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $error = "El precio debe ser un número positivo.";
    } elseif (filter_var($stock, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false) {
        $error = "El stock debe ser un número entero no negativo.";
    } elseif (filter_var($stock_minimo, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false) {
        $error = "El stock mínimo debe ser un número entero no negativo.";
    } else {
        // Convertir a tipos correctos después de la validación
        $precio = (float)$precio;
        $stock = (int)$stock;
        $stock_minimo = (int)$stock_minimo;

        if (agregarProducto($nombre, $descripcion, $precio, $stock, $stock_minimo)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Error al agregar el producto. Inténtalo de nuevo.";
            // Añadir exit para detener la ejecución y mostrar el error
            // exit; // Comentado temporalmente para no detener la ejecución si hay otros problemas
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-50">
    <div class="container mx-auto p-4 min-h-screen flex items-center justify-center">
        <div class="w-full max-w-2xl">
            <!-- Tarjeta del formulario -->
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Encabezado -->
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-6">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <h1 class="text-2xl font-bold text-cyan-100 tracking-tight">Registro de Producto</h1>
                    </div>
                </div>

                <!-- Cuerpo del formulario -->
                <div class="p-8">
                    <?php if ($error): ?>
                        <div class="mb-6 flex items-center bg-rose-100 text-rose-800 px-4 py-3 rounded-lg border border-rose-200">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium"><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="agregar_producto.php" method="POST" class="space-y-6">
                        <!-- Campo Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nombre del Producto</label>
                            <div class="relative">
                                <input type="text" id="nombre" name="nombre" required
                                    class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all"
                                    placeholder="Ej: Arroz, Pollo">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Campo Descripción -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Descripción</label>
                            <div class="relative">
                                <textarea id="descripcion" name="descripcion"
                                    class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all h-32"
                                    placeholder="Detalles del producto"></textarea>
                                <div class="absolute top-3 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Campos en fila -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Precio -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Precio Unitario</label>
                                <div class="relative">
                                    <input type="number" step="0.01" id="precio" name="precio" required
                                        class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all"
                                        placeholder="0.00">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400">$</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Stock Inicial</label>
                                <div class="relative">
                                    <input type="number" id="stock" name="stock" required
                                        class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all"
                                        placeholder="Cantidad disponible">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Mínimo -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Stock Mínimo</label>
                                <div class="relative">
                                    <input type="number" id="stock_minimo" name="stock_minimo" required
                                        class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all"
                                        placeholder="Nivel mínimo de stock">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <button type="submit" 
                                class="w-full sm:w-auto bg-gradient-to-r from-emerald-600 to-emerald-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-emerald-700 hover:to-emerald-600 transition-all shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Registrar Producto
                            </button>
                            <a href="index.php" 
                                class="w-full sm:w-auto bg-gradient-to-r from-slate-600 to-slate-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-slate-700 hover:to-slate-600 transition-all shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>