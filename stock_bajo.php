<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';
$productos_bajo_stock = getLowStockProducts();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos con Stock Bajo - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="theme.js"></script>
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
</head>
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200 min-h-screen">
    <div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

    <header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                <div><h1 class="text-2xl font-bold text-white dark:text-red-100 tracking-tight">Productos con Stock Bajo</h1><p class="text-sm text-cyan-100 dark:text-slate-300 mt-1">Productos que requieren reabastecimiento</p></div>
            </div>
            <div class="flex items-center space-x-3">
                <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                    <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
                <a href="index.php" class="flex items-center space-x-1 bg-slate-200 dark:bg-slate-600/90 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-300 dark:border-slate-400/20"><svg class="w-4 h-4 text-slate-700 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg><span class="text-sm text-slate-700 dark:text-slate-50">Volver</span></a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['success'])): echo "<div class='bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 p-4 mb-6 rounded-lg'><p class='font-bold'>Éxito</p><p>{$_SESSION['success']}</p></div>"; unset($_SESSION['success']); endif; ?>
        <?php if (isset($_SESSION['error'])): echo "<div class='bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300 p-4 mb-6 rounded-lg'><p class='font-bold'>Error</p><p>{$_SESSION['error']}</p></div>"; unset($_SESSION['error']); endif; ?>

        <section class="bg-white dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-white">Alerta de Stock</h2>
            </div>
            
            <?php if (empty($productos_bajo_stock)):
                echo '<div class="p-8 flex flex-col items-center justify-center text-center"><svg class="w-16 h-16 text-slate-400 dark:text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><p class="text-slate-700 dark:text-slate-400 font-medium">¡Todo en orden!</p><p class="text-slate-600 dark:text-slate-500 text-sm mt-2">No hay productos con niveles de stock bajos.</p></div>';
            else:
                echo '<div class="overflow-x-auto"><table class="w-full"><thead class="bg-slate-100 dark:bg-slate-900/50"><tr class="border-b border-slate-200 dark:border-slate-700"><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">ID</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Nombre</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Stock Actual</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Stock Mínimo</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Proveedor</th><th class="px-6 py-3 text-center text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Acciones</th></tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-700">';
                foreach ($productos_bajo_stock as $producto) {
                    echo '<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">';
                    echo '<td class="px-6 py-4 font-medium text-slate-700 dark:text-slate-100">' . htmlspecialchars($producto['id']) . '</td>';
                    echo '<td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">' . htmlspecialchars($producto['nombre']) . '</td>';
                    echo '<td class="px-6 py-4 font-bold text-red-600 dark:text-red-400">' . htmlspecialchars($producto['stock']) . '</td>';
                    echo '<td class="px-6 py-4 text-slate-600 dark:text-slate-400">' . htmlspecialchars($producto['stock_minimo']) . '</td>';
                    echo '<td class="px-6 py-4">';
                    if (!empty($producto['proveedor_id'])) {
                        echo '<a href="editar_proveedor.php?id=' . htmlspecialchars($producto['proveedor_id']) . '" class="text-cyan-600 dark:text-cyan-400 hover:underline">' . htmlspecialchars($producto['nombre_proveedor']) . '</a>';
                    } else { echo '<span class="text-slate-400 dark:text-slate-500">N/A</span>'; }
                    echo '</td><td class="px-6 py-4 text-center"><a href="registrar_compra.php?add_product_id=' . htmlspecialchars($producto['id']) . '" class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold py-2 px-4 rounded-lg transition-all shadow-md transform hover:-translate-y-0.5">Reabastecer</a></td></tr>';
                }
                echo '</tbody></table></div>';
            endif; ?>
        </section>
    </main>
<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>
