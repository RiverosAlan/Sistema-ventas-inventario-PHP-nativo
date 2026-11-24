<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "../../clases/Usuario.php";
    $usuarioModel = new Usuario();

    $id = $_POST['id'] ?? 0;
    $nuevo_usuario = $_POST['cuenta'] ?? '';
    $password = $_POST['password'] ?? '';

    // Mapeamos el campo 'cuenta' al $_POST['usuario'] que espera el método update()
    $_POST['usuario'] = $nuevo_usuario;

    if ($usuarioModel->update($id)) {
        // ACTUALIZACIÓN DE SESIÓN EN CALIENTE:
        // 1. Modificamos el nombre de usuario en la sesión actual
        $_SESSION["user"] = $nuevo_usuario;

        // 2. Si el usuario ingresó una nueva contraseña, actualizamos el hash MD5 en la sesión
        if (!empty($password)) {
            $_SESSION["pasw"] = md5($password);
        }

        // Redireccionamos a la página actual con un mensaje de éxito de perfil
        header("Location: ../home/index.php?perfil_success=1");
        exit;
    }
}

header("Location: ../home/index.php");
exit;
?>
