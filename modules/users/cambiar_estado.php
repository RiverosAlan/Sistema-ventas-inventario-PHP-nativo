<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

// Solo el Administrador puede activar o desactivar usuarios
if ($UserConnected->nivel != 'A') {
    header("Location: ../../error-pages/401.html");
    exit;
}

require_once "../../clases/Usuario.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id           = (int) ($_POST['id'] ?? 0);
$nuevo_estado = $_POST['nuevo_estado'] ?? '';

// Solo se aceptan los valores 0 y 1
if ($id <= 0 || !in_array($nuevo_estado, ['0', '1'], true)) {
    header("Location: index.php?error=1");
    exit;
}

// Un administrador no puede desactivarse a sí mismo (quedaría fuera del sistema)
if ($id === (int) $UserConnected->id && $nuevo_estado === '0') {
    header("Location: index.php?error=5");
    exit;
}

try {
    $usuarioModel = new Usuario();
    if ($usuarioModel->cambiarEstado($id, (int) $nuevo_estado)) {
        header("Location: index.php?estado_success=" . $nuevo_estado);
    } else {
        header("Location: index.php?error=1");
    }
} catch (PDOException $e) {
    // El detalle del error va al log del servidor, no a la pantalla
    error_log("Error cambiando estado de usuario: " . $e->getMessage());
    header("Location: index.php?error=1");
}
exit;