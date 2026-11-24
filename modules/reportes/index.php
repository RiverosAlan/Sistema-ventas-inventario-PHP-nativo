<?php
ob_start();
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Reporte.php";
$reporteModel = new Reporte();

// Rango de fechas: por defecto, los últimos 30 días
$hoy = date('Y-m-d');
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
$hasta = $_GET['hasta'] ?? $hoy;
$fechaValida = fn($f) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $f);
if (!$fechaValida($desde)) $desde = date('Y-m-d', strtotime('-30 days'));
if (!$fechaValida($hasta)) $hasta = $hoy;
if ($desde > $hasta) { [$desde, $hasta] = [$hasta, $desde]; }

$porDia       = $reporteModel->ventasPorDia($desde, $hasta);
$topProductos = $reporteModel->topProductos($desde, $hasta);
$porProveedor = $reporteModel->ventasPorProveedor($desde, $hasta);
$stock        = $reporteModel->stockProductos();

// Datos listos para JavaScript
$datos = [
    'dias'       => array_map(fn($r) => date('d/m', strtotime($r->dia)), $porDia),
    'ventasDia'  => array_map(fn($r) => (float) $r->total, $porDia),
    'prodNombres' => array_map(fn($r) => $r->nombre, $topProductos),
    'prodUnid'   => array_map(fn($r) => (int) $r->unidades, $topProductos),
    'provNombres' => array_map(fn($r) => $r->proveedor, $porProveedor),
    'provTotales' => array_map(fn($r) => (float) $r->total, $porProveedor),
    'stockNombres' => array_map(fn($r) => $r->nombre, $stock),
    'stockCant'  => array_map(fn($r) => (int) $r->stock, $stock),
];

include('../../layout/header.php');
?>

<div class="card sin_margenes mb-4">
  <div id="cabeza" class="card-header bg-dark text-white">
    <h4 class="mb-0">Reportes</h4>
  </div>
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-sm-4 col-md-3">
        <label class="form-label mb-1">Desde</label>
        <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($desde) ?>">
      </div>
      <div class="col-sm-4 col-md-3">
        <label class="form-label mb-1">Hasta</label>
        <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta) ?>">
      </div>
      <div class="col-sm-4 col-md-3">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-filter"></i> Filtrar
        </button>
      </div>
    </form>
    <small class="text-body-secondary d-block mt-2">
      Los tres primeros gráficos usan este rango. El stock siempre muestra el estado actual.
    </small>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header"><strong>Ventas por día</strong> <span class="small ms-1">Total vendido</span></div>
      <div class="card-body">
        <?php if (empty($porDia)) { ?><p class="text-muted text-center my-4">Sin ventas en este rango.</p><?php } ?>
        <div class="c-chart-wrapper"><canvas id="chartVentasDia"></canvas></div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header"><strong>Top 5 productos</strong> <span class="small ms-1">Unidades vendidas</span></div>
      <div class="card-body">
        <?php if (empty($topProductos)) { ?><p class="text-muted text-center my-4">Sin ventas en este rango.</p><?php } ?>
        <div class="c-chart-wrapper"><canvas id="chartTopProductos"></canvas></div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header"><strong>Ventas por proveedor</strong> <span class="small ms-1">Monto vendido</span></div>
      <div class="card-body">
        <?php if (empty($porProveedor)) { ?><p class="text-muted text-center my-4">Sin ventas en este rango.</p><?php } ?>
        <div class="c-chart-wrapper"><canvas id="chartProveedores"></canvas></div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header"><strong>Stock de productos</strong> <span class="small ms-1">Los 10 con menos existencias</span></div>
      <div class="card-body">
        <div class="c-chart-wrapper"><canvas id="chartStock"></canvas></div>
      </div>
    </div>
  </div>
</div>

<?php include('../../layout/footer.php'); ?>

<!-- Librerías de gráficos de la plantilla (las mismas de charts.html) -->
<script src="vendors/chart.js/js/chart.umd.js"></script>
<script src="vendors/@coreui/chartjs/js/coreui-chartjs.js"></script>
<script>
  const D = <?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) ?>;

  // Colores que respetan el tema claro/oscuro
  Chart.defaults.color = getComputedStyle(document.body).color;
  Chart.defaults.borderColor = 'rgba(128,128,128,.2)';
  const paleta = ['#5b6fd6', '#2eb85c', '#f9b115', '#e55353', '#39f', '#9da5b1', '#8e5bd6'];

  // 1. Ventas por día (líneas)
  new Chart(document.getElementById('chartVentasDia'), {
    type: 'line',
    data: {
      labels: D.dias,
      datasets: [{
        label: 'Total vendido',
        data: D.ventasDia,
        borderColor: paleta[0],
        backgroundColor: 'rgba(91,111,214,.15)',
        fill: true,
        tension: .3
      }]
    },
    options: { maintainAspectRatio: true, plugins: { legend: { display: false } } }
  });

  // 2. Top 5 productos (barras horizontales)
  new Chart(document.getElementById('chartTopProductos'), {
    type: 'bar',
    data: {
      labels: D.prodNombres,
      datasets: [{ label: 'Unidades', data: D.prodUnid, backgroundColor: paleta[1], borderRadius: 4 }]
    },
    options: { indexAxis: 'y', maintainAspectRatio: true, plugins: { legend: { display: false } } }
  });

  // 3. Ventas por proveedor (dona)
  new Chart(document.getElementById('chartProveedores'), {
    type: 'doughnut',
    data: {
      labels: D.provNombres,
      datasets: [{ data: D.provTotales, backgroundColor: paleta, borderWidth: 0 }]
    },
    options: { maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
  });

  // 4. Stock (barras; en rojo si hay 5 unidades o menos)
  new Chart(document.getElementById('chartStock'), {
    type: 'bar',
    data: {
      labels: D.stockNombres,
      datasets: [{
        label: 'Stock',
        data: D.stockCant,
        backgroundColor: D.stockCant.map(s => s <= 5 ? '#e55353' : '#2eb85c'),
        borderRadius: 4
      }]
    },
    options: { maintainAspectRatio: true, plugins: { legend: { display: false } } }
  });
</script>