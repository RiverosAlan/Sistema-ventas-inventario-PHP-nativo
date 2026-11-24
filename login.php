<?php
// 1. IMPORTANTE: Iniciamos el motor de sesiones en la primera línea
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('config/conexion.php'); // conectado

// Recibo las variables
$cuenta   = $_POST['cuenta'];
$password = md5($_POST['password']);

// CORRECCIÓN: Agregamos "AND activo = '1'" para que solo deje pasar a los usuarios permitidos
$consulta = "SELECT * FROM usuarios WHERE cuenta='{$cuenta}' AND password='{$password}' AND activo = '1'";

// Ejecuta la consulta
$resultado = mysqli_query($conexion, $consulta);

if (mysqli_num_rows($resultado) != 0) {
    // Si el usuario existe, su clave coincide Y está ACTIVO, guardamos los datos
    $_SESSION['cuenta']   = $cuenta;
    $_SESSION['password'] = $password;
    
    header("Location: modules/home/index.php");
    exit();
} else {
    // Si la contraseña es incorrecta O el usuario está INACTIVO, lo rebota con código de error
    header("Location: index.php?sw=1");
    exit();
}
?>
