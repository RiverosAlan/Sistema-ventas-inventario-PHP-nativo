<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Venta.php";
$ventaModel = new Venta();

$id    = (int) ($_GET['id'] ?? 0);
$venta = $id > 0 ? $ventaModel->get($id) : null;

if (!$venta) {
    http_response_code(404);
    echo '<div class="alert alert-warning mb-0">No se encontró la venta.</div>';
    exit;
}

$detalle = $ventaModel->getDetalle($id);
?>
<div class="row mb-3">
  <div class="col-md-6">
    <div class="text-muted small">Venta N°</div>
    <div class="fw-bold fs-5"><?= $venta->id ?></div>
  </div>
  <div class="col-md-6">
    <div class="text-muted small">Fecha</div>
    <div class="fw-semibold"><?= date('d/m/Y H:i', strtotime($venta->fecha)) ?></div>
  </div>
  <div class="col-md-6 mt-2">
    <div class="text-muted small">Cliente</div>
    <div class="fw-semibold"><?= htmlspecialchars($venta->cliente) ?></div>
  </div>
  <div class="col-md-6 mt-2">
    <div class="text-muted small">Vendedor</div>
    <div class="fw-semibold"><?= htmlspecialchars($venta->vendedor) ?></div>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>PRODUCTO</th>
        <th class="text-center">CANT.</th>
        <th class="text-end">P. UNIT.</th>
        <th class="text-end">SUBTOTAL</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($detalle as $d) { ?>
        <tr>
          <td>
            <?php if (!empty($d->imagen_url)) { ?>
              <img src="<?= htmlspecialchars($d->imagen_url, ENT_QUOTES) ?>" alt="" class="rounded border me-2"
                   style="width:36px;height:36px;object-fit:cover;">
            <?php } ?>
            <?= htmlspecialchars($d->producto) ?>
          </td>
          <td class="text-center"><?= (int) $d->cantidad ?></td>
          <td class="text-end"><?= number_format((float) $d->precio_unitario, 2) ?></td>
          <td class="text-end"><?= number_format($d->cantidad * $d->precio_unitario, 2) ?></td>
        </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr class="table-light">
        <td colspan="3" class="text-end fw-bold">TOTAL</td>
        <td class="text-end fw-bold"><?= number_format((float) $venta->total, 2) ?></td>
      </tr>
    </tfoot>
  </table>
</div>