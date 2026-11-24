<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

// Solo Administrador (A) y Operador (O) pueden eliminar productos
if (!in_array($UserConnected->nivel, ['A', 'O'])) {
    header("Location: ../../error-pages/401.html");
    exit;
}

require_once "../../clases/Producto.php";
$productoModel = new Producto();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php?error=1");
    exit;
}

try {
    $productoModel->delete($id);
    header("Location: index.php?delete_success=1");
} catch (PDOException $e) {
    // Código 23000: el producto ya aparece en ventas (llave foránea en detalle_ventas)
    if ($e->getCode() == '23000') {
        header("Location: index.php?error=2");
    } else {
        header("Location: index.php?error=1");
    }
}
exit;