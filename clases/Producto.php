<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

class Producto
{
    private $conn;
    private $table = "productos";
    private $upload_dir = "../../layout/img/productos/"; // Ruta física donde se guardarán las fotos

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();

        // Crea la carpeta de imágenes si no existe de forma automática
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    public function read()
    {
        // LEFT JOIN: los productos sin proveedor también aparecen en el listado
        $stmt = $this->conn->prepare(
            "SELECT p.*, pr.nombre AS proveedor_nombre
             FROM $this->table p
             LEFT JOIN proveedor pr ON pr.id = p.proveedor_id
             ORDER BY p.id DESC"
        );
        $stmt->execute();
        return $stmt;
    }

    // Lista de proveedores para el <select> de los modales
    public function proveedores()
    {
        $stmt = $this->conn->prepare("SELECT id, nombre FROM proveedor ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function create()
    {
        $nombre = $_POST['nombre'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock  = $_POST['stock'] ?? 0;
        $proveedor_id = !empty($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
        $observaciones = trim($_POST['observaciones'] ?? '');
        $observaciones = $observaciones !== '' ? $observaciones : null;
        $imagen_url = $this->uploadImage();

        $stmt = $this->conn->prepare("INSERT INTO $this->table (nombre, precio, stock, observaciones, proveedor_id, imagen_url) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nombre, $precio, $stock, $observaciones, $proveedor_id, $imagen_url]);
    }

    public function get($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

   public function update($id)
    {
        $nombre = $_POST['nombre'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock  = $_POST['stock'] ?? 0;
        $proveedor_id = !empty($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
        $observaciones = trim($_POST['observaciones'] ?? '');
        $observaciones = $observaciones !== '' ? $observaciones : null;

        $producto_actual = $this->get($id);
        $imagen_url = $producto_actual->imagen_url;

        // Primero subimos la nueva; solo si tuvo éxito borramos la anterior
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $nueva = $this->uploadImage();
            if ($nueva !== null) {
                if (!empty($imagen_url) && file_exists($this->upload_dir . basename($imagen_url))) {
                    @unlink($this->upload_dir . basename($imagen_url));
                }
                $imagen_url = $nueva;
            }
        }

        $stmt = $this->conn->prepare("UPDATE $this->table SET nombre = ?, precio = ?, stock = ?, observaciones = ?, proveedor_id = ?, imagen_url = ? WHERE id = ?");
        return $stmt->execute([$nombre, $precio, $stock, $observaciones, $proveedor_id, $imagen_url, $id]);
    }

    public function delete($id)
    {
        $producto = $this->get($id);

        // Primero el registro; si MySQL lo rechaza (llave foránea) lanza excepción y la foto queda intacta
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = ?");
        $ok = $stmt->execute([$id]);

        if ($ok && $producto && !empty($producto->imagen_url)) {
            $archivo = $this->upload_dir . basename($producto->imagen_url);
            if (file_exists($archivo)) {
                @unlink($archivo);
            }
        }
        return $ok;
    }

    // ========================================================
    // FUNCIÓN AUXILIAR: Procesa y mueve la imagen al servidor
    // ========================================================
    private function uploadImage()
    {
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $filename = $_FILES['imagen']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            // Extensiones permitidas
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                // Generamos un nombre único usando la función time() para evitar colisiones
                $new_filename = time() . "_" . uniqid() . "." . $ext;
                $target_path = $this->upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_path)) {
                    // Retornamos la ruta relativa que se guardará en la DB (útil para las etiquetas <img>)
                    return "layout/img/productos/" . $new_filename;
                }
            }
        }
        return null; // Si no se subió nada o falló, queda como NULL en la base de datos
    }
}
