<?php
ob_start(); // permite redirigir desde create.php / edit.php aunque ya se haya cargado el header
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Proveedor.php";
$proveedorModel = new Proveedor();
$proveedores = $proveedorModel->read()->fetchAll(PDO::FETCH_OBJ);

// A y O pueden crear, editar y eliminar; V solo consulta
$puedeGestionar = in_array($UserConnected->nivel, ['A', 'O']);

include('../../layout/header.php');
?>

<div class="row">
    <div class="col-md-12">
        <div class="card sin_margenes">
            <div id="cabeza" class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Proveedores</h4>
                <?php if ($puedeGestionar) { ?>
                    <button type="button" class="btn btn-success" data-coreui-toggle="modal" data-coreui-target="#modalCrearProveedor">
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
                                <th>NOMBRE</th>
                                <th>NIT</th>
                                <th>REGIMEN</th>
                                <th>CATEGORIA</th>
                                <th>RUBRO</th>
                                <th>FECHA EXPIRACIÓN</th>
                                <?php if ($puedeGestionar) { ?>
                                    <th>ACCIONES</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($proveedores)) { ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No hay proveedores registrados.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($proveedores as $row) { ?>
                                <tr>
                                    <td><?= $row->id ?></td>
                                    <td><strong><?= htmlspecialchars($row->nombre) ?></strong></td>
                                    <td><?= htmlspecialchars($row->nit) ?></td>
                                    <td><?= Proveedor::regimen($row->regimen) ?></td>
                                    <td><?= Proveedor::categoria($row->categoria) ?></td>
                                    <td><?= htmlspecialchars($row->rubro) ?></td>
                                    <td><?= fecha($row->fecha) ?></td>
                                    <?php if ($puedeGestionar) { ?>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm text-white" data-coreui-toggle="modal" data-coreui-target="#modalEditarProveedor<?= $row->id ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <?php if ($UserConnected->nivel == 'A') { ?>
                                                <a href="modules/proveedor/delete.php?id=<?= $row->id ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm(<?= htmlspecialchars(json_encode('¿Está seguro de eliminar a ' . $row->nombre . '?'), ENT_QUOTES) ?>)">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            <?php } ?>
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

<?php if (($_GET['delete_error'] ?? '') === 'productos') { ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        No se puede eliminar el proveedor porque tiene productos asociados.
        Cambia primero el proveedor de esos productos.
        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php } ?>

<?php
if ($puedeGestionar) {
    include "create.php";

    foreach ($proveedores as $row) {
        include "edit.php";
    }
}
?>

<?php include('../../layout/footer.php'); ?>