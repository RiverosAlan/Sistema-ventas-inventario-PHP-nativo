<?php
class Venta
{
    private $conn;
    private $table_ventas = "ventas";
    private $table_detalle = "detalle_ventas";

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }


    //Lista todas las ventas con los nombres legibles de sus clientes y los vendedores

    public function read()
    {
        $query = "SELECT v.*, c.nom_comercial AS cliente, u.nombre AS vendedor 
                  FROM $this->table_ventas v
                  INNER JOIN clientes c ON v.cliente_id = c.id
                  INNER JOIN usuarios u ON v.usuario_id = u.id
                  ORDER BY v.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    //Procesa la venta principal y su lista de productos en una transacción segura
    public function crearVenta($cliente_id, $usuario_id, $total, $productos)
    {
        try {
            $this->conn->beginTransaction();

            $stmtVenta = $this->conn->prepare("INSERT INTO $this->table_ventas (cliente_id, usuario_id, total) VALUES (?, ?, ?)");
            $stmtVenta->execute([$cliente_id, $usuario_id, $total]);
            $venta_id = $this->conn->lastInsertId();

            $stmtDetalle = $this->conn->prepare("INSERT INTO $this->table_detalle (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmtStock   = $this->conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($productos as $item) {
                $stmtDetalle->execute([$venta_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario']]);

                $stmtStock->execute([$item['cantidad'], $item['producto_id'], $item['cantidad']]);
                if ($stmtStock->rowCount() === 0) {
                    throw new Exception("Stock insuficiente para el producto " . $item['producto_id']);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error en Venta: " . $e->getMessage());
            return false;
        }
    }

    //Obtiene los datos generales de una venta específica
    public function get($id)
    {
        $query = "SELECT v.*, c.nom_comercial AS cliente, u.nombre AS vendedor
                  FROM $this->table_ventas v
                  INNER JOIN clientes c ON v.cliente_id = c.id
                  INNER JOIN usuarios u ON v.usuario_id = u.id
                  WHERE v.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    //Obtiene el desglose de productos que pertenecen a una venta en particular
    public function getDetalle($venta_id)
    {
        $query = "SELECT d.*, p.nombre AS producto, p.imagen_url 
                  FROM $this->table_detalle d
                  INNER JOIN productos p ON d.producto_id = p.id
                  WHERE d.venta_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$venta_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    //Elimina una venta (Al tener llaves foráneas con ON DELETE CASCADE, borrará los detalles automáticamente)
    public function delete($id)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Devolver al inventario lo que se había descontado
            $stmtItems = $this->conn->prepare("SELECT producto_id, cantidad FROM $this->table_detalle WHERE venta_id = ?");
            $stmtItems->execute([$id]);
            $stmtStock = $this->conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            foreach ($stmtItems->fetchAll(PDO::FETCH_OBJ) as $it) {
                $stmtStock->execute([$it->cantidad, $it->producto_id]);
            }

            // 2. Borrar detalle y cabecera (funciona con o sin ON DELETE CASCADE)
            $this->conn->prepare("DELETE FROM $this->table_detalle WHERE venta_id = ?")->execute([$id]);
            $ok = $this->conn->prepare("DELETE FROM $this->table_ventas WHERE id = ?")->execute([$id]);

            $this->conn->commit();
            return $ok;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error al eliminar venta: " . $e->getMessage());
            return false;
        }
    }

    // Lista ventas ordenadas por fecha (más recientes primero), con rango opcional
    public function listar($desde = null, $hasta = null)
    {
        $query = "SELECT v.*, c.nom_comercial AS cliente, u.nombre AS vendedor
              FROM $this->table_ventas v
              INNER JOIN clientes c ON v.cliente_id = c.id
              INNER JOIN usuarios u ON v.usuario_id = u.id";
        $where  = [];
        $params = [];

        if ($desde) {
            $where[] = "DATE(v.fecha) >= ?";
            $params[] = $desde;
        }
        if ($hasta) {
            $where[] = "DATE(v.fecha) <= ?";
            $params[] = $hasta;
        }
        if ($where) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY v.fecha DESC, v.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}
