<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

// Solo el Administrador puede eliminar ventas (al hacerlo se devuelve el stock)
if ($UserConnected->nivel !== 'A') {
    header("Location: ../../error-pages/401.html");
    exit;
}

require_once "../../clases/Venta.php";
$ventaModel = new Venta();

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0 && $ventaModel->delete($id)) {
    header("Location: index.php?delete_success=1");
} else {
    header("Location: index.php?error=1");
}
exit;