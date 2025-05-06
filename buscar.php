<?php
require_once 'includes/functions.php';

header('Content-Type: application/json');

$searchTerm = $_GET['term'] ?? $_POST['term'] ?? '';

if (!empty($searchTerm)) {
    $productos = buscarProductos($searchTerm);
    echo json_encode($productos);
} else {
    $productos = obtenerProductos();
    echo json_encode($productos);
}
?>