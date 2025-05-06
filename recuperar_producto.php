<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (recuperarProductoEliminado($id)) {
        $_SESSION['mensaje'] = "Producto recuperado exitosamente.";
    } else {
        $_SESSION['error'] = "Error al recuperar el producto.";
    }
}

header("Location: productos_eliminados.php");
exit;
?> 