<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

class Usuario
{
    private $conn;
    private $table = "usuarios";

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function read()
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table ORDER BY id DESC");
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        $nombre   = $_POST['nombre'] ?? '';
        $cargo    = $_POST['cargo'] ?? '';
        $usuario  = $_POST['usuario'] ?? '';
        $password = md5($_POST['password'] ?? '');
        $nivel    = $_POST['nivel'] ?? '';

        $stmt = $this->conn->prepare("INSERT INTO $this->table (nombre, cargo, usuario, password, nivel) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nombre, $cargo, $usuario, $password, $nivel]);
    }

    public function get($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function update($id)
    {
        $nombre  = $_POST['nombre'] ?? '';
        $cargo   = $_POST['cargo'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';
        $nivel   = $_POST['nivel'] ?? '';

        if (!empty($password)) {
            // Si el usuario cambió la contraseña
            $stmt = $this->conn->prepare("UPDATE $this->table SET nombre = ?, cargo = ?, usuario = ?, password = ?, nivel = ? WHERE id = ?");
            return $stmt->execute([$nombre, $cargo, $usuario, md5($password), $nivel, $id]);
        } else {
            // Si la contraseña se dejó en blanco (no se actualiza)
            $stmt = $this->conn->prepare("UPDATE $this->table SET nombre = ?, cargo = ?, usuario = ?, nivel = ? WHERE id = ?");
            return $stmt->execute([$nombre, $cargo, $usuario, $nivel, $id]);
        }
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function cambiarEstado($id, $estado)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
}
