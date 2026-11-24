<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

// Solo el Administrador puede eliminar usuarios
if ($UserConnected->nivel != 'A') {
    header("Location: ../../error-pages/401.html");
    exit;
}

require_once "../../clases/Usuario.php";
require_once "../../clases/Avatar.php";

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php?error=1");
    exit;
}

// Nadie puede eliminar su propia cuenta
if ($id === (int) $UserConnected->id) {
    header("Location: index.php?error=6");
    exit;
}

try {
    $usuarioModel = new Usuario();

    if ($usuarioModel->delete($id)) {
        // La foto se borra DESPUÉS de eliminar el registro: si MySQL lo rechaza, la foto queda intacta
        Avatar::eliminar($id);
        header("Location: index.php?delete_success=1");
    } else {
        header("Location: index.php?error=1");
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        // El usuario tiene ventas registradas (llave foránea en ventas.usuario_id)
        header("Location: index.php?error=2");
    } else {
        error_log("Error eliminando usuario: " . $e->getMessage());
        header("Location: index.php?error=1");
    }
}
exit;