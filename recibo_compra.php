<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

if (!isset($_GET['compra_id'])) {
    header("Location: index.php");
    exit;
}

$compra_id = $_GET['compra_id'];
$compra = getCompraById($compra_id);
$productos_comprados = getCompraProductosByCompraId($compra_id);
$config = obtenerConfiguracion();

if (!$compra) {
    die("Compra no encontrada.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Compra #<?= htmlspecialchars($compra['id']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes gradient-shift { 
            0% { background-position: 0% 50%; } 
            50% { background-position: 100% 50%; } 
            100% { background-position: 0% 50%; } 
        }
        .gradient-bg { 
            background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); 
            background-size: 400% 400%; 
            animation: gradient-shift 15s ease infinite; 
        }
        @media print {
            body {
                background-color: #fff !important;
                background-image: none !important;
            }
            .no-print {
                display: none !important;
            }
            #recibo-wrapper {
                margin: 0;
                padding: 0;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
            }
            #recibo {
                width: 100%;
                position: absolute;
                left: 0;
                top: 0;
                color: black !important;
            }
            * {
                color: black !important;
                background: white !important;
            }
            table {
                border-color: #e2e8f0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200 min-h-screen">
    <!-- Added gradient background for dark mode -->
    <div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Added theme toggle and improved navigation buttons -->
            <div class="no-print my-6 flex justify-between items-center">
                <a href="historial_compras.php" class="flex items-center space-x-2 text-cyan-600 dark:text-cyan-400 hover:text-cyan-800 dark:hover:text-cyan-300 font-semibold transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Historial de Compras</span>
                </a>
                <div class="flex items-center space-x-3">
                    <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700/50 hover:bg-slate-300 dark:hover:bg-slate-600/50 transition-colors">
                        <svg id="theme-icon-light" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg id="theme-icon-dark" class="w-5 h-5 text-slate-300 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                    <a href="index.php" class="flex items-center space-x-2 bg-slate-600 hover:bg-slate-700 text-white py-2 px-4 rounded-lg font-semibold transition-all shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Inicio</span>
                    </a>
                    <button onclick="window.print()" class="flex items-center space-x-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white py-2 px-5 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-8a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Imprimir</span>
                    </button>
                </div>
            </div>

            <!-- Redesigned receipt with modern styling and theme support -->
            <div id="recibo-wrapper" class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-8 sm:p-10 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div id="recibo">
                    <!-- Header Section -->
                    <header class="text-center mb-8 pb-6 border-b-2 border-slate-200 dark:border-slate-700">
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl shadow-lg mb-3">
                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">
                            <?= htmlspecialchars($config['nombre_tienda']) ?>
                        </h1>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">
                            <?= htmlspecialchars($config['direccion']) ?>
                        </p>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">
                            Tel: <?= htmlspecialchars($config['telefono']) ?> | Email: <?= htmlspecialchars($config['email']) ?>
                        </p>
                    </header>

                    <!-- Receipt Type Badge -->
                    <div class="flex justify-center mb-6">
                        <div class="inline-flex items-center space-x-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 px-4 py-2 rounded-full border border-emerald-300 dark:border-emerald-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="font-bold text-sm">RECIBO DE COMPRA</span>
                        </div>
                    </div>

                    <!-- Receipt Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/30 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Información de Compra</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                                <span class="font-semibold">ID Compra:</span> #<?= htmlspecialchars($compra['id']) ?>
                            </p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                <span class="font-semibold">Fecha:</span> <?= date("d/m/Y H:i", strtotime($compra['fecha_compra'])) ?>
                            </p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/30 rounded-xl">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Proveedor</p>
                            <p class="text-lg font-bold text-slate-800 dark:text-slate-200">
                                <?= htmlspecialchars($compra['nombre_proveedor'] ?? 'N/A') ?>
                            </p>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4">Productos Comprados</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b-2 border-slate-300 dark:border-slate-600">
                                        <th class="text-left font-bold text-slate-700 dark:text-slate-300 py-3 px-2">PRODUCTO</th>
                                        <th class="text-center font-bold text-slate-700 dark:text-slate-300 py-3 px-2">CANTIDAD</th>
                                        <th class="text-right font-bold text-slate-700 dark:text-slate-300 py-3 px-2">COSTO UNIT.</th>
                                        <th class="text-right font-bold text-slate-700 dark:text-slate-300 py-3 px-2">SUBTOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_calculado = 0;
                                    foreach ($productos_comprados as $item):
                                        $subtotal = $item['cantidad'] * $item['costo_unitario'];
                                        $total_calculado += $subtotal;
                                    ?>
                                    <tr class="border-b border-slate-200 dark:border-slate-700">
                                        <td class="py-3 px-2 font-medium text-slate-800 dark:text-slate-200">
                                            <?= htmlspecialchars($item['nombre_producto']) ?>
                                        </td>
                                        <td class="text-center py-3 px-2 text-slate-600 dark:text-slate-400">
                                            <?= htmlspecialchars($item['cantidad']) ?>
                                        </td>
                                        <td class="text-right py-3 px-2 text-slate-600 dark:text-slate-400">
                                            $<?= number_format($item['costo_unitario'], 2) ?>
                                        </td>
                                        <td class="text-right py-3 px-2 font-semibold text-slate-800 dark:text-slate-200">
                                            $<?= number_format($subtotal, 2) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total Section -->
                    <div class="flex justify-end mb-8">
                        <div class="w-full sm:w-80">
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-slate-800 dark:to-slate-700 p-6 rounded-xl border-2 border-emerald-200 dark:border-emerald-800">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-slate-700 dark:text-slate-300 text-lg">TOTAL COMPRA:</span>
                                    <span class="font-bold text-slate-900 dark:text-white text-3xl">
                                        $<?= number_format($total_calculado, 2) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <footer class="text-center pt-6 mt-6 border-t border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Recibo generado por <?= htmlspecialchars($config['nombre_tienda']) ?>
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            ¡Gracias por su negocio!
                        </p>
                    </footer>
                </div>
            </div>
        </div>
    </div>

    <!-- Added theme toggle script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggleButton = document.getElementById('theme-toggle-btn');
        const lightIcon = document.getElementById('theme-icon-light');
        const darkIcon = document.getElementById('theme-icon-dark');
        const htmlElement = document.documentElement;

        function applyTheme(theme) {
            htmlElement.classList.remove('dark');
            if(lightIcon) lightIcon.classList.add('hidden');
            if(darkIcon) darkIcon.classList.add('hidden');

            if (theme === 'dark') {
                htmlElement.classList.add('dark');
                if(darkIcon) darkIcon.classList.remove('hidden');
                localStorage.setItem('theme', 'dark');
            } else {
                if(lightIcon) lightIcon.classList.remove('hidden');
                localStorage.setItem('theme', 'light');
            }
        }

        let preferredTheme = localStorage.getItem('theme') || 'light';
        applyTheme(preferredTheme);

        if (themeToggleButton) {
            themeToggleButton.addEventListener('click', () => {
                let currentTheme = localStorage.getItem('theme') || 'light';
                let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                applyTheme(newTheme);
            });
        }
    });
    </script>
</body>
</html>
