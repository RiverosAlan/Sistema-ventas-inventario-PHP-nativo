
<?php
ob_start();

require_once "../../config/conexion.php";
require_once "../../config/helpers.php";

$UserConnected = verifySession();

require_once "../../clases/Producto.php";
require_once "../../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$id = (int) ($_GET['id'] ?? 0);

$productoModel = new Producto();
$producto = null;

foreach ($productoModel->read()->fetchAll(PDO::FETCH_OBJ) as $p) {
    if ((int) $p->id === $id) {
        $producto = $p;
        break;
    }
}

if (!$producto) {
    ob_end_clean();
    header("Location: index.php");
    exit;
}

// ================= IMAGEN =================

$imgHtml = '
<div class="no-image">
    <span>Sin imagen disponible</span>
</div>';

if (!empty($producto->imagen_url)) {

    $ruta = __DIR__ . '/../../layout/img/productos/' .
            basename($producto->imagen_url);

    if (is_file($ruta)) {

        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $datos = null;
        $mime = null;

        if ($ext === 'webp') {

            if (function_exists('imagecreatefromwebp')) {

                $im = imagecreatefromwebp($ruta);

                if ($im) {
                    ob_start();
                    imagepng($im);
                    $datos = ob_get_clean();

                    imagedestroy($im);
                    $mime = 'image/png';
                }
            }

        } else {

            $datos = file_get_contents($ruta);

            $mime = ($ext === 'png')
                ? 'image/png'
                : 'image/jpeg';
        }

        if ($datos) {
            $imgHtml = '<img src="data:' . $mime .
                ';base64,' . base64_encode($datos) .
                '" class="product-img">';
        }
    }
}

// ================= DATOS =================

$pId = (int) $producto->id;

$pNombre = htmlspecialchars(
    $producto->nombre,
    ENT_QUOTES,
    'UTF-8'
);

$pProv = !empty($producto->proveedor_nombre)
    ? htmlspecialchars($producto->proveedor_nombre, ENT_QUOTES, 'UTF-8')
    : 'Sin proveedor registrado';

$pPrecio = number_format((float) $producto->precio, 2);

$pStock = (int) $producto->stock;

$pObs = !empty($producto->observaciones)
    ? nl2br(htmlspecialchars($producto->observaciones, ENT_QUOTES, 'UTF-8'))
    : 'No se registraron observaciones para este producto.';

$generado = date('d/m/Y - H:i');

// ================= HTML =================

$html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<style>

@page {
    margin: 25px 30px;
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'DejaVu Sans', sans-serif;
    color: #263445;
    background: #ffffff;
    font-size: 10px;
    margin: 0;
}

/* ENCABEZADO */

.header {
    width: 100%;
    border-bottom: 2px solid #e8edf3;
    padding-bottom: 13px;
    margin-bottom: 22px;
}

.header-left {
    float: left;
}

.header-right {
    float: right;
    text-align: right;
}

.brand {
    font-size: 9px;
    font-weight: bold;
    color: #718096;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.document-title {
    font-size: 19px;
    font-weight: bold;
    color: #172b4d;
}

.document-subtitle {
    color: #94a3b8;
    font-size: 9px;
    margin-top: 4px;
}

.id-label {
    background: #edf4ff;
    color: #3267a8;
    padding: 6px 10px;
    border-radius: 5px;
    font-weight: bold;
    font-size: 10px;
}

.date {
    color: #94a3b8;
    font-size: 8px;
    margin-top: 10px;
}

.clear {
    clear: both;
}

/* CONTENIDO */

.main {
    width: 100%;
}

.left {
    width: 35%;
    float: left;
}

.right {
    width: 61%;
    float: right;
}

/* IMAGEN */

.image-container {
    width: 100%;
    height: 205px;
    background: #f7f9fc;
    border: 1px solid #e8edf3;
    border-radius: 9px;
    text-align: center;
    padding: 10px;
}

.product-img {
    width: 100%;
    height: 180px;
    object-fit: contain;
}

.no-image {
    padding-top: 85px;
    color: #a0aec0;
    font-size: 10px;
}

/* INFORMACIÓN */

.category-label {
    color: #3182ce;
    font-size: 8px;
    font-weight: bold;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.product-name {
    color: #172b4d;
    font-size: 22px;
    font-weight: bold;
    line-height: 1.25;
    margin: 0 0 12px 0;
}

.provider-label {
    color: #94a3b8;
    font-size: 9px;
    margin-bottom: 4px;
}

.provider-name {
    color: #344256;
    font-size: 11px;
    font-weight: bold;
}

/* TARJETAS */

.stats {
    width: 100%;
    margin-top: 22px;
    border-spacing: 7px 0;
    margin-left: -7px;
    margin-bottom: 20px;
}

.stat {
    background: #f7f9fc;
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 12px;
    width: 50%;
}

.stat-label {
    color: #94a3b8;
    font-size: 8px;
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 7px;
}

.stat-value {
    font-size: 19px;
    font-weight: bold;
    color: #263445;
}

.price {
    color: #15966b;
}

/* OBSERVACIONES */

.section-title {
    color: #172b4d;
    font-size: 9px;
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-bottom: 1px solid #e8edf3;
    padding-bottom: 7px;
    margin-bottom: 9px;
}

.observations {
    color: #64748b;
    font-size: 9px;
    line-height: 1.6;
}

/* PIE */

.footer {
    position: fixed;
    bottom: -5px;
    left: 0;
    right: 0;
    border-top: 1px solid #e8edf3;
    padding-top: 9px;
    color: #a0aec0;
    font-size: 8px;
}

.footer-left {
    float: left;
}

.footer-right {
    float: right;
}

</style>
</head>

<body>

<!-- ENCABEZADO -->

<div class="header">

    <div class="header-left">
        <div class="brand">Gestión de inventario</div>
        <div class="document-title">Ficha de producto</div>
        <div class="document-subtitle">
            Información y detalles del artículo registrado
        </div>
    </div>

    <div class="header-right">
        <span class="id-label">PRODUCTO #$pId</span>
        <div class="date">Generado: $generado</div>
    </div>

    <div class="clear"></div>

</div>

<!-- CONTENIDO PRINCIPAL -->

<div class="main">

    <div class="left">

        <div class="image-container">
            $imgHtml
        </div>

    </div>

    <div class="right">

        <div class="category-label">
            Información general
        </div>

        <h1 class="product-name">$pNombre</h1>

        <div class="provider-label">
            PROVEEDOR
        </div>

        <div class="provider-name">
            $pProv
        </div>

        <table class="stats">
            <tr>

                <td class="stat">
                    <div class="stat-label">Precio de venta</div>
                    <div class="stat-value price">
                        Bs. $pPrecio
                    </div>
                </td>

                <td class="stat">
                    <div class="stat-label">Existencias</div>
                    <div class="stat-value">
                        $pStock <span style="font-size:9px;color:#94a3b8;">unidades</span>
                    </div>
                </td>

            </tr>
        </table>

        <div class="section-title">
            Detalles y observaciones
        </div>

        <div class="observations">
            $pObs
        </div>

    </div>

    <div class="clear"></div>

</div>

<!-- PIE DE PÁGINA -->

<div class="footer">

    <div class="footer-left">
        Documento informativo · Gestión de inventario
    </div>

    <div class="footer-right">
        Registro de producto #$pId
    </div>

    <div class="clear"></div>

</div>

</body>
</html>
HTML;

// ================= DOMPDF =================

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A5', 'landscape');
$dompdf->render();

ob_end_clean();

$dompdf->stream(
    'ficha_producto_' . $pId . '.pdf',
    ['Attachment' => false]
);

exit;
