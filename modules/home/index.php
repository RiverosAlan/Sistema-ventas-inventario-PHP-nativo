<?php
include('../../config/conexion.php');
include('../../layout/header.php');

// Totales del panel (si algo falla, el panel igual carga con ceros)
$totales = ['ventas' => 0, 'productos' => 0, 'clientes' => 0, 'proveedores' => 0, 'stock_bajo' => 0];
try {
    $dbHome = new Database();
    $cn = $dbHome->getConnection();
    $totales['ventas']      = (int) $cn->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
    $totales['productos']   = (int) $cn->query("SELECT COUNT(*) FROM productos")->fetchColumn();
    $totales['clientes']    = (int) $cn->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $totales['proveedores'] = (int) $cn->query("SELECT COUNT(*) FROM proveedor")->fetchColumn();
    $totales['stock_bajo']  = (int) $cn->query("SELECT COUNT(*) FROM productos WHERE stock <= 5")->fetchColumn();
} catch (Exception $e) {
    // se mantienen los ceros
}

// Fecha en español
$dias  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaHoy = ucfirst($dias[date('w')]) . ', ' . date('j') . ' de ' . $meses[date('n') - 1] . ' de ' . date('Y');

// Módulos: icono, color, título, descripción y enlace
$modulos = [
    ['fas fa-shopping-cart', 'primary', 'Ventas',      'Registra nuevas ventas y consulta el historial de transacciones.', 'modules/ventas/index.php'],
    ['fas fa-box',           'info',    'Productos',   'Administra precios, existencias e inventario.',                    'modules/productos/index.php'],
    ['fas fa-users',         'success', 'Clientes',    'Registra y administra la información de tus clientes.',            'modules/clientes/index.php'],
    ['fas fa-truck',         'danger',  'Proveedores', 'Gestiona los proveedores que abastecen tu almacén.',               'modules/proveedor/index.php'],
    ['fas fa-user-cog',      'warning', 'Usuarios',    'Administra las cuentas y permisos del sistema.',                   'modules/users/index.php'],
];
?>

<style>
    .home-hero {
        background: linear-gradient(135deg, #1e2a4a 0%, #121a30 100%);
        position: relative;
        overflow: hidden;
    }
    .home-hero::before,
    .home-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, .07);
    }
    .home-hero::before { width: 260px; height: 260px; top: -110px; right: -60px; }
    .home-hero::after  { width: 160px; height: 160px; bottom: -80px; right: 190px; }

    .home-stat .icono {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .home-modulo {
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .home-modulo:hover {
        transform: translateY(-4px);
        box-shadow: 0 .75rem 1.5rem rgba(0, 0, 0, .12) !important;
    }
    .home-modulo .flecha {
        transition: transform .2s ease;
    }
    .home-modulo:hover .flecha {
        transform: translateX(4px);
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Bienvenida -->
    <div class="card home-hero border-0 text-white mb-4 shadow">
        <div class="card-body p-4 p-md-5 position-relative" style="z-index:1;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge rounded-pill bg-white bg-opacity-25 text-white fw-normal px-3 py-2 mb-3">
                        <i class="far fa-calendar-alt me-1"></i> <?= $fechaHoy ?>
                    </span>

                    <h2 class="fw-bold mb-2">¡Bienvenido al sistema!</h2>

                    <p class="mb-0 text-white-50 fs-6">
                        Administra tus ventas, productos, clientes y proveedores
                        desde un solo lugar.
                    </p>
                </div>

                <div class="col-md-4 text-md-end mt-4 mt-md-0">
                    <a href="modules/ventas/index.php"
                        class="btn btn-light btn-lg text-primary fw-semibold shadow-sm px-4">
                        <i class="fas fa-cart-plus me-2"></i>
                        Nueva venta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row g-3 mb-4">
        <?php
        $stats = [
            ['fas fa-receipt',  'primary', 'Ventas',      $totales['ventas']],
            ['fas fa-box-open', 'info',    'Productos',   $totales['productos']],
            ['fas fa-users',    'success', 'Clientes',    $totales['clientes']],
            ['fas fa-truck',    'danger',  'Proveedores', $totales['proveedores']],
        ];
        foreach ($stats as $s) { ?>
            <div class="col-6 col-xl-3">
                <div class="card home-stat border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 p-3 p-md-4">
                        <div class="icono bg-<?= $s[1] ?> bg-opacity-10 text-<?= $s[1] ?>">
                            <i class="<?= $s[0] ?> fs-5"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold lh-1"><?= number_format($s[3]) ?></div>
                            <small class="text-body-secondary"><?= $s[2] ?></small>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Aviso de stock bajo (solo si corresponde) -->
    <?php if ($totales['stock_bajo'] > 0) { ?>
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle fs-5"></i>
            <div class="flex-grow-1">
                <strong><?= $totales['stock_bajo'] ?></strong>
                producto<?= $totales['stock_bajo'] == 1 ? '' : 's' ?> con stock bajo (5 unidades o menos).
            </div>
            <a href="modules/productos/index.php" class="btn btn-sm btn-warning text-white">Revisar</a>
        </div>
    <?php } ?>

    <!-- Título -->
    <div class="mb-3">
        <h5 class="fw-bold mb-1">Accesos rápidos</h5>
        <small class="text-body-secondary">
            Selecciona el módulo que deseas gestionar.
        </small>
    </div>

    <!-- Módulos -->
    <div class="row g-3">
        <?php foreach ($modulos as $m) { ?>
            <div class="col-12 col-sm-6 col-xl">
                <a href="<?= $m[4] ?>" class="card home-modulo h-100 text-decoration-none shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="bg-<?= $m[1] ?> bg-opacity-10 text-<?= $m[1] ?> rounded-3 d-inline-flex p-3 mb-3">
                            <i class="<?= $m[0] ?> fs-4"></i>
                        </div>

                        <h6 class="fw-bold text-body"><?= $m[2] ?></h6>

                        <p class="text-body-secondary small mb-3"><?= $m[3] ?></p>

                        <span class="text-<?= $m[1] ?> small fw-semibold">
                            Ir al módulo <i class="fas fa-arrow-right ms-1 flecha"></i>
                        </span>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>

    <!-- Información -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body d-flex align-items-center gap-3 p-3">
            <i class="fas fa-info-circle text-primary fs-4"></i>
            <div>
                <div class="fw-semibold small">Sistema de gestión de ventas</div>
                <small class="text-body-secondary">
                    Selecciona una opción del panel para comenzar.
                </small>
            </div>
        </div>
    </div>

</div>

<?php
include('../../layout/footer.php');
?>