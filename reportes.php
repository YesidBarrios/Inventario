<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';
$productos = obtenerProductos();
$estadisticas = obtenerEstadisticasStock();
$stockTotal = $estadisticas['stock_total'];
$total_productos = $estadisticas['total_productos'];
$productos_bajos = $estadisticas['productos_bajo_minimo'];
$valor_total_inventario = $estadisticas['valor_total_inventario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-50">
    <header class="bg-gradient-to-r from-slate-900 to-slate-800 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 sm:mb-0">
                <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h1 class="text-2xl font-bold text-cyan-100 tracking-tight">Reportes Analíticos</h1>
            </div>
            <a href="index.php" class="flex items-center space-x-1 bg-cyan-600/90 hover:bg-cyan-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-cyan-400/20">
                <svg class="w-4 h-4 text-cyan-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10M3 14h10m0-4H3m14 0h2m0 0v6m0-6v6m0-6h-2m2 6h-2"/>
                </svg>
                <span class="text-sm text-cyan-50">Panel Principal</span>
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Tarjeta de Métricas -->
        <section class="bg-white rounded-xl shadow-2xl border border-slate-200 mb-8">
            <div class="px-6 py-4 bg-slate-900 border-b">
                <h2 class="text-lg font-semibold text-cyan-100">Resumen General</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Stock Total -->
                <div class="bg-cyan-50 p-5 rounded-xl border border-cyan-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Stock Total</p>
                            <p class="text-2xl font-bold text-cyan-800"><?php echo number_format($stockTotal); ?></p>
                        </div>
                        <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <span class="text-xs text-slate-500">Unidades disponibles</span>
                </div>

                <!-- Total Productos -->
                <div class="bg-emerald-50 p-5 rounded-xl border border-emerald-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Productos</p>
                            <p class="text-2xl font-bold text-emerald-800"><?php echo $total_productos; ?></p>
                        </div>
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <span class="text-xs text-slate-500">Registrados</span>
                </div>

                <!-- Valor Inventario -->
                <div class="bg-violet-50 p-5 rounded-xl border border-violet-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Valor Total</p>
                            <p class="text-2xl font-bold text-violet-800">$<?php echo number_format($valor_total_inventario, 2); ?></p>
                        </div>
                        <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs text-slate-500">Valor en inventario</span>
                </div>

                <!-- Stock Bajo -->
                <div class="bg-rose-50 p-5 rounded-xl border border-rose-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Stock Bajo</p>
                            <p class="text-2xl font-bold text-rose-800"><?php echo $productos_bajos; ?></p>
                        </div>
                        <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <span class="text-xs text-slate-500">Productos críticos</span>
                </div>
            </div>
        </section>

        <!-- Gráfico Interactivo -->
<section class="bg-white rounded-xl shadow-2xl border border-slate-200">
    <div class="px-6 py-4 bg-slate-900 border-b">
        <h2 class="text-lg font-semibold text-cyan-100">Distribución de Stock</h2>
    </div>
    <div class="p-6 h-[600px] min-h-[400px]"> <!-- Altura aumentada -->
        <canvas id="inventarioChart" class="h-full w-full"></canvas>
    </div>
</section>

<script>
const ctx = document.getElementById('inventarioChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_map(function($p) { return $p['nombre']; }, $productos)); ?>,
        datasets: [{
            label: 'Stock Actual',
            data: <?php echo json_encode(array_map(function($p) { return $p['stock']; }, $productos)); ?>,
            backgroundColor: '#06b6d4',
            borderColor: '#0891b2',
            borderWidth: 1,
            borderRadius: 8,
            barPercentage: 0.6, // Barras más anchas
            categoryPercentage: 0.8 // Más espacio entre categorías
        }, {
            label: 'Stock Mínimo',
            data: <?php echo json_encode(array_map(function($p) { return $p['stock_minimo']; }, $productos)); ?>,
            backgroundColor: '#fb7185',
            borderColor: '#f43f5e',
            borderWidth: 1,
            borderRadius: 8,
            barPercentage: 0.6,
            categoryPercentage: 0.8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { 
                    color: '#475569',
                    font: { size: 14 } // Texto más grande
                }
            }
        },
        scales: {
            x: {
                grid: { color: '#e2e8f0' },
                ticks: { 
                    color: '#64748b',
                    font: { size: 12 }, // Tamaño de letra aumentado
                    autoSkip: false, // Muestra todos los labels
                    maxRotation: 45, // Rotación para labels largos
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#e2e8f0' },
                ticks: { 
                    color: '#64748b',
                    font: { size: 12 }, // Tamaño de letra aumentado
                    stepSize: 1 // Mostrar todos los números enteros
                }
            }
        }
    }
});
</script>
</body>
</html>