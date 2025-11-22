<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';
requireAdmin();

// Si es GET y tiene un ID, proceder a eliminar directamente
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // No se requiere doble autorización, eliminar directamente
    if (eliminarProducto($id)) {
        $_SESSION['success'] = "Producto eliminado correctamente";
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = "Error al eliminar el producto";
        // Redirigir a la página principal o a una página de error si falla
        header('Location: index.php');
        exit;
    }
} else {
    // Si no es una solicitud GET con ID, redirigir a la página principal
    header('Location: index.php');
    exit;
}
?>