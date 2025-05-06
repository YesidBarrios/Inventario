<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'vendor/autoload.php';
require_once 'includes/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$productos = obtenerProductos();

$documento = new Spreadsheet();
$hoja = $documento->getActiveSheet();

// Establecer los encabezados de las columnas
$hoja->setCellValue('A1', 'ID');
$hoja->setCellValue('B1', 'Nombre');
$hoja->setCellValue('C1', 'Descripción');
$hoja->setCellValue('D1', 'Precio');
$hoja->setCellValue('E1', 'Stock');
$hoja->setCellValue('F1', 'Stock Mínimo');

// Estilo para los encabezados
$estiloEncabezado = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'CCCCCC',
        ],
    ],
];
$hoja->getStyle('A1:F1')->applyFromArray($estiloEncabezado);

// Añadir los datos
$fila = 2;
foreach ($productos as $producto) {
    $hoja->setCellValue('A' . $fila, $producto['id']);
    $hoja->setCellValue('B' . $fila, $producto['nombre']);
    $hoja->setCellValue('C' . $fila, $producto['descripcion']);
    $hoja->setCellValue('D' . $fila, $producto['precio']);
    $hoja->setCellValue('E' . $fila, $producto['stock']);
    $hoja->setCellValue('F' . $fila, $producto['stock_minimo']);
    $fila++;
}

// Ajustar el ancho de las columnas automáticamente
foreach (range('A', 'F') as $columna) {
    $hoja->getColumnDimension($columna)->setAutoSize(true);
}

// Crear el archivo Excel
$writer = new Xlsx($documento);

// Configurar las cabeceras para la descarga del archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="inventario.xlsx"');
header('Cache-Control: max-age=0');

// Guardar el archivo Excel en el flujo de salida de PHP
$writer->save('php://output');
exit;
?>