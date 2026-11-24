<?php

/** @var mixed $UserConnected */
/** @var mixed $productos */
/** @var mixed $clientes */


// Lógica de registro (se incluye desde index.php, que ya tiene los modelos y $UserConnected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear_venta') {
  $ir = function ($query) {
    header("Location: index.php?" . $query);
    exit;
  };

  $cliente_id = (int) ($_POST['cliente_id'] ?? 0);
  $carrito    = json_decode($_POST['carrito'] ?? '[]', true);

  if ($cliente_id <= 0 || !$clienteModel->get($cliente_id) || !is_array($carrito) || count($carrito) === 0) {
    $ir('error=1');
  }

  // Unir cantidades si un producto llegó repetido
  $cantidades = [];
  foreach ($carrito as $linea) {
    if (!is_array($linea)) {
      $ir('error=1');
    }
    $pid  = (int) ($linea['producto_id'] ?? 0);
    $cant = (int) ($linea['cantidad'] ?? 0);
    if ($pid <= 0 || $cant <= 0) {
      $ir('error=1');
    }
    $cantidades[$pid] = ($cantidades[$pid] ?? 0) + $cant;
  }

  // Los precios y el stock se leen de la BD: nunca se confía en lo que manda el navegador
  $items = [];
  $total = 0;
  foreach ($cantidades as $pid => $cant) {
    $producto = $productoModel->get($pid);
    if (!$producto) {
      $ir('error=1');
    }
    if ($producto->stock < $cant) {
      $ir('error=4');
    }

    $items[] = ['producto_id' => $pid, 'cantidad' => $cant, 'precio_unitario' => $producto->precio];
    $total  += $producto->precio * $cant;
  }

  if ($ventaModel->crearVenta($cliente_id, $UserConnected->id, round($total, 2), $items)) {
    $ir('success=1');
  }
  $ir('error=1');
}
?>

<style>
  /* Cada columna del modal hace scroll por su cuenta; la altura se ajusta a la pantalla */
  #modalNuevaVenta .venta-scroll-catalogo {
    max-height: calc(100vh - 300px);
    min-height: 240px;
    overflow-y: auto;
  }

  #modalNuevaVenta .venta-scroll-carrito {
    max-height: calc(100vh - 440px);
    min-height: 140px;
    overflow-y: auto;
  }
</style>

<!-- Modal: Nueva venta (carrito) -->
<div class="modal fade" id="modalNuevaVenta" tabindex="-1" aria-labelledby="tituloNuevaVenta" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <form action="modules/ventas/index.php" method="POST" id="formVenta" class="modal-content">
      <input type="hidden" name="accion" value="crear_venta">
      <input type="hidden" name="carrito" id="venta_carrito" value="[]">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="tituloNuevaVenta"><i class="fas fa-cart-plus me-2"></i>Nueva venta</h5>
        <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4">

          <!-- Catálogo: el buscador queda fijo y solo la lista de productos hace scroll -->
          <div class="col-lg-7">
            <input type="search" id="venta_buscar" class="form-control mb-3" placeholder="Buscar producto...">
            <div class="venta-scroll-catalogo pe-2">
              <div class="row g-2" id="venta_catalogo">
                <?php foreach ($productos as $p) { ?>
                  <div class="col-6 col-md-4 venta-prod" data-buscar="<?= htmlspecialchars(mb_strtolower($p->nombre), ENT_QUOTES) ?>">
                    <div class="card h-100">
                      <?php if (!empty($p->imagen_url)) { ?>
                        <img src="<?= htmlspecialchars($p->imagen_url, ENT_QUOTES) ?>" alt="" class="card-img-top" style="height:90px;object-fit:cover;">
                      <?php } else { ?>
                        <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="height:90px;">
                          <i class="fas fa-image fa-2x"></i>
                        </div>
                      <?php } ?>
                      <div class="card-body p-2 d-flex flex-column">
                        <div class="fw-semibold small"><?= htmlspecialchars($p->nombre) ?></div>
                        <div class="small text-muted mb-2">
                          <?= number_format((float) $p->precio, 2) ?> · Stock: <?= (int) $p->stock ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-success mt-auto btn-agregar-prod"
                          data-id="<?= $p->id ?>"
                          data-nombre="<?= htmlspecialchars($p->nombre, ENT_QUOTES) ?>"
                          data-precio="<?= (float) $p->precio ?>"
                          data-stock="<?= (int) $p->stock ?>"
                          <?= $p->stock <= 0 ? 'disabled' : '' ?>>
                          <?= $p->stock <= 0 ? 'Sin stock' : '<i class="fas fa-plus"></i> Agregar' ?>
                        </button>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <!-- Carrito: cliente y total siempre visibles, solo la lista de items hace scroll -->
          <div class="col-lg-5">
            <label for="venta_cliente" class="form-label">Cliente</label>
            <select name="cliente_id" id="venta_cliente" class="form-select mb-3" required>
              <option value="">Seleccione un cliente...</option>
              <?php foreach ($clientes as $c) { ?>
                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nom_comercial) ?> (NIT <?= htmlspecialchars($c->nit) ?>)</option>
              <?php } ?>
            </select>

            <div class="venta-scroll-carrito">
              <table class="table table-sm align-middle mb-2">
                <thead class="table-light">
                  <tr>
                    <th>Producto</th>
                    <th style="width:125px;">Cant.</th>
                    <th class="text-end">Subtotal</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="venta_items"></tbody>
              </table>
              <p id="venta_vacio" class="text-muted text-center my-3">El carrito está vacío.</p>
            </div>

            <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-2 mt-2">
              <span>Total</span>
              <span id="venta_total">0.00</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-danger" id="venta_vaciar">Vaciar carrito</button>
        <button type="submit" class="btn btn-success" id="venta_guardar" disabled>
          <i class="fas fa-save"></i> Registrar venta
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const carrito = {}; // { id: {id, nombre, precio, stock, cantidad} }

    const $items = document.getElementById('venta_items');
    const $vacio = document.getElementById('venta_vacio');
    const $total = document.getElementById('venta_total');
    const $hidden = document.getElementById('venta_carrito');
    const $guardar = document.getElementById('venta_guardar');
    const $buscar = document.getElementById('venta_buscar');
    const $catalogo = document.getElementById('venta_catalogo');
    const $form = document.getElementById('formVenta');

    function esc(texto) {
      const d = document.createElement('div');
      d.textContent = texto;
      return d.innerHTML;
    }

    function render() {
      const ids = Object.keys(carrito);
      let total = 0;
      $items.innerHTML = '';

      ids.forEach(function(id) {
        const it = carrito[id];
        const sub = it.precio * it.cantidad;
        total += sub;

        const tr = document.createElement('tr');
        tr.innerHTML =
          '<td><div class="fw-semibold">' + esc(it.nombre) + '</div>' +
          '<small class="text-muted">' + it.precio.toFixed(2) + ' c/u</small></td>' +
          '<td><div class="input-group input-group-sm">' +
          '<button type="button" class="btn btn-outline-secondary" data-accion="menos" data-id="' + it.id + '">−</button>' +
          '<input type="number" class="form-control text-center px-1" min="1" max="' + it.stock + '" value="' + it.cantidad + '" data-accion="cant" data-id="' + it.id + '">' +
          '<button type="button" class="btn btn-outline-secondary" data-accion="mas" data-id="' + it.id + '">+</button>' +
          '</div></td>' +
          '<td class="text-end">' + sub.toFixed(2) + '</td>' +
          '<td><button type="button" class="btn btn-sm btn-outline-danger" data-accion="quitar" data-id="' + it.id + '"><i class="fas fa-times"></i></button></td>';
        $items.appendChild(tr);
      });

      $total.textContent = total.toFixed(2);
      $vacio.style.display = ids.length ? 'none' : '';
      $guardar.disabled = ids.length === 0;
      $hidden.value = JSON.stringify(ids.map(function(id) {
        return {
          producto_id: carrito[id].id,
          cantidad: carrito[id].cantidad
        };
      }));
    }

    // Agregar desde el catálogo
    $catalogo.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn-agregar-prod');
      if (!btn || btn.disabled) return;

      const id = btn.dataset.id;
      if (carrito[id]) {
        if (carrito[id].cantidad < carrito[id].stock) carrito[id].cantidad++;
      } else {
        carrito[id] = {
          id: parseInt(id, 10),
          nombre: btn.dataset.nombre,
          precio: parseFloat(btn.dataset.precio),
          stock: parseInt(btn.dataset.stock, 10),
          cantidad: 1
        };
      }
      render();
    });

    // Botones + / − / quitar dentro del carrito
    $items.addEventListener('click', function(e) {
      const btn = e.target.closest('button[data-accion]');
      if (!btn) return;
      const it = carrito[btn.dataset.id];
      if (!it) return;

      if (btn.dataset.accion === 'mas' && it.cantidad < it.stock) it.cantidad++;
      if (btn.dataset.accion === 'menos' && it.cantidad > 1) it.cantidad--;
      if (btn.dataset.accion === 'quitar') delete carrito[it.id];
      render();
    });

    // Cantidad escrita a mano
    $items.addEventListener('change', function(e) {
      const input = e.target.closest('input[data-accion="cant"]');
      if (!input) return;
      const it = carrito[input.dataset.id];
      if (!it) return;

      let v = parseInt(input.value, 10);
      if (isNaN(v) || v < 1) v = 1;
      if (v > it.stock) v = it.stock;
      it.cantidad = v;
      render();
    });

    // Buscador de productos
    $buscar.addEventListener('input', function() {
      const q = $buscar.value.trim().toLowerCase();
      $catalogo.querySelectorAll('.venta-prod').forEach(function(el) {
        el.style.display = el.dataset.buscar.includes(q) ? '' : 'none';
      });
    });

    // Vaciar carrito
    document.getElementById('venta_vaciar').addEventListener('click', function() {
      Object.keys(carrito).forEach(function(id) {
        delete carrito[id];
      });
      render();
    });

    // Evitar envío vacío o doble clic
    $form.addEventListener('submit', function(e) {
      if (Object.keys(carrito).length === 0) {
        e.preventDefault();
        return;
      }
      $guardar.disabled = true;
    });

    render();
  });
</script>