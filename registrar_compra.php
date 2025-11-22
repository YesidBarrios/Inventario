<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';
requireAdmin();

// Auto-add product from stock_bajo.php
if (isset($_GET['add_product_id']) && is_numeric($_GET['add_product_id'])) {
    $producto_a_anadir_id = (int)$_GET['add_product_id'];

    if (!isset($_SESSION['compra_carrito'])) {
        $_SESSION['compra_carrito'] = [];
    }

    $producto_en_carrito = false;
    foreach ($_SESSION['compra_carrito'] as $item) {
        if ($item['producto_id'] == $producto_a_anadir_id) {
            $producto_en_carrito = true;
            break;
        }
    }

    if (!$producto_en_carrito) {
        $producto = obtenerProductoPorId($producto_a_anadir_id);
        if ($producto) {
            $_SESSION['compra_carrito'][] = [
                'producto_id' => $producto['id'],
                'cantidad' => 1,
                'costo' => $producto['costo'],
                'unidad_id' => $producto['unidad_id']
            ];
            if (!empty($producto['proveedor_id'])) {
                $_SESSION['preselect_proveedor_id'] = $producto['proveedor_id'];
            }
        }
    }
    header("Location: registrar_compra.php");
    exit;
}

$error = '';
$success = '';

if (!isset($_SESSION['compra_carrito'])) {
    $_SESSION['compra_carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'agregar') {
        $producto_id = $_POST['producto_id'] ?? '';
        $cantidad = trim($_POST['cantidad'] ?? '');
        $costo = trim($_POST['costo'] ?? '');
        $unidad_id = $_POST['unidad_id'] ?? '';

        if (empty($producto_id) || empty($cantidad) || empty($costo) || empty($unidad_id) || !is_numeric($cantidad) || !is_numeric($costo) || $cantidad <= 0 || $costo < 0) {
            $error = "Datos inválidos para agregar al carrito de compras.";
        } else {
            $_SESSION['compra_carrito'][] = [
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'costo' => $costo,
                'unidad_id' => $unidad_id
            ];
            $success = "Producto agregado al carrito de compras.";
        }
    } elseif ($action === 'eliminar') {
        $item_key = $_POST['item_key'] ?? '';
        if (isset($_SESSION['compra_carrito'][$item_key])) {
            unset($_SESSION['compra_carrito'][$item_key]);
            $success = "Producto eliminado del carrito de compras.";
        }
    } elseif ($action === 'finalizar') {
        $proveedor_id = $_POST['proveedor_id'] ?? '';
        if (empty($_SESSION['compra_carrito'])) {
            $error = "El carrito de compras está vacío.";
        } elseif (empty($proveedor_id)) {
            $error = "Debe seleccionar un proveedor.";
        } else {
            $resultado = finalizarCompraCarrito($_SESSION['compra_carrito'], $proveedor_id, $_SESSION['user_id']);
            if ($resultado['success']) {
                unset($_SESSION['compra_carrito']);
                header("Location: recibo_compra.php?compra_id=" . $resultado['compra_id']);
                exit;
            } else {
                $error = "Error: " . $resultado['error'];
            }
        }
    }
}

$productos_disponibles = obtenerProductos();
$proveedores = getAllProveedores();
$config = obtenerConfiguracion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Compra - <?= htmlspecialchars($config['nombre_tienda']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Agregado sistema de tema igual que en los otros archivos -->
    <script src="theme.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos del fondo oscuro y animación */
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
        
        /* Estilos Select2 adaptados para modo claro y oscuro */
        /* Modo oscuro */
        .dark .select2-container--default .select2-selection--single { background-color: rgba(51, 65, 85, 0.5) !important; border: 1px solid rgb(71 85 105) !important; height: 48px !important; padding: 10px 0 !important; border-radius: 0.5rem !important; color: white !important; }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered { color: white !important; line-height: 28px !important; }
        .dark .select2-container--default .select2-selection--single .select2-selection__arrow b { border-color: #94a3b8 transparent transparent transparent !important; }
        .dark .select2-dropdown { background-color: #1e293b !important; border: 1px solid rgb(71 85 105) !important; border-radius: 0.5rem !important; color: white !important; }
        .dark .select2-results__option { color: #cbd5e1 !important; }
        .dark .select2-results__option--highlighted { background-color: #3b82f6 !important; color: white !important; }
        .dark .select2-search--dropdown .select2-search__field { background-color: rgba(51, 65, 85, 0.8) !important; border: 1px solid rgb(71 85 105) !important; color: white !important; }
        
        /* Modo claro */
        .select2-container--default .select2-selection--single { background-color: #f8fafc !important; border: 1px solid #cbd5e1 !important; height: 48px !important; padding: 10px 0 !important; border-radius: 0.5rem !important; color: #1e293b !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: #1e293b !important; line-height: 28px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow b { border-color: #64748b transparent transparent transparent !important; }
        .select2-dropdown { background-color: white !important; border: 1px solid #cbd5e1 !important; border-radius: 0.5rem !important; color: #1e293b !important; }
        .select2-results__option { color: #475569 !important; }
        .select2-results__option--highlighted { background-color: #3b82f6 !important; color: white !important; }
        .select2-search--dropdown .select2-search__field { background-color: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #1e293b !important; }
    </style>
</head>
<!-- Actualizado body para soportar modo claro y oscuro igual que los otros archivos -->
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200 min-h-screen">
<!-- Fondo de gradiente solo visible en modo oscuro -->
<div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

<header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Registrar Compra</h1>
                <p class="text-sm text-cyan-100 mt-1">Añade productos comprados a proveedores</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="flex items-center space-x-1 bg-white/10 hover:bg-white/20 text-white rounded-xl px-4 py-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="text-sm">Volver</span>
                </a>
                <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                    <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
            </div>
        </div>
    </header>
            </div>
        </div>
    </div>
</header>

<main class="container mx-auto p-4 sm:p-6 lg:p-8">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        
        <div class="lg:col-span-2">
            <!-- Actualizado con estilos para modo claro -->
            <div class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 p-6 rounded-2xl shadow-2xl">
                <div class="flex items-center space-x-3 mb-4 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:bg-slate-900/70 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">1. Agregar Producto</h2>
                </div>
                <form action="registrar_compra.php" method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="agregar">
                    <div>
                        <label for="producto_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Buscar Producto</label>
                        <select id="producto_id" name="producto_id" required class="mt-1 block w-full text-slate-900 dark:text-white">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($productos_disponibles as $producto): ?>
                                <option value="<?= htmlspecialchars($producto['id']) ?>" class="bg-white dark:bg-slate-800"><?= htmlspecialchars($producto['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="details-container" class="hidden space-y-4">
                        <div id="product-details" class="text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 p-3 rounded-md border border-slate-200 dark:border-slate-600"></div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cantidad</label>
                                <input type="number" name="cantidad" min="0" step="any" required class="mt-1 block w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Unidad</label>
                                <select name="unidad_id" id="unidad_id" required class="mt-1 block w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500"></select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Costo Unit.</label>
                                <input type="number" name="costo" min="0" step="0.01" required class="mt-1 block w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold py-3 px-4 rounded-xl hover:shadow-lg hover:shadow-cyan-500/30 transition-all transform hover:-translate-y-1">Añadir al Carrito</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-3">
            <!-- Actualizado con estilos para modo claro -->
            <div class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 p-6 rounded-2xl shadow-2xl">
                <div class="flex items-center space-x-3 mb-4 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:bg-slate-900/70 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg></div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">2. Carrito de Compra</h2>
                </div>
                <?php if ($error) echo "<div class='my-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300 p-3 rounded-lg text-sm'>{$error}</div>"; ?>
                <?php if ($success) echo "<div class='my-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 p-3 rounded-lg text-sm'>{$success}</div>"; ?>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-100 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Producto</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Cantidad</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Costo Unit.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-transparent divide-y divide-slate-200 dark:divide-slate-700">
                            <?php if (empty($_SESSION['compra_carrito'])): ?>
                                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500 dark:text-slate-500">El carrito está vacío</td></tr>
                            <?php else: ?>
                                <?php foreach ($_SESSION['compra_carrito'] as $key => $item):
                                    $p = obtenerProductoPorId($item['producto_id']);
                                    $u = getUnidadById($item['unidad_id']);
                                ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100"><?= htmlspecialchars($p['nombre']) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300"><?= htmlspecialchars($item['cantidad']) ?> <?= htmlspecialchars($u['abreviatura']) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">$<?= number_format($item['costo'], 2) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                            <form action="registrar_compra.php" method="POST" class="inline">
                                                <input type="hidden" name="action" value="eliminar">
                                                <input type="hidden" name="item_key" value="<?= $key ?>">
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-semibold">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($_SESSION['compra_carrito'])): ?>
                    <form action="registrar_compra.php" method="POST" class="mt-6 space-y-4">
                        <input type="hidden" name="action" value="finalizar">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Proveedor</label>
                            <select name="proveedor_id" required class="mt-1 block w-full p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                                <option value="" class="bg-white dark:bg-slate-800">Seleccionar...</option>
                                <?php 
                                $selected_proveedor_id = $_SESSION['preselect_proveedor_id'] ?? null;
                                foreach ($proveedores as $proveedor): 
                                    $selected = ($proveedor['id'] == $selected_proveedor_id) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($proveedor['id']) ?>" <?= $selected ?> class="bg-white dark:bg-slate-800"><?= htmlspecialchars($proveedor['nombre']) ?></option>
                                <?php 
                                endforeach;
                                unset($_SESSION['preselect_proveedor_id']);
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-green-600 text-white font-bold py-4 px-4 rounded-xl hover:shadow-lg hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-1">Finalizar Compra</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailsContainer = document.getElementById('details-container');
    const detailsDiv = document.getElementById('product-details');
    const unidadSelect = document.getElementById('unidad_id');

    $('#producto_id').select2({
        placeholder: 'Escribe o selecciona un producto',
        width: '100%'
    });

    $('#producto_id').on('change', async function() {
        const productoId = this.value;
        if (!productoId) {
            detailsContainer.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`api.php?action=get_product_details&id=${productoId}`);
            const productoData = await response.json();
            if (productoData.error) throw new Error(productoData.error);

            detailsDiv.innerHTML = `Stock actual: <span class="font-bold text-slate-900 dark:text-slate-100">${productoData.stock} ${productoData.abreviatura_unidad}</span> | Costo ref: $${parseFloat(productoData.costo).toFixed(2)}`;
            
            unidadSelect.innerHTML = ''; // Limpiar opciones anteriores
            productoData.unidades_compatibles.forEach(unidad => {
                const option = new Option(`${unidad.nombre} (${unidad.abreviatura})`, unidad.id);
                unidadSelect.add(option);
            });
            unidadSelect.value = productoData.unidad_id; // Pre-seleccionar la unidad base

            detailsContainer.classList.remove('hidden');
        } catch (error) {
            console.error('Error fetching product details:', error);
            detailsContainer.classList.add('hidden');
        }
    });
});
</script>
<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>
