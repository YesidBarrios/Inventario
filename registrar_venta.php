<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$error = '';
$success = '';
$productos_disponibles = obtenerProductos(); // Obtener productos no eliminados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = $_POST['producto_id'] ?? '';
    $cantidad_vendida = trim($_POST['cantidad_vendida'] ?? '');
    $usuario_id = $_SESSION['user_id'] ?? null; // Obtener el ID del usuario logueado

    // Validación de entrada
    if (empty($producto_id) || !is_numeric($producto_id)) {
        $error = "Debe seleccionar un producto válido.";
    } elseif (filter_var($cantidad_vendida, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
        $error = "La cantidad vendida debe ser un número entero positivo.";
    } else {
        $producto_id = (int)$producto_id;
        $cantidad_vendida = (int)$cantidad_vendida;

        // Verificar si el producto existe y no está eliminado antes de intentar registrar la venta
        $producto_a_vender = obtenerProductoPorId($producto_id);
        if (!$producto_a_vender || $producto_a_vender['deleted'] == 1) {
             $error = "El producto seleccionado no es válido o no está disponible.";
        } elseif ($producto_a_vender['stock'] < $cantidad_vendida) {
            $error = "Stock insuficiente. Cantidad disponible: " . $producto_a_vender['stock'];
        }
        else {
            // Intentar registrar la venta
            if (registrarVenta($producto_id, $cantidad_vendida, $usuario_id)) {
                $success = "Venta registrada exitosamente y stock actualizado.";
                // Opcional: redirigir a otra página o limpiar el formulario
                // header("Location: index.php");
                // exit;
                 // Recargar la lista de productos disponibles después de una venta exitosa
                $productos_disponibles = obtenerProductos();
            } else {
                $error = "Error al registrar la venta. Inténtalo de nuevo.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ventas - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-50">
    <div class="container mx-auto p-4 min-h-screen flex items-center justify-center">
        <div class="w-full max-w-2xl">
            <!-- Tarjeta del formulario -->
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-slate-200">
                <!-- Encabezado -->
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-6">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h1 class="text-2xl font-bold text-cyan-100 tracking-tight">Registro de Ventas</h1>
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

                    <?php if ($success): ?>
                        <div class="mb-6 flex items-center bg-emerald-100 text-emerald-800 px-4 py-3 rounded-lg border border-emerald-200">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium"><?php echo $success; ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="registrar_venta.php" method="POST" class="space-y-6">
                        <!-- Selección de Producto -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Producto</label>
                            <div class="relative">
                                <select id="producto_id" name="producto_id" required
                                    class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all appearance-none">
                                    <option value="">Selecciona un producto</option>
                                    <?php foreach ($productos_disponibles as $producto): ?>
                                        <option value="<?php echo $producto['id']; ?>" data-price="<?php echo $producto['precio']; ?>">
                                            <?php echo htmlspecialchars($producto['nombre']) . ' - Stock: ' . $producto['stock']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Cantidad Vendida -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Cantidad</label>
                            <div class="relative">
                                <input type="number" id="cantidad_vendida" name="cantidad_vendida" min="1" required
                                    class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all"
                                    placeholder="Ej: 5 unidades">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Precio Estimado -->
                        <div class="bg-cyan-50 p-4 rounded-lg border border-cyan-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-600">Total Estimado</p>
                                    <p id="precio_total_estimado" class="text-2xl font-bold text-cyan-800">$0.00</p>
                                </div>
                                <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <button type="submit" 
                                class="w-full sm:w-auto bg-gradient-to-r from-emerald-600 to-emerald-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-emerald-700 hover:to-emerald-600 transition-all shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Confirmar Venta
                            </button>
                            <a href="historial_ventas.php" 
                                class="w-full sm:w-auto bg-gradient-to-r from-slate-600 to-slate-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-slate-700 hover:to-slate-600 transition-all shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                Historial
                            </a>
                            <a href="index.php" 
                                class="w-full sm:w-auto bg-gradient-to-r from-rose-600 to-rose-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-rose-700 hover:to-rose-600 transition-all shadow-lg flex items-center justify-center">
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

    <script>
        const productoSelect = document.getElementById('producto_id');
        const cantidadInput = document.getElementById('cantidad_vendida');
        const precioTotalSpan = document.getElementById('precio_total_estimado');

        function calcularPrecioTotal() {
            const selectedOption = productoSelect.options[productoSelect.selectedIndex];
            const precioUnitario = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
            const cantidad = parseInt(cantidadInput.value) || 0;
            const precioTotal = precioUnitario * cantidad;

            precioTotalSpan.textContent = `$${precioTotal.toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        }

        productoSelect.addEventListener('change', calcularPrecioTotal);
        cantidadInput.addEventListener('input', calcularPrecioTotal);
        calcularPrecioTotal();
    </script>
</body>
</html>