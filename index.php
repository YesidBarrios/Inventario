<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';
$productos = obtenerProductos();
$config = obtenerConfiguracion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200">
<header class="bg-gradient-to-r from-slate-900 to-slate-800 shadow-xl">
    <div class="container mx-auto px-4 py-3 flex flex-col sm:flex-row justify-between items-center">
        <div class="flex items-center space-x-2">
            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h1 class="text-2xl font-bold text-cyan-100 tracking-tight font-mono"><?php echo htmlspecialchars($config['nombre_tienda']); ?></h1>
        </div>
        <div class="flex items-center mt-2 sm:mt-0 space-x-4">
            <div class="flex items-center space-x-2 bg-white/5 px-3 py-1 rounded-full border border-cyan-400/20">
                <span class="text-sm text-cyan-100"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <svg class="w-4 h-4 text-cyan-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/></svg>
            </div>
            <a href="logout.php" class="flex items-center space-x-1 bg-cyan-600/90 hover:bg-cyan-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-cyan-400/20">
                <svg class="w-4 h-4 text-cyan-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="text-sm text-cyan-50">Salir</span>
            </a>
        </div>
    </div>
</header>
    <main class="container mx-auto px-4 py-8">
    <section class="mb-8">
    <div class="flex flex-wrap gap-3 mb-4">
    <!-- Botón Agregar Producto -->
    <a href="agregar_producto.php" class="flex items-center bg-emerald-700/90 hover:bg-emerald-800 text-emerald-50 px-4 py-2 rounded-lg transition-all shadow-sm border border-emerald-500/20">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
        Nuevo Producto
    </a>

    <!-- Botón Reportes -->
    <a href="reportes.php" class="flex items-center bg-slate-700/90 hover:bg-slate-800 text-slate-100 px-4 py-2 rounded-lg transition-all shadow-sm border border-slate-500/20">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Reportes
    </a>

    <!-- Botón Eliminados -->
    <a href="productos_eliminados.php" class="flex items-center bg-amber-700/90 hover:bg-amber-800 text-amber-100 px-4 py-2 rounded-lg transition-all shadow-sm border border-amber-500/20">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Eliminados
    </a>

    <!-- Botón Exportar -->
    <a href="exportar.php" class="flex items-center bg-cyan-700/90 hover:bg-cyan-800 text-cyan-100 px-4 py-2 rounded-lg transition-all shadow-sm border border-cyan-500/20">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Exportar
    </a>

    <!-- Botón Venta -->
    <a href="registrar_venta.php" class="flex items-center bg-violet-700/90 hover:bg-violet-800 text-violet-100 px-4 py-2 rounded-lg transition-all shadow-sm border border-violet-500/20">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Venta
    </a>
</div>

    <!-- Barra de Búsqueda y Configuración (se mantiene igual) -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="relative flex-1 max-w-md">
            <input type="text" id="searchInput" placeholder="Buscar productos..." 
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
            <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <a href="configuracion.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-all shadow-sm flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
            </svg>
            Configuración
        </a>
    </div>
</section>
<section class="bg-slate-50 rounded-xl shadow-lg border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 bg-slate-900 border-b">
        <h2 class="text-lg font-semibold text-cyan-100">Lista de Productos</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-800 text-slate-200 text-sm">
                <tr>
                    <th class="px-6 py-3 text-left font-medium">ID</th>
                    <th class="px-6 py-3 text-left font-medium">Producto</th>
                    <th class="px-6 py-3 text-left font-medium hidden md:table-cell">Descripción</th>
                    <th class="px-6 py-3 text-left font-medium">Precio</th>
                    <th class="px-6 py-3 text-left font-medium">Stock</th>
                    <th class="px-6 py-3 text-left font-medium hidden sm:table-cell">Mínimo</th>
                    <th class="px-6 py-3 text-left font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-700">
                <?php foreach ($productos as $producto): ?>
                <tr class="<?php echo $producto['stock'] <= $producto['stock_minimo'] ? 'bg-rose-50' : 'hover:bg-slate-100 transition-colors'; ?>">
                    <td class="px-6 py-4 font-medium text-slate-900"><?php echo $producto['id']; ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <?php echo htmlspecialchars($producto['nombre']); ?>
                            <?php if ($producto['stock'] <= $producto['stock_minimo']): ?>
                                <span class="flex items-center ml-2 text-xs font-medium bg-rose-100 text-rose-800 px-2 py-1 rounded-full border border-rose-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 12a1 1 0 110-2 1 1 0 010 2zm1-9a1 1 0 10-2 0v4a1 1 0 102 0V5z"/></svg>
                                    Stock bajo
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell text-slate-600"><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                    <td class="px-6 py-4 font-semibold text-emerald-700">$<?php echo number_format($producto['precio'], 2); ?></td>
                    <td class="px-6 py-4 text-slate-900"><?php echo $producto['stock']; ?></td>
                    <td class="px-6 py-4 hidden sm:table-cell text-slate-600"><?php echo $producto['stock_minimo']; ?></td>
                    <td class="px-6 py-4">
    <div class="flex gap-3">
        <!-- Botón Editar (Icono de lápiz) -->
        <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" 
           class="p-1.5 hover:bg-slate-200 rounded-full transition-colors"
           title="Editar">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </a>

        <!-- Botón Eliminar (Icono de basura) -->
        <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>" 
           class="p-1.5 hover:bg-rose-100 rounded-full transition-colors"
           title="Eliminar"
           onclick="return confirm('¿Estás seguro?');">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </a>
    </div>
</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
</main>
    <script>
    document.getElementById('searchInput').addEventListener('input', function() {
        var searchTerm = this.value;
        var tbody = document.querySelector('tbody');

        fetch('buscar.php?term=' + encodeURIComponent(searchTerm))
            .then(response => response.json())
            .then(productos => {
                tbody.innerHTML = ''; // Limpiar la tabla actual
                productos.forEach(producto => {
                    const row = document.createElement('tr');
                    row.classList.add('border-b');
                    if (producto.stock <= producto.stock_minimo) {
                        row.classList.add('bg-red-100');
                    }

                    row.innerHTML = `
                        <td class="px-4 py-2">${producto.id}</td>
                        <td class="px-4 py-2">
                            ${htmlspecialchars(producto.nombre)}
                            ${producto.stock <= producto.stock_minimo ? '<span class="inline-block bg-red-500 text-white text-xs px-2 py-1 rounded ml-2">¡Stock Bajo!</span>' : ''}
                        </td>
                        <td class="px-4 py-2">${htmlspecialchars(producto.descripcion)}</td>
                        <td class="px-4 py-2">$${parseFloat(producto.precio).toFixed(2)}</td>
                        <td class="px-4 py-2">${producto.stock}</td>
                        <td class="px-4 py-2">${producto.stock_minimo}</td>
                        <td class="px-4 py-2">
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                <a href="editar_producto.php?id=${producto.id}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-2 rounded text-sm text-center">Editar</a>
                                <a href="eliminar_producto.php?id=${producto.id}" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded text-sm text-center" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');">Eliminar</a>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error en la búsqueda:', error);
                // Opcional: mostrar un mensaje de error al usuario
            });
    });

    // Función de escape para HTML (simple, considerar una librería para producción)
    function htmlspecialchars(str) {
        var map = {
            '&': '&',
            '<': '<',
            '>': '>',
            '"': '"',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
</body>
</html>
        