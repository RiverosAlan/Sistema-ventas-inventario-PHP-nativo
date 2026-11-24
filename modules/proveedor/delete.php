<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Proveedor.php";

// Solo el Administrador elimina (igual que el botón del index)
if ($UserConnected->nivel !== 'A') {
    header("Location: index.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$proveedorModel = new Proveedor();

// No se elimina si tiene productos asociados
if ($proveedorModel->contarProductos($id) > 0) {
    header("Location: index.php?delete_error=productos");
    exit;
}

try {
    $proveedorModel->delete($id);
    header("Location: index.php?delete_success=1");
} catch (PDOException $e) {
    // Respaldo: si MySQL lo rechaza por llave foránea
    header("Location: index.php?delete_error=productos");
}
exit;