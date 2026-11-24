<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
$UserConnected = verifySession();

require_once "../../clases/Venta.php";
require_once "../../clases/Cliente.php";

header('Content-Type: application/json; charset=utf-8');

$ventaModel = new Venta();
$id    = (int) ($_GET['id'] ?? 0);
$venta = $id > 0 ? $ventaModel->get($id) : null;

if (!$venta) {
    http_response_code(404);
    echo json_encode(['error' => 'No se encontró la venta.']);
    exit;
}

$cliente = (new Cliente())->get($venta->cliente_id);

$items = [];
foreach ($ventaModel->getDetalle($id) as $d) {
    $items[] = [
        'producto' => $d->producto,
        'cantidad' => (int) $d->cantidad,
        'precio'   => (float) $d->precio_unitario,
        'subtotal' => (float) ($d->cantidad * $d->precio_unitario),
    ];
}

echo json_encode([
    'numero'   => str_pad($venta->id, 6, '0', STR_PAD_LEFT),
    'fecha'    => date('d/m/Y', strtotime($venta->fecha)),
    'hora'     => date('H:i', strtotime($venta->fecha)),
    'vendedor' => $venta->vendedor,
    'cliente'  => [
        'nombre'    => $cliente->nom_comercial ?? $venta->cliente,
        'legal'     => $cliente->nom_legal ?? '',
        'nit'       => $cliente->nit ?? '',
        'direccion' => $cliente->direccion ?? '',
        'telf'      => $cliente->telf ?? '',
    ],
    'items'    => $items,
    'total'    => (float) $venta->total,
], JSON_UNESCAPED_UNICODE);