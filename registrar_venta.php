<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$error = '';
$success = '';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'agregar') {
        $producto_id = $_POST['producto_id'] ?? '';
        $cantidad = trim($_POST['cantidad_vendida'] ?? '');
        $unidad_id = $_POST['unidad_venta_id'] ?? '';

        if (empty($producto_id) || empty($cantidad) || empty($unidad_id) || !is_numeric($cantidad) || $cantidad <= 0) {
            $error = "Datos inválidos para agregar al carrito.";
        } else {
            $_SESSION['carrito'][] = [
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'unidad_id' => $unidad_id
            ];
            $success = "Producto agregado al carrito.";
        }
    } elseif ($action === 'eliminar') {
        $item_key = $_POST['item_key'] ?? '';
        if (isset($_SESSION['carrito'][$item_key])) {
            unset($_SESSION['carrito'][$item_key]);
            $success = "Producto eliminado del carrito.";
        }
    } elseif ($action === 'finalizar') {
        if (empty($_SESSION['carrito'])) {
            $error = "El carrito está vacío.";
        } else {
            $resultado = finalizarVentaCarrito($_SESSION['carrito'], $_SESSION['user_id']);
            if ($resultado['success']) {
                unset($_SESSION['carrito']);
                header("Location: recibo.php?transaccion_id=" . $resultado['transaccion_id']);
                exit;
            } else {
                $error = "Error: " . $resultado['error'];
            }
        }
    }
}

$productos_disponibles = obtenerProductos();
$config = obtenerConfiguracion();
?>

<!DOCTYPE html>
<html lang="es" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - <?= htmlspecialchars($config['nombre_tienda']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
             darkMode: 'class', // Habilitar modo oscuro basado en clase
             theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        slideIn: { '0%': { transform: 'translateX(-100%)' }, '100%': { transform: 'translateX(0)' } },
                    }
                }
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .dark .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
        
        /* Updated Select2 styles to support both light and dark modes */
        .select2-container--default .select2-selection--single { 
            background-color: rgba(51, 65, 85, 0.5) !important; 
            border: 1px solid rgb(71 85 105) !important; 
            height: 48px !important; 
            padding: 10px 0 !important; 
            border-radius: 0.5rem !important; 
        }
        html:not(.dark) .select2-container--default .select2-selection--single {
            background-color: white !important;
            border: 1px solid rgb(203 213 225) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { 
            color: white !important; 
            line-height: 28px !important; 
        }
        html:not(.dark) .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgb(30 41 59) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b { 
            border-color: #94a3b8 transparent transparent transparent !important; 
        }
        .select2-dropdown { 
            background-color: #1e293b !important; 
            border: 1px solid rgb(71 85 105) !important; 
            border-radius: 0.5rem !important; 
        }
        html:not(.dark) .select2-dropdown {
            background-color: white !important;
            border: 1px solid rgb(203 213 225) !important;
        }
        .select2-results__option { 
            color: #cbd5e1 !important; 
        }
        html:not(.dark) .select2-results__option {
            color: rgb(30 41 59) !important;
        }
        .select2-results__option--highlighted { 
            background-color: #3b82f6 !important; 
            color: white !important; 
        }
        .select2-search--dropdown .select2-search__field { 
            background-color: rgba(51, 65, 85, 0.8) !important; 
            border: 1px solid rgb(71 85 105) !important; 
            color: white !important; 
        }
        html:not(.dark) .select2-search--dropdown .select2-search__field {
            background-color: rgb(248 250 252) !important;
            border: 1px solid rgb(203 213 225) !important;
            color: rgb(30 41 59) !important;
        }
    </style>
</head>
<!-- Updated body classes to support dynamic theme switching -->
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <!-- Added gradient background that only shows in dark mode -->
    <div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

<header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shadow-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
            <div><h1 class="text-2xl font-bold text-white tracking-tight">Nueva Venta</h1><p class="text-sm text-cyan-100 mt-1">Registra una nueva transacción</p></div>
        </div>
        <div class="flex items-center space-x-4">
            <a href="index.php" class="flex items-center space-x-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-all duration-200 transform hover:scale-105"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg><span>Volver</span></a>
            <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
        </div>
    </div>
</header>

<main class="container mx-auto p-4 sm:p-6 lg:p-8">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-2">
            <!-- Updated card background for light/dark mode support -->
            <div class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm border border-slate-200 dark:border-slate-700 p-6 rounded-2xl shadow-2xl">
                <div class="flex items-center space-x-3 mb-4 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:bg-slate-900/70 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">1. Agregar Producto</h2>
                </div>
                <form action="registrar_venta.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="agregar">
                    <div>
                        <label for="producto_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Buscar Producto</label>
                        <select id="producto_id" name="producto_id" required class="mt-1 block w-full"><option value="">Seleccionar...</option><?php foreach ($productos_disponibles as $producto) echo "<option value=\"{$producto['id']}\">" . htmlspecialchars($producto['nombre']) . "</option>"; ?></select>
                    </div>
                    <div id="details-container" class="hidden space-y-4">
                        <div id="product-details" class="text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 p-3 rounded-md border border-slate-200 dark:border-slate-600"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cantidad</label>
                                <input type="number" name="cantidad_vendida" id="cantidad_vendida" min="0" step="any" required class="mt-1 block w-full p-3 bg-white dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Unidad</label>
                                <select name="unidad_venta_id" id="unidad_venta_id" required class="mt-1 block w-full p-3 bg-white dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white"></select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold py-3 px-4 rounded-xl hover:shadow-lg hover:shadow-cyan-500/30 transition-all transform hover:-translate-y-1 flex items-center justify-center space-x-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Añadir al Carrito</span></button>
                </form>
            </div>
        </div>
        <div class="lg:col-span-3">
            <!-- Updated card background for light/dark mode support -->
            <div class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm border border-slate-200 dark:border-slate-700 p-6 rounded-2xl shadow-2xl">
                <div class="flex items-center space-x-3 mb-4 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:bg-slate-900/70 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">2. Carrito de Venta</h2>
                </div>
                <?php if ($error) echo "<div class='my-3 bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-300 p-3 rounded-lg text-sm'>{$error}</div>"; ?>
                <?php if ($success) echo "<div class='my-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-300 p-3 rounded-lg text-sm'>{$success}</div>"; ?>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Producto</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Cantidad</th><th class="px-4 py-2 text-right text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Acción</th></tr>
                        </thead>
                        <tbody class="bg-transparent divide-y divide-slate-200 dark:divide-slate-700">
                            <?php if (empty($_SESSION['carrito'])): echo '<tr><td colspan="3" class="px-4 py-10 text-center text-slate-500"><svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg><p class="mt-2">El carrito está vacío</p></td></tr>';
                            else: foreach ($_SESSION['carrito'] as $key => $item):
                                $p = obtenerProductoPorId($item['producto_id']);
                                $u = getUnidadById($item['unidad_id']);
                                echo "<tr class='hover:bg-slate-50 dark:hover:bg-slate-700/30'><td class='px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-slate-100'>" . htmlspecialchars($p['nombre']) . "</td><td class='px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300'>" . htmlspecialchars($item['cantidad']) . ' ' . htmlspecialchars($u['abreviatura']) . "</td><td class='px-4 py-3 whitespace-nowrap text-sm text-right'><form action='registrar_venta.php' method='POST' class='inline'><input type='hidden' name='action' value='eliminar'><input type='hidden' name='item_key' value='{$key}'><button type='submit' class='text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-semibold'>Eliminar</button></form></td></tr>";
                            endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($_SESSION['carrito'])): echo '<form action="registrar_venta.php" method="POST" class="mt-6"><input type="hidden" name="action" value="finalizar"><button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-green-600 text-white font-bold py-4 px-4 rounded-xl hover:shadow-lg hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-1 flex items-center justify-center space-x-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Finalizar Venta y Generar Recibo</span></button></form>'; endif; ?>
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
    const unidadVentaSelect = document.getElementById('unidad_venta_id');

    $('#producto_id').select2({ placeholder: 'Escribe o selecciona un producto', width: '100%' });

    $('#producto_id').on('change', async function() {
        const productoId = this.value;
        if (!productoId) { detailsContainer.classList.add('hidden'); return; }

        try {
            const response = await fetch(`api.php?action=get_product_details&id=${productoId}`);
            const productoData = await response.json();
            if (productoData.error) throw new Error(productoData.error);

            detailsDiv.innerHTML = `Stock: <span class="font-bold text-slate-800 dark:text-slate-100">${productoData.stock} ${productoData.abreviatura_unidad}</span> | Precio: <span class="font-bold text-emerald-600 dark:text-emerald-400">${parseFloat(productoData.precio).toFixed(2)} / ${productoData.abreviatura_unidad}</span>`;
            
            unidadVentaSelect.innerHTML = '';
            productoData.unidades_compatibles.forEach(unidad => {
                const option = new Option(`${unidad.nombre} (${unidad.abreviatura})`, unidad.id);
                unidadVentaSelect.add(option);
            });
            unidadVentaSelect.value = productoData.unidad_id;

            detailsContainer.classList.remove('hidden');
        } catch (error) {
            console.error('Error fetching details:', error);
            detailsContainer.classList.add('hidden');
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const themeToggleButton = document.getElementById('theme-toggle-btn');
    const lightIcon = document.getElementById('theme-icon-light');
    const darkIcon = document.getElementById('theme-icon-dark');
    const htmlElement = document.documentElement;
    const bodyElement = document.body;

    function applyTheme(theme) {
        // Siempre empieza limpiando las clases para evitar conflictos
        htmlElement.classList.remove('dark');
        bodyElement.classList.remove('gradient-bg'); // Asegúrate de que el fondo degradado se quite en modo claro
        if(lightIcon) lightIcon.classList.add('hidden');
        if(darkIcon) darkIcon.classList.add('hidden');

        if (theme === 'dark') {
            htmlElement.classList.add('dark');
            bodyElement.classList.add('gradient-bg'); // Añade el fondo solo en modo oscuro
            if(darkIcon) darkIcon.classList.remove('hidden');
            localStorage.setItem('theme', 'dark');
        } else {
            // No se necesita clase específica para el modo claro en <html>, la ausencia de 'dark' es suficiente
            if(lightIcon) lightIcon.classList.remove('hidden');
            localStorage.setItem('theme', 'light');
        }
    }

    // Cargar tema guardado o usar 'light' por defecto
    let preferredTheme = localStorage.getItem('theme') || 'light';
    applyTheme(preferredTheme);

    // Toggle al hacer clic en el botón
    if (themeToggleButton) {
        themeToggleButton.addEventListener('click', () => {
            let currentTheme = localStorage.getItem('theme') || 'light';
            let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    }
});
</script>
<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>
