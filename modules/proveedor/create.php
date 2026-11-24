<?php
$errorCrear = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_proveedor'])) {
  if (trim($_POST['nombre'] ?? '') === '' || trim($_POST['nit'] ?? '') === '') {
    $errorCrear = 'El nombre y el NIT son obligatorios.';
  } else {
    try {
      $proveedorModel->create();
      header("Location: index.php?success=1");
      exit;
    } catch (PDOException $e) {
      $errorCrear = 'No se pudo registrar el proveedor.';
    }
  }
}
?>

<div class="modal fade" id="modalCrearProveedor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="post">
        <input type="hidden" name="crear_proveedor" value="1">

        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title">Nuevo proveedor</h5>
          <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <?php if ($errorCrear) { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorCrear) ?></div>
          <?php } ?>

          <div class="row">
            <div class="col-md-8 mb-3">
              <label class="form-label">Nombre</label>
              <input class="form-control" type="text" name="nombre" required
                value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">NIT</label>
              <input class="form-control" type="text" name="nit" required
                value="<?= htmlspecialchars($_POST['nit'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Régimen</label>
            <?php $i = 0; foreach (Proveedor::$regimenes as $cod => $txt) { $i++; ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="regimen" id="crearRegimen<?= $cod ?>"
                  value="<?= $cod ?>" <?= $i === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="crearRegimen<?= $cod ?>"><?= $txt ?></label>
              </div>
            <?php } ?>
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Categoría</label>
            <?php $i = 0; foreach (Proveedor::$categorias as $cod => $txt) { $i++; ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="categoria" id="crearCat<?= $cod ?>"
                  value="<?= $cod ?>" <?= $i === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="crearCat<?= $cod ?>"><?= $txt ?></label>
              </div>
            <?php } ?>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Rubro</label>
              <select name="rubro" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach (Proveedor::$rubros as $cod => $txt) { ?>
                  <option value="<?= $cod ?>"><?= $txt ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha de expiración</label>
              <input class="form-control" type="date" name="fecha">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Registrar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if ($errorCrear) { ?>
  <script>
    // Si hubo error, reabre el modal para que el usuario lo vea
    window.addEventListener('load', function () {
      new coreui.Modal(document.getElementById('modalCrearProveedor')).show();
    });
  </script>
<?php } ?>