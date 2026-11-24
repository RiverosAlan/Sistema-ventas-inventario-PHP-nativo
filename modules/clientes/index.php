<?php
ob_start();
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Cliente.php";
$clienteModel = new Cliente();
$clientes = $clienteModel->read()->fetchAll(PDO::FETCH_OBJ);

// A y O pueden crear, editar y eliminar; V solo consulta
$puedeGestionar = in_array($UserConnected->nivel, ['A', 'O']);

include('../../layout/header.php');
?>

<div class="row">
  <div class="col-md-12">
    <div class="card sin_margenes">
      <div id="cabeza" class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Clientes</h4>
        <?php if ($puedeGestionar) { ?>
          <button type="button" class="btn btn-success" data-coreui-toggle="modal" data-coreui-target="#modalCrearCliente">
            <i class="fas fa-plus"></i> Adicionar
          </button>
        <?php } ?>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>NOMBRE COMERCIAL</th>
                <th>NOMBRE LEGAL</th>
                <th>NIT</th>
                <th>DIRECCIÓN</th>
                <th>TELÉFONO</th>
                <th>RUBRO</th>
                <?php if ($puedeGestionar) { ?>
                  <th>ACCIONES</th>
                <?php } ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($clientes)) { ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No hay clientes registrados.</td>
                </tr>
              <?php } ?>

              <?php foreach ($clientes as $row) { ?>
                <tr>
                  <td><?= $row->id ?></td>
                  <td><strong><?= htmlspecialchars($row->nom_comercial) ?></strong></td>
                  <td><?= htmlspecialchars($row->nom_legal) ?></td>
                  <td><?= htmlspecialchars($row->nit) ?></td>
                  <td><?= htmlspecialchars($row->direccion) ?></td>
                  <td><?= htmlspecialchars($row->telf) ?></td>
                  <td><span class="badge bg-secondary"><?= htmlspecialchars($row->Rubro) ?></span></td>
                  <?php if ($puedeGestionar) { ?>
                    <td>
                      <button type="button" class="btn btn-warning btn-sm text-white" data-coreui-toggle="modal" data-coreui-target="#modalEditarCliente<?= $row->id ?>">
                        <i class="fas fa-edit"></i> Editar
                      </button>
                      <a href="modules/clientes/delete.php?id=<?= $row->id ?>" class="btn btn-danger btn-sm"
                        onclick="return confirm(<?= htmlspecialchars(json_encode('¿Está seguro de eliminar a ' . $row->nom_comercial . '?'), ENT_QUOTES) ?>)">
                        <i class="fas fa-trash"></i> Eliminar
                      </a>
                    </td>
                  <?php } ?>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (($_GET['delete_error'] ?? '') === 'ventas') { ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    No se puede eliminar el cliente porque tiene ventas registradas.
    <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Cerrar"></button>
  </div>
<?php } ?>

<?php
if ($puedeGestionar) {
  // Modal de creación (aislado)
  include "create.php";

  // Modales de edición: se generan fuera de la tabla para mantener el HTML válido
  foreach ($clientes as $row) {
    include "edit.php";
  }
}
?>

<?php include('../../layout/footer.php'); ?>