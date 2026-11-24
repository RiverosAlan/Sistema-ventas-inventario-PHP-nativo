<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Cliente.php";

// Mismo control de roles que el botón del index
if (!in_array($UserConnected->nivel, ['A', 'O'])) {
    header("Location: index.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$clienteModel = new Cliente();

// No se elimina si tiene ventas registradas
if ($clienteModel->contarVentas($id) > 0) {
    header("Location: index.php?delete_error=ventas");
    exit;
}

try {
    $clienteModel->delete($id);
    header("Location: index.php?delete_success=1");
} catch (PDOException $e) {
    // Respaldo: si MySQL lo rechaza por llave foránea
    header("Location: index.php?delete_error=ventas");
}
exit;