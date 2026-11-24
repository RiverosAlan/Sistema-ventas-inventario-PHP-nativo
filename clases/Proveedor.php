<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

class Proveedor
{
    private $conn;
    private $table = "proveedor";

    // Opciones usadas en los formularios y en el listado
    public static $regimenes = ['N' => 'Nacional', 'I' => 'Internacional'];
    public static $categorias = ['1' => 'Primera', '2' => 'Segunda', '3' => 'Tercera'];
    public static $rubros = [
        'Alimentos'   => 'Alimentos y bebidas',
        'Limpieza'    => 'Limpieza e higiene',
        'Papeleria'   => 'Papelería y oficina',
        'Ferreteria'  => 'Ferretería y herramientas',
        'Electronica' => 'Electrónica y accesorios',
    ];

    public static function regimen($valor)
    {
        return htmlspecialchars(self::$regimenes[$valor] ?? $valor);
    }

    public static function categoria($valor)
    {
        return htmlspecialchars(self::$categorias[$valor] ?? $valor);
    }

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
    // Cantidad de productos que usan este proveedor
    public function contarProductos($id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM productos WHERE proveedor_id = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function create()
    {
        $nombre      = trim($_POST['nombre'] ?? '');
        $nit         = trim($_POST['nit'] ?? '');
        $regimen     = $_POST['regimen'] ?? '';
        $rubro       = trim($_POST['rubro'] ?? '');
        $fecha       = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
        $categoria   = $_POST['categoria'] ?? '';
        $presupuesto = $_POST['presupuesto'] ?? 0;

        $stmt = $this->conn->prepare(
            "INSERT INTO $this->table (nombre, nit, regimen, rubro, fecha, categoria, presupuesto)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$nombre, $nit, $regimen, $rubro, $fecha, $categoria, $presupuesto]);
    }

    public function get($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function update($id)
    {
        $nombre      = trim($_POST['nombre'] ?? '');
        $nit         = trim($_POST['nit'] ?? '');
        $regimen     = $_POST['regimen'] ?? '';
        $rubro       = trim($_POST['rubro'] ?? '');
        $fecha       = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
        $categoria   = $_POST['categoria'] ?? '';
        $presupuesto = $_POST['presupuesto'] ?? 0;

        $stmt = $this->conn->prepare(
            "UPDATE $this->table
             SET nombre = ?, nit = ?, regimen = ?, rubro = ?, fecha = ?, categoria = ?, presupuesto = ?
             WHERE id = ?"
        );
        return $stmt->execute([$nombre, $nit, $regimen, $rubro, $fecha, $categoria, $presupuesto, $id]);
    }

    public function delete($id)
    {
        // Si el proveedor tiene productos y hay llave foránea, MySQL lanza excepción
        // y el registro queda intacto; delete.php se encarga de avisar al usuario.
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
