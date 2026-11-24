<?php
/** @var ..\..\clases $row */
// Lógica de edición (se incluye desde index.php; $clienteModel ya existe)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar_cliente') {
  $id = (int) ($_POST['id'] ?? 0);

  $camposObligatorios = ['nom_comercial', 'nom_legal', 'nit'];
  $faltaCampo = false;
  foreach ($camposObligatorios as $campo) {
    if (trim($_POST[$campo] ?? '') === '') {
      $faltaCampo = true;
      break;
    }
  }

  if ($id <= 0 || $faltaCampo) {
    header("Location: index.php?error=1");
    exit;
  }

  try {
    $clienteModel->update($id);   // lee los datos de $_POST internamente
    header("Location: index.php?edit_success=1");
  } catch (PDOException $e) {
    header("Location: index.php?error=1");
  }
  exit;
}
?>

<!-- Modal: Editar cliente (uno por fila; $row viene del foreach de index.php) -->
<div class="modal fade" id="modalEditarCliente<?= $row->id ?>" tabindex="-1" aria-labelledby="tituloEditarCliente<?= $row->id ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="modules/clientes/index.php" method="POST">
        <input type="hidden" name="accion" value="editar_cliente">
        <input type="hidden" name="id" value="<?= $row->id ?>">

        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title" id="tituloEditarCliente<?= $row->id ?>">
            <i class="fas fa-edit me-2"></i>Editar cliente
          </h5>
          <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="editar_nom_comercial<?= $row->id ?>" class="form-label">Nombre comercial</label>
              <input class="form-control" type="text" id="editar_nom_comercial<?= $row->id ?>" name="nom_comercial"
                     value="<?= htmlspecialchars($row->nom_comercial, ENT_QUOTES) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="editar_nom_legal<?= $row->id ?>" class="form-label">Nombre legal</label>
              <input class="form-control" type="text" id="editar_nom_legal<?= $row->id ?>" name="nom_legal"
                     value="<?= htmlspecialchars($row->nom_legal, ENT_QUOTES) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="editar_nit<?= $row->id ?>" class="form-label">NIT</label>
              <input class="form-control" type="text" id="editar_nit<?= $row->id ?>" name="nit"
                     value="<?= htmlspecialchars($row->nit, ENT_QUOTES) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="editar_telf<?= $row->id ?>" class="form-label">Teléfono</label>
              <input class="form-control" type="text" id="editar_telf<?= $row->id ?>" name="telf"
                     value="<?= htmlspecialchars($row->telf, ENT_QUOTES) ?>">
            </div>
            <div class="col-md-8">
              <label for="editar_direccion<?= $row->id ?>" class="form-label">Dirección</label>
              <input class="form-control" type="text" id="editar_direccion<?= $row->id ?>" name="direccion"
                     value="<?= htmlspecialchars($row->direccion, ENT_QUOTES) ?>">
            </div>
            <div class="col-md-4">
              <label for="editar_rubro<?= $row->id ?>" class="form-label">Rubro</label>
              <input class="form-control" type="text" id="editar_rubro<?= $row->id ?>" name="Rubro"
                     value="<?= htmlspecialchars($row->Rubro, ENT_QUOTES) ?>">
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