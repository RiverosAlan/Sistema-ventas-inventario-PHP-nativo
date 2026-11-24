<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

class Cliente
{
    private $conn;
    private $table = "clientes";

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
        $nom_comercial = $_POST['nom_comercial'] ?? '';
        $nom_legal     = $_POST['nom_legal'] ?? '';
        $nit           = $_POST['nit'] ?? '';
        $direccion     = $_POST['direccion'] ?? '';
        $telf          = $_POST['telf'] ?? '';
        $Rubro         = $_POST['Rubro'] ?? '';

        $stmt = $this->conn->prepare("INSERT INTO $this->table (nom_comercial, nom_legal, nit, direccion, telf, Rubro) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nom_comercial, $nom_legal, $nit, $direccion, $telf, $Rubro]);
    }

    public function get($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function update($id)
    {
        $nom_comercial = $_POST['nom_comercial'] ?? '';
        $nom_legal     = $_POST['nom_legal'] ?? '';
        $nit           = $_POST['nit'] ?? '';
        $direccion     = $_POST['direccion'] ?? '';
        $telf          = $_POST['telf'] ?? '';
        $Rubro         = $_POST['Rubro'] ?? '';

        $stmt = $this->conn->prepare("UPDATE $this->table SET nom_comercial = ?, nom_legal = ?, nit = ?, direccion = ?, telf = ?, Rubro = ? WHERE id = ?");
        return $stmt->execute([$nom_comercial, $nom_legal, $nit, $direccion, $telf, $Rubro, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Cantidad de ventas registradas a este cliente
    public function contarVentas($id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM ventas WHERE cliente_id = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
