<?php
// Lógica de registro (se incluye desde index.php, que ya tiene $productoModel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear_producto') {
  $nombre = trim($_POST['nombre'] ?? '');
  $precio = $_POST['precio'] ?? '';
  $stock  = $_POST['stock'] ?? '';

  if ($nombre === '' || !is_numeric($precio) || $precio < 0 || filter_var($stock, FILTER_VALIDATE_INT) === false || $stock < 0) {
    header("Location: index.php?error=1");
    exit;
  }

  // La imagen es opcional, pero si se envió debe ser válida
  if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
      header("Location: index.php?error=3");
      exit;
    }
  }

  try {
    $productoModel->create();   // lee $_POST y $_FILES internamente (incluido proveedor_id)
    header("Location: index.php?success=1");
  } catch (PDOException $e) {
    header("Location: index.php?error=1");
  }
  exit;
}

// Proveedores para el <select> del modal
$proveedores = $productoModel->proveedores();
?>

<!-- Modal: Registrar nuevo producto (se incluye desde index.php) -->
<div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="tituloCrearProducto" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="modules/productos/index.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="crear_producto">

        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="tituloCrearProducto">
            <i class="fas fa-plus me-2"></i>Registrar producto
          </h5>
          <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label for="crear_prod_nombre" class="form-label">Nombre</label>
              <input class="form-control" type="text" id="crear_prod_nombre" name="nombre" required>
            </div>
            <div class="col-md-6">
              <label for="crear_prod_precio" class="form-label">Precio</label>
              <input class="form-control" type="number" id="crear_prod_precio" name="precio" min="0" step="0.01" required>
            </div>
            <div class="col-md-6">
              <label for="crear_prod_stock" class="form-label">Stock</label>
              <input class="form-control" type="number" id="crear_prod_stock" name="stock" min="0" step="1" value="0" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Observaciones</label>
              <textarea name="observaciones" class="form-control" rows="2" maxlength="255"
                placeholder="Opcional"><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
            </div>
            <div class="col-md-12">
              <label for="crear_prod_proveedor" class="form-label">Proveedor</label>
              <select class="form-select" id="crear_prod_proveedor" name="proveedor_id">
                <option value="">-- Sin proveedor --</option>
                <?php foreach ($proveedores as $pr): ?>
                  <option value="<?= $pr->id ?>"><?= htmlspecialchars($pr->nombre) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-12">
              <label for="crear_prod_imagen" class="form-label">Imagen (JPG, PNG o WEBP)</label>
              <input class="form-control" type="file" id="crear_prod_imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Registrar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>