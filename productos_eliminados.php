<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$productosEliminados = obtenerProductosEliminados();
$historial = obtenerHistorialProductos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Eliminaciones - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-50">
    <header class="bg-gradient-to-r from-slate-900 to-slate-800 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 sm:mb-0">
                <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <h1 class="text-2xl font-bold text-rose-100 tracking-tight">Registro de Eliminaciones</h1>
            </div>
            <a href="index.php" class="flex items-center space-x-1 bg-cyan-600/90 hover:bg-cyan-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-cyan-400/20">
                <svg class="w-4 h-4 text-cyan-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10M3 14h10m0-4H3m14 0h2m0 0v6m0-6v6m0-6h-2m2 6h-2"/>
                </svg>
                <span class="text-sm text-cyan-50">Panel Principal</span>
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 space-y-8">
        <!-- Sección de Productos Eliminados -->
        <section class="bg-white rounded-xl shadow-2xl border border-slate-200">
            <div class="px-6 py-4 bg-slate-900 border-b">
                <h2 class="text-lg font-semibold text-rose-100">Productos Eliminados</h2>
            </div>
            
            <?php if (!empty($productosEliminados)): ?>
                <div class="overflow-x-auto p-6">
                    <table class="w-full">
                        <thead class="bg-slate-800 text-slate-200 text-sm">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">ID</th>
                                <th class="px-6 py-3 text-left font-medium">Producto</th>
                                <th class="px-6 py-3 text-left font-medium hidden md:table-cell">Descripción</th>
                                <th class="px-6 py-3 text-left font-medium">Precio</th>
                                <th class="px-6 py-3 text-left font-medium">Stock</th>
                                <th class="px-6 py-3 text-left font-medium">Eliminado</th>
                                <th class="px-6 py-3 text-left font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            <?php foreach ($productosEliminados as $producto): ?>
                                <tr class="hover:bg-rose-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?php echo $producto['id']; ?></td>
                                    <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td class="px-6 py-4 hidden md:table-cell text-slate-600"><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                    <td class="px-6 py-4 text-rose-700">$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td class="px-6 py-4"><?php echo $producto['stock']; ?></td>
                                    <td class="px-6 py-4 text-slate-600 text-sm"><?php echo $producto['deleted_at']; ?></td>
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
                    <svg class="w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-slate-600 font-medium">No hay productos eliminados</p>
                    <p class="text-slate-500 text-sm mt-2">Los productos eliminados aparecerán aquí</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Sección de Historial -->
        <section class="bg-white rounded-xl shadow-2xl border border-slate-200">
            <div class="px-6 py-4 bg-slate-900 border-b">
                <h2 class="text-lg font-semibold text-cyan-100">Bitácora de Cambios</h2>
            </div>
            
            <?php if (!empty($historial)): ?>
                <div class="overflow-x-auto p-6">
                    <table class="w-full">
                        <thead class="bg-slate-800 text-slate-200 text-sm">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">ID</th>
                                <th class="px-6 py-3 text-left font-medium">Producto</th>
                                <th class="px-6 py-3 text-left font-medium">Acción</th>
                                <th class="px-6 py-3 text-left font-medium hidden md:table-cell">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            <?php foreach ($historial as $registro): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?php echo $registro['producto_id']; ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($registro['nombre']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-sm <?php echo $registro['accion'] === 'Eliminado' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                            <?php echo $registro['accion']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 hidden md:table-cell"><?php echo $registro['fecha_accion']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-slate-600 font-medium">No hay registros históricos</p>
                    <p class="text-slate-500 text-sm mt-2">Todos los cambios se registrarán aquí</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>