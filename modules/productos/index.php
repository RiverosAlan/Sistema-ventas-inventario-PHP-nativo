<?php
ob_start(); // permite redirigir desde create.php / edit.php aunque ya se haya cargado el header
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Producto.php";
$productoModel = new Producto();
$productos = $productoModel->read()->fetchAll(PDO::FETCH_OBJ);

// A y O pueden crear, editar y eliminar; V solo consulta
$puedeGestionar = in_array($UserConnected->nivel, ['A', 'O']);

include('../../layout/header.php');
?>

<div class="row">
  <div class="col-md-12">
    <div class="card sin_margenes">
      <div id="cabeza" class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Productos</h4>
        <?php if ($puedeGestionar) { ?>
          <button type="button" class="btn btn-success" data-coreui-toggle="modal" data-coreui-target="#modalCrearProducto">
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
                <th>IMAGEN</th>
                <th>NOMBRE</th>
                <th>PROVEEDOR</th>
                <th>PRECIO</th>
                <th>STOCK</th>
                <th>OBSERVACIONES</th>
                <?php if ($puedeGestionar) { ?>
                  <th>ACCIONES</th>
                <?php } ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($productos)) { ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No hay productos registrados.</td>
                </tr>
              <?php } ?>

              <?php foreach ($productos as $row) { ?>
                <tr>
                  <td><?= $row->id ?></td>
                  <td>
                    <?php if (!empty($row->imagen_url)) { ?>
                      <img src="<?= htmlspecialchars($row->imagen_url, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($row->nombre, ENT_QUOTES) ?>"
                        class="rounded border" style="width:48px;height:48px;object-fit:cover;">
                    <?php } else { ?>
                      <div class="rounded border bg-light text-muted d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="fas fa-image"></i>
                      </div>
                    <?php } ?>
                  </td>
                  <td><strong><?= htmlspecialchars($row->nombre) ?></strong></td>
                  <td>
                    <?php if (!empty($row->proveedor_nombre)) { ?>
                      <?= htmlspecialchars($row->proveedor_nombre) ?>
                    <?php } else { ?>
                      <span class="text-muted">Sin proveedor</span>
                    <?php } ?>
                  </td>
                  <td><?= number_format((float) $row->precio, 2) ?></td>
                  <td>
                    <span class="badge <?= $row->stock <= 5 ? 'bg-danger' : 'bg-success' ?>"><?= (int) $row->stock ?></span>
                  </td>
                  <td style="max-width:420px;">
                    <?php if (!empty($row->observaciones)) { ?>
                      <span class="d-inline-block text-truncate" style="max-width:420px;"
                        title="<?= htmlspecialchars($row->observaciones, ENT_QUOTES) ?>">
                        <?= htmlspecialchars($row->observaciones) ?>
                      </span>
                    <?php } else { ?>
                      <span class="text-muted">—</span>
                    <?php } ?>
                  </td>
                  <?php if ($puedeGestionar) { ?>
                    <td>
                      <a href="modules/productos/pdf.php?id=<?= $row->id ?>" target="_blank" class="btn btn-info btn-sm text-white">
                        <i class="fas fa-file-pdf"></i> PDF
                      </a>
                      <button type="button" class="btn btn-warning btn-sm text-white" data-coreui-toggle="modal" data-coreui-target="#modalEditarProducto<?= $row->id ?>">
                        <i class="fas fa-edit"></i> Editar
                      </button>
                      <a href="modules/productos/delete.php?id=<?= $row->id ?>" class="btn btn-danger btn-sm"
                        onclick="return confirm(<?= htmlspecialchars(json_encode('¿Está seguro de eliminar ' . $row->nombre . '?'), ENT_QUOTES) ?>)">
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

<?php
if ($puedeGestionar) {
  include "create.php";

  foreach ($productos as $row) {
    include "edit.php";
  }
}
?>

<?php include('../../layout/footer.php'); ?>