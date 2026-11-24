<?php

/** @var object $row */

// Lógica de edición (se incluye desde index.php; $productoModel ya existe)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar_producto') {
  $id     = (int) ($_POST['id'] ?? 0);
  $nombre = trim($_POST['nombre'] ?? '');
  $precio = $_POST['precio'] ?? '';
  $stock  = $_POST['stock'] ?? '';

  if ($id <= 0 || $nombre === '' || !is_numeric($precio) || $precio < 0 || filter_var($stock, FILTER_VALIDATE_INT) === false || $stock < 0) {
    header("Location: index.php?error=1");
    exit;
  }

  // La imagen nueva es opcional; si se envió debe ser válida
  if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
      header("Location: index.php?error=3");
      exit;
    }
  }

  try {
    $productoModel->update($id);   // conserva la imagen actual si no se sube otra; guarda proveedor_id
    header("Location: index.php?edit_success=1");
  } catch (PDOException $e) {
    header("Location: index.php?error=1");
  }
  exit;
}

// Proveedores para el <select>: se cargan una sola vez aunque haya muchos modales
if (!isset($proveedores)) {
  $proveedores = $productoModel->proveedores();
}
?>

<!-- Modal: Editar producto (uno por fila; $row viene del foreach de index.php) -->
<div class="modal fade" id="modalEditarProducto<?= $row->id ?>" tabindex="-1" aria-labelledby="tituloEditarProducto<?= $row->id ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="modules/productos/index.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="editar_producto">
        <input type="hidden" name="id" value="<?= $row->id ?>">

        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title" id="tituloEditarProducto<?= $row->id ?>">
            <i class="fas fa-edit me-2"></i>Editar producto
          </h5>
          <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label for="editar_prod_nombre<?= $row->id ?>" class="form-label">Nombre</label>
              <input class="form-control" type="text" id="editar_prod_nombre<?= $row->id ?>" name="nombre"
                value="<?= htmlspecialchars($row->nombre, ENT_QUOTES) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="editar_prod_precio<?= $row->id ?>" class="form-label">Precio</label>
              <input class="form-control" type="number" id="editar_prod_precio<?= $row->id ?>" name="precio"
                min="0" step="0.01" value="<?= htmlspecialchars((string) $row->precio, ENT_QUOTES) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="editar_prod_stock<?= $row->id ?>" class="form-label">Stock</label>
              <input class="form-control" type="number" id="editar_prod_stock<?= $row->id ?>" name="stock"
                min="0" step="1" value="<?= (int) $row->stock ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Observaciones</label>
              <textarea name="observaciones" class="form-control" rows="2" maxlength="255"
                placeholder="Opcional"><?= htmlspecialchars($row->observaciones ?? '') ?></textarea>
            </div>
            <div class="col-md-12">
              <label for="editar_prod_proveedor<?= $row->id ?>" class="form-label">Proveedor</label>
              <select class="form-select" id="editar_prod_proveedor<?= $row->id ?>" name="proveedor_id">
                <option value="">-- Sin proveedor --</option>
                <?php foreach ($proveedores as $pr): ?>
                  <option value="<?= $pr->id ?>" <?= ((int) $row->proveedor_id === (int) $pr->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pr->nombre) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label d-block">Imagen actual</label>
              <?php if (!empty($row->imagen_url)) { ?>
                <img src="<?= htmlspecialchars($row->imagen_url, ENT_QUOTES) ?>" alt="Imagen actual"
                  class="rounded border mb-2" style="max-height:120px;max-width:100%;object-fit:cover;">
              <?php } else { ?>
                <p class="text-muted small mb-2">Este producto no tiene imagen.</p>
              <?php } ?>
              <label for="editar_prod_imagen<?= $row->id ?>" class="form-label d-block">Cambiar imagen (opcional)</label>
              <input class="form-control" type="file" id="editar_prod_imagen<?= $row->id ?>" name="imagen" accept=".jpg,.jpeg,.png,.webp">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning text-white">
            <i class="fas fa-save"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>