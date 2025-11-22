<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

// Obtener todos los productos, sin paginación
$productos = obtenerProductos(null, 0);

$filename = "reporte_inventario_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");

// Escribir la fila de encabezado
fputcsv($output, array('ID', 'Nombre', 'Descripcion', 'Precio', 'Stock', 'Stock Minimo', 'Proveedor'), ';');

// Escribir los datos de los productos
if (!empty($productos)) {
    foreach ($productos as $producto) {
        fputcsv($output, array(
            $producto['id'],
            $producto['nombre'],
            $producto['descripcion'],
            $producto['precio'],
            $producto['stock'],
            $producto['stock_minimo'],
            $producto['nombre_proveedor'] ?? 'N/A' // Usar el nombre del proveedor si existe
        ), ';');
    }
}

fclose($output);
exit;
?>