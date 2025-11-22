<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/config.php';
require_once 'includes/security_headers.php';
require_once 'includes/functions.php';

$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$productos = function_exists('getOptimizedProducts') ? getOptimizedProducts($limit, $offset) : obtenerProductos($limit, $offset);
$total_productos = contarProductosActivos();
$total_paginas = ceil($total_productos / $limit);
$productos_stock_bajo = getLowStockProducts();
$config = obtenerConfiguracion();
$stats_hoy = function_exists('getOptimizedDayStats') ? getOptimizedDayStats() : obtenerEstadisticasDia();
$unidades_vendidas_hoy = $stats_hoy['productos_vendidos'] ?? 0;
$total_ventas_hoy_monto = $stats_hoy['ventas_hoy']['total'] ?? 0;


?>
<!DOCTYPE html>
<html lang="es" class=""> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Aplicar tema inmediatamente para evitar parpadeo
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <title>Dashboard - <?php echo htmlspecialchars($config['nombre_tienda']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>


    <script>
        tailwind.config = {
             darkMode: 'class', // Habilitar modo oscuro basado en clase
             theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        slideIn: { '0%': { transform: 'translateX(-100%)' }, '100%': { transform: 'translateX(0)' } },
                        bounceGentle: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-5px)' } }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        /* Definición de la clase gradient-bg (solo para modo oscuro) */
        .dark .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
        /* Scrollbar Claro */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(45deg, #67e8f9, #60a5fa); border-radius: 3px; }
        /* Scrollbar Oscuro */
        .dark .custom-scrollbar::-webkit-scrollbar-track { background: #334155; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(45deg, #06b6d4, #3b82f6); }
        .sidebar-shadow { box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1); }
        .dark .sidebar-shadow { box-shadow: 4px 0 20px rgba(0, 0, 0, 0.25); }
    </style>
</head>
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200 min-h-screen">
<div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

<div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 sidebar-shadow transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="flex flex-col h-full">
        
        <div class="flex items-center justify-center h-20 px-6 border-b border-white/20 dark:border-slate-700/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center shadow-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
                <div><h1 class="text-lg font-bold text-white tracking-tight"><?php echo htmlspecialchars($config['nombre_tienda']); ?></h1><p class="text-xs text-cyan-100 dark:text-slate-400">Dashboard</p></div>
            </div>
        </div>
       
        <nav class="flex-1 px-4 py-6 space-y-2 custom-scrollbar overflow-y-auto">
            <div class="mb-6"><h3 class="px-3 text-xs font-semibold text-white/70 dark:text-slate-400 uppercase tracking-wider mb-3">Principal</h3><a href="index.php" class="flex items-center px-3 py-3 text-white bg-black/20 dark:bg-gradient-to-r dark:from-cyan-600 dark:to-blue-600 rounded-xl shadow-lg transform transition-all duration-200 hover:scale-105"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v2H8V5z"/></svg><span class="font-medium">Inventario</span></a></div>
            <div class="mb-6"><h3 class="px-3 text-xs font-semibold text-white/70 dark:text-slate-400 uppercase tracking-wider mb-3">Productos</h3><div class="space-y-1"><?php if (isAdmin()): ?><a href="agregar_producto.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg><span>Nuevo Producto</span></a><?php endif; ?><a href="stock_bajo.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Stock Bajo</span><?php if (!empty($productos_stock_bajo)): ?><span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full animate-pulse-slow"><?= count($productos_stock_bajo) ?></span><?php endif; ?></a><?php if (isAdmin()): ?><a href="productos_eliminados.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg><span>Eliminados</span></a><?php endif; ?></div></div>
            <div class="mb-6"><h3 class="px-3 text-xs font-semibold text-white/70 dark:text-slate-400 uppercase tracking-wider mb-3">Operaciones</h3><div class="space-y-1"><a href="registrar_venta.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Registrar Venta</span></a><?php if (isAdmin()): ?><a href="registrar_compra.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg><span>Registrar Compra</span></a><a href="historial_compras.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg><span>Historial Compras</span></a><?php endif; ?></div></div>
            <div class="mb-6"><h3 class="px-3 text-xs font-semibold text-white/70 dark:text-slate-400 uppercase tracking-wider mb-3">Gestión</h3><div class="space-y-1"><?php if (isAdmin()): ?><a href="reportes.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Reportes</span></a><?php endif; ?><a href="proveedores.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Proveedores</span></a><?php if (isAdmin()): ?><a href="configuracion.php" class="flex items-center px-3 py-2.5 text-cyan-100 dark:text-slate-300 hover:text-white hover:bg-black/10 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200 group"><svg class="w-5 h-5 mr-3 group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Configuración</span></a><?php endif; ?></div></div>
        </nav>
        <div class="p-4 border-t border-slate-700/50"><div class="flex items-center space-x-3 mb-4"><div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/></svg></div><div class="flex-1 min-w-0"><p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p><p class="text-xs text-cyan-100 dark:text-slate-400">Administrador</p></div></div><a href="logout.php" class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-700 rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 transform hover:scale-105 shadow-lg"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Cerrar Sesión</a></div>
    </div>
</div>

<div class="lg:hidden bg-white dark:bg-slate-900 shadow-lg border-b border-slate-200 dark:border-slate-700 relative z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <button id="sidebar-toggle" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"><svg class="w-6 h-6 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <h1 class="text-lg font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($config['nombre_tienda']); ?></h1>
        <div class="w-10"></div>
    </div>
</div>

<div class="lg:ml-64 min-h-screen">
    <header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Dashboard de Inventario</h1>
                    <p class="text-sm text-cyan-100 mt-1">Gestiona tu inventario</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Buscar productos..." class="w-80 pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition-all text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <a href="agregar_producto.php" class="flex items-center px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-emerald-500/25"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Nuevo Producto</a>
                    <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                        <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Card 1: Total Productos -->
            <div class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900/30 dark:to-blue-900/30">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col space-y-1">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Productos</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $total_productos; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-cyan-400 rounded-xl flex items-center justify-center shadow-lg shadow-cyan-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>
            </div>
            <!-- Card 2: Stock Bajo -->
            <div class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 <?php echo !empty($productos_stock_bajo) ? 'border-red-500/50 dark:border-red-500/70 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900/30 dark:to-red-900/40' : 'bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/30'; ?>">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col space-y-1">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Productos con Stock Bajo</p>
                        <p class="text-3xl font-bold <?php echo !empty($productos_stock_bajo) ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white'; ?>"><?php echo count($productos_stock_bajo); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                 <?php if (!empty($productos_stock_bajo)): ?><div class="mt-3 text-xs"><a href="stock_bajo.php" class="text-red-600 dark:text-red-400 font-semibold hover:underline">Ver detalles →</a></div><?php endif; ?>
            </div>
            <!-- Card 3: Ventas Hoy -->
            <div class="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900/30 dark:to-green-900/30">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col space-y-1">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Unidades Vendidas (Hoy)</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo number_format($unidades_vendidas_hoy, 0); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-400 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/20">
                         <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">Ingresos de hoy: <span class="font-bold text-emerald-600 dark:text-emerald-400">$<?php echo number_format($total_ventas_hoy_monto, 2); ?></span></div>
            </div>
        </div>
        

        
        <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden animate-fade-in">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700 flex justify-between items-center"><div class="flex items-center space-x-3"><div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div><h2 class="text-xl font-bold text-white">Lista de Productos</h2><span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm"><?php echo $total_productos; ?> productos</span></div><?php if ($total_paginas > 1): ?><nav class="flex items-center space-x-1"><?php if ($page > 1): ?><a href="index.php?page=<?php echo $page - 1; ?>" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a><?php endif; ?><?php for ($i = max(1, $page - 2); $i <= min($total_paginas, $page + 2); $i++): ?><a href="index.php?page=<?php echo $i; ?>" class="px-3 py-1.5 <?php echo $i == $page ? 'bg-white text-cyan-600' : 'bg-white/20 hover:bg-white/30 text-white'; ?> rounded-lg text-sm transition-colors"><?php echo $i; ?></a><?php endfor; ?><?php if ($page < $total_paginas): ?><a href="index.php?page=<?php echo $page + 1; ?>" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a><?php endif; ?></nav><?php endif; ?></div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full">
                    <thead class="bg-slate-100 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400"><tr><th class="px-6 py-4 text-left font-semibold text-xs uppercase">ID</th><th class="px-6 py-4 text-left font-semibold text-xs uppercase">Producto</th><th class="px-6 py-4 text-left font-semibold text-xs uppercase hidden md:table-cell">Descripción</th><th class="px-6 py-4 text-left font-semibold text-xs uppercase">Precio</th><th class="px-6 py-4 text-left font-semibold text-xs uppercase">Stock</th><th class="px-6 py-4 text-left font-semibold text-xs uppercase hidden sm:table-cell">Mínimo</th><th class="px-6 py-4 text-left font-semibold text-xs uppercase">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-slate-300 dark:divide-slate-600">
                        <?php if (empty($productos)): ?>
                            <tr><td colspan="7" class="text-center py-16"><div class="flex flex-col items-center"><div class="w-20 h-20 bg-slate-200 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4"><svg class="w-10 h-10 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div><h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-2">No hay productos</h3><p class="text-slate-500 dark:text-slate-400 mb-6">Empieza agregando tu primer producto.</p><a href="agregar_producto.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:shadow-lg"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Agregar Producto</a></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($productos as $index => $producto): ?>
                            <tr class="<?php echo $producto['stock'] <= $producto['stock_minimo'] ? 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500' : 'hover:bg-slate-50 dark:hover:bg-slate-700/30'; ?> transition-all duration-200 animate-fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                <td class="px-6 py-4"><div class="flex items-center"><div class="w-8 h-8 bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 text-sm font-bold"><?php echo $producto['id']; ?></div></div></td>
                                <td class="px-6 py-4"><div class="flex items-center space-x-3"><div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-800/60 rounded-lg flex items-center justify-center ring-2 ring-cyan-200 dark:ring-cyan-700"><svg class="w-5 h-5 text-cyan-600 dark:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div><div><p class="font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($producto['nombre']); ?></p><?php if ($producto['stock'] <= $producto['stock_minimo']): ?><div class="flex items-center mt-1"><span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 dark:bg-red-500/20 text-red-800 dark:text-red-300 rounded-full border border-red-200 dark:border-red-500/30"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 12a1 1 0 110-2 1 1 0 010 2zm1-9a1 1 0 10-2 0v4a1 1 0 102 0V5z"/></svg>Stock crítico</span></div><?php endif; ?></div></div></td>
                                <td class="px-6 py-4 hidden md:table-cell"><p class="text-slate-600 dark:text-slate-400 text-sm"><?php echo htmlspecialchars($producto['descripcion']); ?></p></td>
                                <td class="px-6 py-4"><div class="text-lg font-bold text-emerald-700 dark:text-emerald-400">$<?php echo number_format($producto['precio'], 2); ?></div><div class="text-xs text-slate-500 dark:text-slate-400">por <?= htmlspecialchars($producto['abreviatura_unidad'] ?? 'unidad') ?></div></td>
                                <td class="px-6 py-4"><div class="flex items-center space-x-2"><div class="text-lg font-semibold <?php echo $producto['stock'] <= $producto['stock_minimo'] ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white'; ?>"><?php echo $producto['stock']; ?></div><span class="text-sm text-slate-500 dark:text-slate-400"><?= htmlspecialchars($producto['abreviatura_unidad'] ?? 'un') ?></span></div></td>
                                <td class="px-6 py-4 hidden sm:table-cell"><div class="text-slate-600 dark:text-slate-400"><?php echo $producto['stock_minimo']; ?> <?= htmlspecialchars($producto['abreviatura_unidad'] ?? 'un') ?></div></td>
                                <td class="px-6 py-4"><?php if (isAdmin()): ?><div class="flex items-center space-x-2">
                                    <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="p-2 bg-blue-100 dark:bg-blue-500/20 hover:bg-blue-200 dark:hover:bg-blue-500/40 text-blue-600 dark:text-blue-300 rounded-lg transition-all" title="Editar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></a>
                                    <button type="button" class="delete-btn p-2 bg-red-100 dark:bg-red-500/20 hover:bg-red-200 dark:hover:bg-red-500/40 text-red-600 dark:text-red-300 rounded-lg transition-all" title="Eliminar" data-product-id="<?php echo $producto['id']; ?>" data-product-name="<?php echo htmlspecialchars($producto['nombre']); ?>"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_paginas > 1): ?>
        <div class="flex justify-center mt-8"><nav class="flex items-center space-x-2 bg-white dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg p-2"><?php if ($page > 1): ?><a href="index.php?page=<?php echo $page - 1; ?>" class="flex items-center px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>Anterior</a><?php endif; ?><?php for ($i = 1; $i <= $total_paginas; $i++): ?><a href="index.php?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i == $page ? 'bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow-lg' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50'; ?> rounded-lg transition-all duration-200 font-medium"><?php echo $i; ?></a><?php endfor; ?><?php if ($page < $total_paginas): ?><a href="index.php?page=<?php echo $page + 1; ?>" class="flex items-center px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-lg transition-all duration-200">Siguiente<svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a><?php endif; ?></nav></div>
        <?php endif; ?>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm z-[100] flex items-center justify-center p-4 hidden animate-fade-in">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md transform transition-all border border-slate-200 dark:border-slate-700">
        <div class="p-6 text-center"><div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-500/10 mb-4 ring-4 ring-red-200 dark:ring-red-500/20"><svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div><h3 class="text-2xl font-bold text-slate-800 dark:text-white">Confirmar Eliminación</h3><p class="mt-2 text-slate-600 dark:text-slate-400">¿Estás seguro? El producto <strong id="modal-product-name" class="text-slate-900 dark:text-slate-100"></strong> se moverá a la papelera.</p></div>
        <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-slate-200 dark:border-slate-700"><button id="modal-cancel-btn" type="button" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">Cancelar</button><a id="modal-confirm-btn" href="#" class="px-6 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">Eliminar</a></div>
    </div>
</div>

<?php include 'includes/chatbot_widget.php'; ?>

<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Lógica del Sidebar y Search ---
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const searchInput = document.getElementById('searchInput');

    function toggleSidebar() {
        if (sidebar && sidebarOverlay) {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        }
    }
    if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const searchTerm = searchInput.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    const productNameCell = row.querySelector('td:nth-child(2) p');
                    if (productNameCell) {
                        const text = productNameCell.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) || searchTerm === '' ? '' : 'none';
                    }
                });
            }, 300);
        });
    }

    // --- Lógica del Modal de Confirmación ---
    const deleteModal = document.getElementById('delete-modal');
    const modalProductName = document.getElementById('modal-product-name');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');
    const modalConfirmBtn = document.getElementById('modal-confirm-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    function openModal(productId, productName) {
        if(modalProductName) modalProductName.textContent = `"${productName}"`;
        if(modalConfirmBtn) modalConfirmBtn.href = `eliminar_producto.php?id=${productId}`;
        if(deleteModal) deleteModal.classList.remove('hidden');
    }

    function closeModal() {
       if(deleteModal) deleteModal.classList.add('hidden');
    }

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            openModal(productId, productName);
        });
    });

    if(modalCancelBtn) modalCancelBtn.addEventListener('click', closeModal);
    if(deleteModal) deleteModal.addEventListener('click', e => (e.target === deleteModal) && closeModal());

    // <CHANGE> Corregido: usar clase 'dark' en lugar de 'theme-dark' para que Tailwind funcione
    const themeToggleButton = document.getElementById('theme-toggle-btn');
    const lightIcon = document.getElementById('theme-icon-light');
    const darkIcon = document.getElementById('theme-icon-dark');
    const bodyElement = document.body;
    const htmlElement = document.documentElement;

    function applyTheme(theme) {
        // Remover la clase 'dark' de Tailwind
        htmlElement.classList.remove('dark');
        if(lightIcon) lightIcon.classList.add('hidden');
        if(darkIcon) darkIcon.classList.add('hidden');

        if (theme === 'dark') {
            // Agregar la clase 'dark' que Tailwind espera
            htmlElement.classList.add('dark');
            if(darkIcon) darkIcon.classList.remove('hidden');
            localStorage.setItem('theme', 'dark');
        } else {
            // Modo claro (sin clase 'dark')
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

}); // Fin DOMContentLoaded
</script>

<script>
    // Ajuste automático de escala para pantallas pequeñas
    function adjustScale() {
        if (window.innerWidth < 1400) {
            document.body.style.zoom = "80%";
        } else {
            document.body.style.zoom = "100%";
        }
    }
    window.addEventListener('resize', adjustScale);
    document.addEventListener('DOMContentLoaded', adjustScale);
</script>


</body>
</html>