<?php
require_once "config/conexion.php";

class Auth
{
    private $conn;
    private $table = "usuarios";
    public $motivoRechazo = null;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();

        // Asegura que la sesión esté iniciada si no lo está
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password)
    {
        $passwordMD5 = md5($password);

        // 1. Buscamos el usuario de forma segura con consultas preparadas
        $query = "SELECT * FROM " . $this->table . " WHERE usuario = ? AND password = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $passwordMD5]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            // Cuenta desactivada: no se crea token, sesión ni cookies
            if ((int) ($user->activo ?? 1) === 0) {
                $this->motivoRechazo = 'inactivo';
                return false;
            }

            // Generamos un token aleatorio y seguro
            $token = md5(rand(0, 100000) . time());

            // 2. Guardamos el token en la Base de Datos de forma segura
            $queryUpdate = "UPDATE " . $this->table . " SET token = ? WHERE id = ?";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->execute([$token, $user->id]);

            // 3. Guardamos los datos necesarios en la Sesión de PHP
            $_SESSION["user"]  = $username;
            $_SESSION["pasw"]  = $passwordMD5;
            $_SESSION["token"] = $token;

            // 4. Manejo de Cookies para la casilla "Recordar"
            $recordar = $_POST['recordar'] ?? '0';
            if ($recordar === '1') {
                // Si marca la casilla, guardamos los datos por unos 23 días (2.000.000 segundos)
                setcookie("recordar", '1', time() + 2000000, "/");
                setcookie("user", $username, time() + 2000000, "/");
                setcookie("pasw", $password, time() + 2000000, "/");
            } else {
                // Si no la marca o la desmarca, destruimos las cookies limpiando su valor y expirándolas
                setcookie("recordar", '0', time() - 3600, "/");
                setcookie("user", '', time() - 3600, "/");
                setcookie("pasw", '', time() - 3600, "/");
            }

            return $user;
        }
        return false;
    }

    public function logout()
    {
        // Aseguramos que la sesión esté abierta para poder destruirla
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpiamos y destruimos las sesiones
        $_SESSION = array();
        session_unset();
        session_destroy();
    }
}
