<?php
ob_start(); // permite redirigir desde create.php aunque ya se haya cargado el header
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Venta.php";
require_once "../../clases/Producto.php";
require_once "../../clases/Cliente.php";

// Ruta base del proyecto, calculada desde este archivo: /coreui/modules/ventas/index.php -> /coreui
$BASE = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');

$ventaModel    = new Venta();
$productoModel = new Producto();
$clienteModel  = new Cliente();

// Permisos: ajusta estas dos líneas según lo que signifique cada nivel
$puedeVender   = in_array($UserConnected->nivel, ['A', 'O']);
$puedeEliminar = ($UserConnected->nivel === 'A');

// Filtro opcional por rango de fechas
$esFecha = fn($f) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) === 1;
$desde = $esFecha($_GET['desde'] ?? '') ? $_GET['desde'] : '';
$hasta = $esFecha($_GET['hasta'] ?? '') ? $_GET['hasta'] : '';

$ventas    = $ventaModel->listar($desde ?: null, $hasta ?: null)->fetchAll(PDO::FETCH_OBJ);
$productos = $productoModel->read()->fetchAll(PDO::FETCH_OBJ);
$clientes  = $clienteModel->read()->fetchAll(PDO::FETCH_OBJ);

// Agrupar las ventas por día
$porDia = [];
$totalGeneral = 0;
foreach ($ventas as $v) {
    $porDia[date('Y-m-d', strtotime($v->fecha))][] = $v;
    $totalGeneral += $v->total;
}

include('../../layout/header.php');
?>

<div class="row">
    <div class="col-md-12">
        <div class="card sin_margenes">
            <div id="cabeza" class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Ventas</h4>
                <?php if ($puedeVender) { ?>
                    <button type="button" class="btn btn-success" data-coreui-toggle="modal" data-coreui-target="#modalNuevaVenta">
                        <i class="fas fa-cart-plus"></i> Nueva venta
                    </button>
                <?php } ?>
            </div>
            <div class="card-body">

                <!-- Filtro por fechas -->
                <form method="GET" action="modules/ventas/index.php" class="row g-2 align-items-end mb-3">
                    <div class="col-auto">
                        <label for="filtro_desde" class="form-label mb-0 small">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="filtro_desde" name="desde" value="<?= htmlspecialchars($desde) ?>">
                    </div>
                    <div class="col-auto">
                        <label for="filtro_hasta" class="form-label mb-0 small">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="filtro_hasta" name="hasta" value="<?= htmlspecialchars($hasta) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                        <?php if ($desde || $hasta) { ?>
                            <a href="modules/ventas/index.php" class="btn btn-sm btn-outline-secondary">Quitar filtro</a>
                        <?php } ?>
                    </div>
                    <div class="col-auto ms-md-auto">
                        <button type="button" class="btn btn-sm btn-success" id="btnExportExcel" <?= empty($ventas) ? 'disabled' : '' ?>>
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="btnExportPdf" <?= empty($ventas) ? 'disabled' : '' ?>>
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>N°</th>
                                <th>HORA</th>
                                <th>CLIENTE</th>
                                <th>VENDEDOR</th>
                                <th class="text-end">TOTAL</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ventas)) { ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay ventas registradas.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($porDia as $dia => $ventasDia) { ?>
                                <tr class="table-secondary">
                                    <td colspan="6">
                                        <strong><i class="far fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($dia)) ?></strong>
                                        <span class="text-black ms-2">
                                            <?= count($ventasDia) ?> venta(s) · Total del día: <strong><?= number_format(array_sum(array_column($ventasDia, 'total')), 2) ?></strong>
                                        </span>
                                    </td>
                                </tr>

                                <?php foreach ($ventasDia as $row) { ?>
                                    <tr>
                                        <td><?= $row->id ?></td>
                                        <td><?= date('H:i', strtotime($row->fecha)) ?></td>
                                        <td><strong><?= htmlspecialchars($row->cliente) ?></strong></td>
                                        <td><?= htmlspecialchars($row->vendedor) ?></td>
                                        <td class="text-end"><?= number_format((float) $row->total, 2) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm text-white btn-ver-venta"
                                                data-coreui-toggle="modal" data-coreui-target="#modalDetalleVenta" data-id="<?= $row->id ?>">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm btn-recibo-venta"
                                                data-coreui-toggle="modal" data-coreui-target="#modalRecibo" data-id="<?= $row->id ?>">
                                                <i class="fas fa-print"></i> Recibo
                                            </button>
                                            <?php if ($puedeEliminar) { ?>
                                                <a href="modules/ventas/delete.php?id=<?= $row->id ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Eliminar la venta N° <?= $row->id ?>? El stock de sus productos será devuelto al inventario.')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                        <?php if (!empty($ventas)) { ?>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end fw-bold">TOTAL <?= ($desde || $hasta) ? 'DEL PERÍODO' : 'GENERAL' ?></td>
                                    <td class="text-end fw-bold"><?= number_format($totalGeneral, 2) ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Paginación -->
<div id="paginacionVentas" class="d-flex flex-column flex-md-row
     justify-content-between align-items-center gap-3 mt-3">

    <div class="text-muted small" id="infoPaginacion">
        Mostrando ventas...
    </div>

    <nav aria-label="Paginación de ventas">
        <ul class="pagination pagination-sm mb-0" id="controlesPaginacion">
        </ul>
    </nav>

</div>



<!-- Modal único de detalle (se llena por AJAX con detalle.php) -->
<div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-labelledby="tituloDetalleVenta" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="tituloDetalleVenta"><i class="fas fa-receipt me-2"></i>Detalle de la venta</h5>
                <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleVentaBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal: Recibo de venta (vista previa, imprimir y guardar PDF) -->
<div class="modal fade" id="modalRecibo" tabindex="-1" aria-labelledby="tituloRecibo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="tituloRecibo"><i class="fas fa-receipt me-2"></i>Recibo de venta</h5>
                <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="reciboCuerpo"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnReciboImprimir" disabled>
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-danger" id="btnReciboPdf" disabled>
                    <i class="fas fa-file-pdf"></i> Guardar PDF
                </button>
            </div>
        </div>
    </div>
</div>



<?php
if ($puedeVender) {
    include "create.php"; // modal del carrito + lógica de registro
}
?>

<?php include('../../layout/footer.php'); ?>

<?php
// Datos para exportar: se calculan en PHP y se entregan al JS como JSON
$periodoTxt = ($desde || $hasta)
    ? 'Del ' . ($desde ? date('d/m/Y', strtotime($desde)) : 'inicio') . ' al ' . ($hasta ? date('d/m/Y', strtotime($hasta)) : 'hoy')
    : 'Todas las ventas';

$sufijoArchivo = ($desde || $hasta)
    ? ($desde ?: 'inicio') . '_a_' . ($hasta ?: 'hoy')
    : 'todas';

$ventasExport = array_map(function ($v) {
    return [
        'id'       => (int) $v->id,
        'fecha'    => date('d/m/Y', strtotime($v->fecha)),
        'hora'     => date('H:i', strtotime($v->fecha)),
        'cliente'  => $v->cliente,
        'vendedor' => $v->vendedor,
        'total'    => (float) $v->total,
    ];
}, $ventas);
?>

<!-- Librerías de exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>



<script>
    window.BASE_URL = <?= json_encode($BASE) ?>;
    window.EXPORT_VENTAS = <?= json_encode([
                                'periodo' => $periodoTxt,
                                'archivo' => 'ventas_' . $sufijoArchivo,
                                'ventas'  => $ventasExport,
                            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= $BASE ?>/modules/ventas/js/paginacionVentas.js"></script>
<script src="<?= $BASE ?>/modules/ventas/js/ventas-utils.js"></script>
<script src="<?= $BASE ?>/modules/ventas/js/ventas-detalle.js"></script>
<script src="<?= $BASE ?>/modules/ventas/js/ventas-excel.js"></script>
<script src="<?= $BASE ?>/modules/ventas/js/ventas-pdf.js"></script>
<script src="<?= $BASE ?>/modules/ventas/js/ventas-recibo.js"></script>




<style>
    /* Paginación de ventas */
    #paginacionVentas {
        padding: 12px 0;
        border-top: 1px solid #e9ecef;
    }

    #controlesPaginacion .page-link {
        border-radius: 8px;
        margin: 0 3px;
        color: #495057;
        border: 1px solid #e9ecef;
        min-width: 34px;
        text-align: center;
        transition: all 0.2s ease;
    }

    #controlesPaginacion .page-item.active .page-link {
        background-color: #321fdb;
        border-color: #321fdb;
        color: white;
    }

    #controlesPaginacion .page-link:hover {
        background-color: #f0efff;
        color: #321fdb;
    }

    #controlesPaginacion .page-item.disabled .page-link {
        background-color: #f8f9fa;
        color: #adb5bd;
        cursor: not-allowed;
    }

    @media (max-width: 576px) {
        #paginacionVentas {
            align-items: center;
        }

        #controlesPaginacion {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>