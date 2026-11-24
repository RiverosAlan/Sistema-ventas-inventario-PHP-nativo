<?php
/** @var object $row */
// $row = proveedor de esta fila (viene del foreach del index)
$esEste = isset($_POST['editar_proveedor']) && (int)($_POST['id'] ?? 0) === (int)$row->id;
$errorEditar = '';

if ($esEste) {
  if (trim($_POST['nombre'] ?? '') === '' || trim($_POST['nit'] ?? '') === '') {
    $errorEditar = 'El nombre y el NIT son obligatorios.';
  } else {
    try {
      $proveedorModel->update($row->id);
      header("Location: index.php?edit_success=1");
      exit;
    } catch (PDOException $e) {
      $errorEditar = 'No se pudo actualizar el proveedor.';
    }
  }
}

// Si hubo error se conserva lo que el usuario escribió; si no, se muestran los datos guardados
$val = fn($k) => $esEste ? ($_POST[$k] ?? '') : ($row->$k ?? '');
$fechaVal = $esEste ? ($_POST['fecha'] ?? '') : (!empty($row->fecha) ? substr($row->fecha, 0, 10) : '');
?>

<div class="modal fade" id="modalEditarProveedor<?= $row->id ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="post">
        <input type="hidden" name="editar_proveedor" value="1">
        <input type="hidden" name="id" value="<?= $row->id ?>">
        <!-- presupuesto no se edita aquí: se reenvía el valor actual para no sobrescribirlo con 0 -->
        <input type="hidden" name="presupuesto" value="<?= htmlspecialchars($row->presupuesto ?? 0) ?>">

        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title">Editar proveedor</h5>
          <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <?php if ($errorEditar) { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorEditar) ?></div>
          <?php } ?>

          <div class="row">
            <div class="col-md-8 mb-3">
              <label class="form-label">Nombre</label>
              <input class="form-control" type="text" name="nombre" required
                value="<?= htmlspecialchars($val('nombre')) ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">NIT</label>
              <input class="form-control" type="text" name="nit" required
                value="<?= htmlspecialchars($val('nit')) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Régimen</label>
            <?php foreach (Proveedor::$regimenes as $cod => $txt) { ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="regimen"
                  id="editRegimen<?= $row->id . $cod ?>" value="<?= $cod ?>"
                  <?= (string)$val('regimen') === (string)$cod ? 'checked' : '' ?>>
                <label class="form-check-label" for="editRegimen<?= $row->id . $cod ?>"><?= $txt ?></label>
              </div>
            <?php } ?>
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Categoría</label>
            <?php foreach (Proveedor::$categorias as $cod => $txt) { ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="categoria"
                  id="editCat<?= $row->id . $cod ?>" value="<?= $cod ?>"
                  <?= (string)$val('categoria') === (string)$cod ? 'checked' : '' ?>>
                <label class="form-check-label" for="editCat<?= $row->id . $cod ?>"><?= $txt ?></label>
              </div>
            <?php } ?>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Rubro</label>
              <select name="rubro" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php
                // Si el proveedor tiene un rubro viejo que ya no está en la lista, se conserva como opción
                $rubroActual = $val('rubro');
                if ($rubroActual !== '' && !isset(Proveedor::$rubros[$rubroActual])) { ?>
                  <option value="<?= htmlspecialchars($rubroActual) ?>" selected><?= htmlspecialchars($rubroActual) ?></option>
                <?php } ?>
                <?php foreach (Proveedor::$rubros as $cod => $txt) { ?>
                  <option value="<?= $cod ?>" <?= $rubroActual === $cod ? 'selected' : '' ?>><?= $txt ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha de expiración</label>
              <input class="form-control" type="date" name="fecha" value="<?= htmlspecialchars($fechaVal) ?>">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning text-white">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if ($errorEditar) { ?>
  <script>
    // Si hubo error, reabre el modal de esta fila
    window.addEventListener('load', function () {
      new coreui.Modal(document.getElementById('modalEditarProveedor<?= $row->id ?>')).show();
    });
  </script>
<?php } ?>