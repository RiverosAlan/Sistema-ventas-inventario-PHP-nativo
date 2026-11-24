<?php
// Lógica de registro (este archivo se incluye desde index.php, que ya tiene $clienteModel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear_cliente') {
  $camposObligatorios = ['nom_comercial', 'nom_legal', 'nit'];

  foreach ($camposObligatorios as $campo) {
    if (trim($_POST[$campo] ?? '') === '') {
      header("Location: index.php?error=1");
      exit;
    }
  }

  try {
    $clienteModel->create();   // lee los datos de $_POST internamente
    header("Location: index.php?success=1");
  } catch (PDOException $e) {
    header("Location: index.php?error=1");
  }
  exit;
}
?>

<!-- Modal: Registrar nuevo cliente (se incluye desde index.php) -->
<div class="modal fade" id="modalCrearCliente" tabindex="-1" aria-labelledby="tituloCrearCliente" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="modules/clientes/index.php" method="POST">
        <input type="hidden" name="accion" value="crear_cliente">

        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="tituloCrearCliente">
            <i class="fas fa-plus me-2"></i>Registrar cliente
          </h5>
          <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="crear_nom_comercial" class="form-label">Nombre comercial</label>
              <input class="form-control" type="text" id="crear_nom_comercial" name="nom_comercial" required>
            </div>
            <div class="col-md-6">
              <label for="crear_nom_legal" class="form-label">Nombre legal</label>
              <input class="form-control" type="text" id="crear_nom_legal" name="nom_legal" required>
            </div>
            <div class="col-md-6">
              <label for="crear_nit" class="form-label">NIT</label>
              <input class="form-control" type="text" id="crear_nit" name="nit" required>
            </div>
            <div class="col-md-6">
              <label for="crear_telf" class="form-label">Teléfono</label>
              <input class="form-control" type="text" id="crear_telf" name="telf">
            </div>
            <div class="col-md-8">
              <label for="crear_direccion" class="form-label">Dirección</label>
              <input class="form-control" type="text" id="crear_direccion" name="direccion">
            </div>
            <div class="col-md-4">
              <label for="crear_rubro" class="form-label">Rubro</label>
              <input class="form-control" type="text" id="crear_rubro" name="Rubro">
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