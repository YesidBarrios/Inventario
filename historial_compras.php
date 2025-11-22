<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';
requireAdmin();

$historial_compras = obtenerHistorialCompras();
$config = obtenerConfiguracion();
?>

<!DOCTYPE html>
<html lang="es" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - <?= htmlspecialchars($config['nombre_tienda']) ?></title>
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
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .dark .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

<header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Historial de Compras</h1>
                <p class="text-sm text-cyan-100 mt-1">Registro de todas las compras realizadas</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                <svg id="theme-icon-light" class="w-5 h-5 text-yellow-300 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <svg id="theme-icon-dark" class="w-5 h-5 text-slate-300 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>
            <a href="index.php" class="flex items-center space-x-1 bg-slate-200 dark:bg-slate-600 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-300 dark:border-slate-500">
                <svg class="w-4 h-4 text-slate-700 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-sm text-slate-700 dark:text-slate-50">Volver al Inicio</span>
            </a>
        </div>
    </div>
</header>

<main class="container mx-auto p-4 sm:p-6 lg:p-8">
    <section class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-white">Listado de Compras</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-100 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">ID Compra</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Proveedor</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Fecha</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Total</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-transparent divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($historial_compras)):
                        echo '<tr><td colspan="5" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">No hay compras registradas.</td></tr>';
                    else:
                        foreach ($historial_compras as $compra):
                            echo "<tr class='hover:bg-slate-50 dark:hover:bg-slate-700/30'>";
                            echo "<td class='px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-slate-200'>#" . htmlspecialchars($compra['id']) . "</td>";
                            echo "<td class='px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300'>" . htmlspecialchars($compra['nombre_proveedor'] ?? 'N/A') . "</td>";
                            echo "<td class='px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300'>" . date("d/m/Y H:i", strtotime($compra['fecha_compra'])) . "</td>";
                            echo "<td class='px-4 py-3 whitespace-nowrap text-sm text-slate-800 dark:text-slate-200 font-medium text-right'>$" . number_format($compra['total_compra'], 2) . "</td>";
                            echo "<td class='px-4 py-3 whitespace-nowrap text-sm text-center'><a href='recibo_compra.php?compra_id={$compra['id']}' class='text-cyan-600 dark:text-cyan-400 hover:text-cyan-800 dark:hover:text-cyan-300 font-semibold'>Ver Recibo</a></td>";
                            echo "</tr>";
                        endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeToggleButton = document.getElementById('theme-toggle-btn');
    const lightIcon = document.getElementById('theme-icon-light');
    const darkIcon = document.getElementById('theme-icon-dark');
    const bodyElement = document.body;
    const htmlElement = document.documentElement;

    function applyTheme(theme) {
        // Remover la clase 'dark' de Tailwind
        htmlElement.classList.remove('dark');
        bodyElement.classList.remove('gradient-bg', 'text-slate-200', 'bg-slate-100', 'text-slate-800');
        if(lightIcon) lightIcon.classList.add('hidden');
        if(darkIcon) darkIcon.classList.add('hidden');

        if (theme === 'dark') {
            // Agregar la clase 'dark' que Tailwind espera
            htmlElement.classList.add('dark');
            bodyElement.classList.add('gradient-bg', 'text-slate-200');
            if(darkIcon) darkIcon.classList.remove('hidden');
            localStorage.setItem('theme', 'dark');
        } else {
            // Modo claro (sin clase 'dark')
            bodyElement.classList.add('bg-slate-100', 'text-slate-800');
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
