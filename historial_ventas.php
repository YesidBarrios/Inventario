<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$historial_ventas = obtenerHistorialVentas();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Added theme.js script -->
    <script src="theme.js"></script>
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
    </style>
</head>
<!-- Updated body classes for light/dark mode support -->
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200">
    <!-- Added gradient background that only shows in dark mode -->
    <div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

    <header class="bg-gradient-to-r from-slate-900 to-slate-800 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 sm:mb-0">
                <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h1 class="text-2xl font-bold text-cyan-100 tracking-tight">Historial de Transacciones</h1>
            </div>
            <div class="flex items-center space-x-2">
                <!-- Added theme toggle button -->
                <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors">
                    <svg id="theme-icon-light" class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg id="theme-icon-dark" class="w-5 h-5 text-slate-300 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <a href="index.php" class="flex items-center space-x-1 bg-slate-600/90 hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-400/20">
                    <svg class="w-4 h-4 text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="text-sm text-slate-50">Volver al Inicio</span>
                </a>
                <a href="registrar_venta.php" class="flex items-center space-x-1 bg-cyan-600/90 hover:bg-cyan-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-cyan-400/20">
                    <svg class="w-4 h-4 text-cyan-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-sm text-cyan-50">Nueva Venta</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Updated section background for light/dark mode -->
        <section class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-cyan-100">Registro Completo de Ventas</h2>
            </div>
            
            <?php if (!empty($historial_ventas)): ?>
                <div class="overflow-x-auto p-6">
                    <table class="w-full">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">ID</th>
                                <th class="px-6 py-3 text-left font-medium">Producto</th>
                                <th class="px-6 py-3 text-left font-medium">Cantidad</th>
                                <th class="px-6 py-3 text-left font-medium">P. Unitario</th>
                                <th class="px-6 py-3 text-left font-medium">Total</th>
                                <th class="px-6 py-3 text-left font-medium">Fecha</th>
                                <th class="px-6 py-3 text-left font-medium hidden md:table-cell">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                            <?php foreach ($historial_ventas as $venta): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100"><?php echo $venta['id']; ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($venta['nombre_producto']); ?></td>
                                    <td class="px-6 py-4"><?php echo $venta['cantidad_vendida']; ?></td>
                                    <td class="px-6 py-4 font-semibold text-emerald-700 dark:text-emerald-400">$<?php echo number_format($venta['precio_unitario'], 2); ?></td>
                                    <td class="px-6 py-4 font-semibold text-cyan-700 dark:text-cyan-400">$<?php echo number_format($venta['precio_total'], 2); ?></td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo $venta['fecha_venta']; ?></td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell"><?php echo $venta['nombre_usuario'] ?? '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-slate-600 dark:text-slate-400 font-medium">No se encontraron registros de ventas</p>
                    <p class="text-slate-500 dark:text-slate-500 text-sm mt-2">Todas las transacciones aparecerán aquí</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
