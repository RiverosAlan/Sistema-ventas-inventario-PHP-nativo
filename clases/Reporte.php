<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

class Reporte
{
    private $conn;

    // ===== AJUSTA AQUÍ si tus columnas se llaman distinto =====
    private $vFecha    = "fecha";           // ventas: fecha de la venta
    private $vTotal    = "total";           // ventas: total de la venta
    private $dVenta    = "venta_id";        // detalle_ventas: id de la venta
    private $dProducto = "producto_id";     // detalle_ventas: id del producto
    private $dCantidad = "cantidad";        // detalle_ventas: cantidad
    private $dPrecio   = "precio_unitario"; // detalle_ventas: precio por unidad
    // ===========================================================

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // 1. Total vendido por día
    public function ventasPorDia($desde, $hasta)
    {
        $sql = "SELECT DATE(v.$this->vFecha) AS dia, SUM(v.$this->vTotal) AS total
                FROM ventas v
                WHERE DATE(v.$this->vFecha) BETWEEN ? AND ?
                GROUP BY DATE(v.$this->vFecha)
                ORDER BY dia ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 2. Top 5 productos por unidades vendidas
    public function topProductos($desde, $hasta)
    {
        $sql = "SELECT p.nombre, SUM(d.$this->dCantidad) AS unidades
                FROM detalle_ventas d
                JOIN ventas v    ON v.id = d.$this->dVenta
                JOIN productos p ON p.id = d.$this->dProducto
                WHERE DATE(v.$this->vFecha) BETWEEN ? AND ?
                GROUP BY p.id, p.nombre
                ORDER BY unidades DESC
                LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 3. Monto vendido por proveedor
    public function ventasPorProveedor($desde, $hasta)
    {
        $sql = "SELECT COALESCE(pr.nombre, 'Sin proveedor') AS proveedor,
                       SUM(d.$this->dCantidad * d.$this->dPrecio) AS total
                FROM detalle_ventas d
                JOIN ventas v     ON v.id = d.$this->dVenta
                JOIN productos p  ON p.id = d.$this->dProducto
                LEFT JOIN proveedor pr ON pr.id = p.proveedor_id
                WHERE DATE(v.$this->vFecha) BETWEEN ? AND ?
                GROUP BY pr.id, pr.nombre
                ORDER BY total DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 4. Stock actual (los 10 con menos existencias)
    public function stockProductos()
    {
        $stmt = $this->conn->prepare("SELECT nombre, stock FROM productos ORDER BY stock ASC LIMIT 10");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}