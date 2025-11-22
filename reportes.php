<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';
requireAdmin();


$periodo = $_GET['periodo'] ?? 'mes_actual';
$fecha_inicio_custom = $_GET['fecha_inicio'] ?? '';
$fecha_fin_custom = $_GET['fecha_fin'] ?? '';
$hoy = new DateTime();

switch ($periodo) {
    case 'hoy': $fecha_inicio = $hoy->format('Y-m-d 00:00:00'); $fecha_fin = $hoy->format('Y-m-d 23:59:59'); break;
    case 'ayer': $ayer = (new DateTime())->modify('-1 day'); $fecha_inicio = $ayer->format('Y-m-d 00:00:00'); $fecha_fin = $ayer->format('Y-m-d 23:59:59'); break;
    case 'semana_actual': $fecha_inicio = (new DateTime())->modify('this week')->format('Y-m-d 00:00:00'); $fecha_fin = (new DateTime())->modify('this week +6 days')->format('Y-m-d 23:59:59'); break;
    case 'mes_pasado': $fecha_inicio = (new DateTime('first day of last month'))->format('Y-m-d 00:00:00'); $fecha_fin = (new DateTime('last day of last month'))->format('Y-m-d 23:59:59'); break;
    case 'custom': $fecha_inicio = !empty($fecha_inicio_custom) ? (new DateTime($fecha_inicio_custom))->format('Y-m-d 00:00:00') : ''; $fecha_fin = !empty($fecha_fin_custom) ? (new DateTime($fecha_fin_custom))->format('Y-m-d 23:59:59') : ''; break;
    default: $fecha_inicio = $hoy->format('Y-m-01 00:00:00'); $fecha_fin = $hoy->format('Y-m-t 23:59:59'); break;
}

$reporte_financiero = obtenerReporteFinanciero($fecha_inicio, $fecha_fin);
$ventas_periodo = obtenerVentasPorPeriodo($fecha_inicio, $fecha_fin);
$compras_periodo = obtenerComprasPorPeriodo($fecha_inicio, $fecha_fin);
$estadisticas_stock = obtenerEstadisticasStock();
?>

<!DOCTYPE html>
<html lang="es" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
             darkMode: 'class',
        }
    </script>
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .dark .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">

    <header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Reportes Analíticos</h1>
                    <p class="text-sm text-cyan-100 mt-1">Análisis de ventas, compras e inventario</p>
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
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <section class="bg-white dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-lg mb-8 p-6">
            <form action="reportes.php" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                <div class="flex-1">
                    <label for="periodo" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Periodo</label>
                    <select name="periodo" id="periodo" class="mt-1 block w-full p-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white">
                        <option value="mes_actual" <?= $periodo == 'mes_actual' ? 'selected' : '' ?>>Este Mes</option>
                        <option value="hoy" <?= $periodo == 'hoy' ? 'selected' : '' ?>>Hoy</option>
                        <option value="ayer" <?= $periodo == 'ayer' ? 'selected' : '' ?>>Ayer</option>
                        <option value="semana_actual" <?= $periodo == 'semana_actual' ? 'selected' : '' ?>>Esta Semana</option>
                        <option value="mes_pasado" <?= $periodo == 'mes_pasado' ? 'selected' : '' ?>>Mes Pasado</option>
                        <option value="custom" <?= $periodo == 'custom' ? 'selected' : '' ?>>Personalizado</option>
                    </select>
                </div>
                <div id="custom-dates" class="flex-1 grid grid-cols-2 gap-4 <?= $periodo == 'custom' ? '' : 'hidden' ?>">
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Desde</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio_custom) ?>" class="mt-1 block w-full p-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Hasta</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" value="<?= htmlspecialchars($fecha_fin_custom) ?>" class="mt-1 block w-full p-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="pt-5">
                    <button type="submit" class="bg-cyan-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-cyan-700 transition-colors">Generar</button>
                </div>
            </form>
        </section>

        <div class="dark:hidden"><div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-md border">
                <p class="text-sm text-slate-500 mb-1">Ingresos Brutos</p>
                <p class="text-3xl font-bold text-emerald-600">$<?= number_format($reporte_financiero['ingresos_brutos'], 2) ?></p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-md border">
                <p class="text-sm text-slate-500 mb-1">Total Gastos</p>
                <p class="text-3xl font-bold text-red-600">$<?= number_format($reporte_financiero['total_compras'], 2) ?></p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-md border">
                <p class="text-sm text-slate-500 mb-1">Ganancia Bruta</p>
                <p class="text-3xl font-bold text-sky-600">$<?= number_format($reporte_financiero['ganancia_bruta'], 2) ?></p>
            </div>
        </div></div>

        <div class="hidden dark:grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-emerald-500/10 p-5 rounded-xl border border-emerald-500/30">
                <p class="text-sm text-slate-300 mb-1">Ingresos Brutos</p>
                <p class="text-3xl font-bold text-emerald-300">$<?= number_format($reporte_financiero['ingresos_brutos'], 2) ?></p>
            </div>
            <div class="bg-red-500/10 p-5 rounded-xl border border-red-500/30">
                <p class="text-sm text-slate-300 mb-1">Total Gastos</p>
                <p class="text-3xl font-bold text-red-300">$<?= number_format($reporte_financiero['total_compras'], 2) ?></p>
            </div>
            <div class="bg-sky-500/10 p-5 rounded-xl border border-sky-500/30">
                <p class="text-sm text-slate-300 mb-1">Ganancia Bruta</p>
                <p class="text-3xl font-bold text-sky-300">$<?= number_format($reporte_financiero['ganancia_bruta'], 2) ?></p>
            </div>
        </div>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-lg">
                <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Ventas del Periodo</h3>
                    <a href="exportar_reporte.php?reporte=ventas_periodo&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>" class="text-sm bg-emerald-100 text-emerald-800 dark:bg-emerald-800/50 dark:text-emerald-200 font-semibold py-1 px-3 rounded-lg hover:bg-emerald-200 dark:hover:bg-emerald-800 transition-colors">Exportar</a>
                </div>
                <div class="overflow-y-auto max-h-96 p-6">
                    <ul class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php if(empty($ventas_periodo)): ?>
                            <li class="py-3 text-center text-slate-500">No hay ventas.</li>
                        <?php else: foreach($ventas_periodo as $venta): ?>
                            <li class="py-3">
                                <div class="flex justify-between">
                                    <span class="font-medium text-slate-800 dark:text-slate-200"><?= htmlspecialchars($venta['nombre_producto']) ?></span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">+$<?= number_format($venta['precio_total'], 2) ?></span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    <span><?= date("d/m/Y H:i", strtotime($venta['fecha_venta'])) ?></span>
                                    <a href="recibo.php?transaccion_id=<?= htmlspecialchars($venta['transaccion_id']) ?>" class="inline-flex items-center space-x-1 ml-2 px-2 py-1 bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 text-xs font-medium rounded-md hover:bg-cyan-100 dark:hover:bg-cyan-900/40 transition-colors border border-cyan-200 dark:border-cyan-800" title="Ver Recibo">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Ver Recibo #<?= htmlspecialchars($venta['transaccion_id']) ?></span>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; endif; ?>
                    </ul>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-lg">
                <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Compras del Periodo</h3>
                    <a href="exportar_reporte.php?reporte=compras_periodo&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>" class="text-sm bg-emerald-100 text-emerald-800 dark:bg-emerald-800/50 dark:text-emerald-200 font-semibold py-1 px-3 rounded-lg hover:bg-emerald-200 dark:hover:bg-emerald-800 transition-colors">Exportar</a>
                </div>
                <div class="overflow-y-auto max-h-96 p-6">
                    <ul class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php if(empty($compras_periodo)): ?>
                            <li class="py-3 text-center text-slate-500">No hay compras.</li>
                        <?php else: foreach($compras_periodo as $compra): ?>
                            <li class="py-3">
                                <div class="flex justify-between">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Compra a <?= htmlspecialchars($compra['nombre_proveedor'] ?? 'N/A') ?></span>
                                    <span class="font-bold text-red-600 dark:text-red-400">-$<?= number_format($compra['total_compra'], 2) ?></span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    <span><?= date("d/m/Y H:i", strtotime($compra['fecha_compra'])) ?></span>
                                    <a href="recibo_compra.php?compra_id=<?= $compra['id'] ?>" class="inline-flex items-center space-x-1 ml-2 px-2 py-1 bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 text-xs font-medium rounded-md hover:bg-cyan-100 dark:hover:bg-cyan-900/40 transition-colors border border-cyan-200 dark:border-cyan-800" title="Ver Recibo">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Ver Recibo #<?= $compra['id'] ?></span>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; endif; ?>
                    </ul>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.getElementById('periodo').addEventListener('change', function() {
            document.getElementById('custom-dates').classList.toggle('hidden', this.value !== 'custom');
        });

        const themeToggleButton = document.getElementById('theme-toggle-btn');
        const lightIcon = document.getElementById('theme-icon-light');
        const darkIcon = document.getElementById('theme-icon-dark');
        const htmlElement = document.documentElement;
        const bodyElement = document.body;

        function applyTheme(theme) {
            htmlElement.classList.remove('dark');
            bodyElement.classList.remove('gradient-bg', 'text-slate-200', 'bg-slate-100', 'text-slate-800');
            if(lightIcon) lightIcon.classList.add('hidden');
            if(darkIcon) darkIcon.classList.add('hidden');

            if (theme === 'dark') {
                htmlElement.classList.add('dark');
                bodyElement.classList.add('gradient-bg', 'text-slate-200');
                if(darkIcon) darkIcon.classList.remove('hidden');
                localStorage.setItem('theme', 'dark');
            } else {
                bodyElement.classList.add('bg-slate-100', 'text-slate-800');
                if(lightIcon) lightIcon.classList.remove('hidden');
                localStorage.setItem('theme', 'light');
            }
        }

        let preferredTheme = localStorage.getItem('theme') || 'dark';
        applyTheme(preferredTheme);

        if (themeToggleButton) {
            themeToggleButton.addEventListener('click', () => {
                let currentTheme = localStorage.getItem('theme') || 'dark';
                let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                applyTheme(newTheme);
            });
        }
    </script>
<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>