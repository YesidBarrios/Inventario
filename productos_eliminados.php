<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';
requireAdmin();

$productosEliminados = obtenerProductosEliminados();
$historial = obtenerHistorialProductos();
?>

<!DOCTYPE html>
<html lang="es" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Eliminaciones - Gestor de Inventario</title>
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
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 sm:mb-0">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Registro de Eliminaciones</h1>
            </div>
            <div class="flex items-center space-x-2">
                <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                    <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
                <a href="index.php" class="flex items-center space-x-1 bg-slate-200 dark:bg-slate-600 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-300 dark:border-slate-500">
                    <svg class="w-4 h-4 text-slate-700 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="text-sm text-slate-700 dark:text-slate-50">Volver al Inicio</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 space-y-8">
        <!-- Sección de Productos Eliminados -->
        <section class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700 rounded-t-xl">
                <h2 class="text-lg font-semibold text-white">Productos Eliminados</h2>
            </div>
            
            <?php if (!empty($productosEliminados)): ?>
                <div class="overflow-x-auto p-6">
                    <table class="w-full">
                        <thead class="bg-slate-100 dark:bg-slate-900/50 text-sm">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">ID</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">Producto</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300 hidden md:table-cell">Descripción</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">Precio</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">Stock</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">Eliminado</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <?php foreach ($productosEliminados as $producto): ?>
                                <tr class="hover:bg-rose-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100"><?php echo $producto['id']; ?></td>
                                    <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td class="px-6 py-4 hidden md:table-cell text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                    <td class="px-6 py-4 text-rose-700 dark:text-rose-400">$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td class="px-6 py-4 text-slate-900 dark:text-slate-100"><?php echo $producto['stock']; ?></td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm"><?php echo $producto['deleted_at']; ?></td>
                                    <td class="px-6 py-4">
                                        <a href="recuperar_producto.php?id=<?php echo $producto['id']; ?>" 
                                           class="flex items-center bg-emerald-600/90 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-all text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10M3 14h10m0-4H3m14 0h2m0 0v6m0-6v6m0-6h-2m2 6h-2"/>
                                            </svg>
                                            Restaurar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-slate-400 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-slate-600 dark:text-slate-400 font-medium">No hay productos eliminados</p>
                    <p class="text-slate-500 dark:text-slate-500 text-sm mt-2">Los productos eliminados aparecerán aquí</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Sección de Historial -->
        <section class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700 rounded-t-xl">
                <h2 class="text-lg font-semibold text-white">Bitácora de Cambios</h2>
            </div>
            
            <?php if (!empty($historial)): ?>
                <div class="overflow-x-auto p-6">
                    <table class="w-full">
                        <thead class="bg-slate-100 dark:bg-slate-900/50 text-sm">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">ID</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">Producto</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300">Acción</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700 dark:text-slate-300 hidden md:table-cell">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <?php foreach ($historial as $registro): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100"><?php echo $registro['producto_id']; ?></td>
                                    <td class="px-6 py-4 text-slate-900 dark:text-white"><?php echo htmlspecialchars($registro['nombre']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-sm <?php echo $registro['accion'] === 'Eliminado' ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300' : 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300' ?>">
                                            <?php echo $registro['accion']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell"><?php echo $registro['fecha_accion']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-slate-400 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-slate-600 dark:text-slate-400 font-medium">No hay registros históricos</p>
                    <p class="text-slate-500 dark:text-slate-500 text-sm mt-2">Todos los cambios se registrarán aquí</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggleButton = document.getElementById('theme-toggle-btn');
        const lightIcon = document.getElementById('theme-icon-light');
        const darkIcon = document.getElementById('theme-icon-dark');
        const htmlElement = document.documentElement;
        const bodyElement = document.body;
    
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
