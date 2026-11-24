<?php
// Aseguramos que la clase esté disponible
require_once "../../clases/Usuario.php";
require_once "../../clases/Avatar.php";
/** @var ..\..\clases $row */
/** @var ..\..\config $UserConnected */




// LÓGICA POO: Interceptamos la actualización enviada desde el formulario del modal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_usuario'])) {
    $usuarioModel = new Usuario();
    $id_editar = (int) ($_POST['id'] ?? 0);

    // Sincronizamos la variable del formulario con lo que espera tu clase en $_POST['usuario']
    $_POST['usuario'] = $_POST['cuenta'] ?? '';

    // 1. Validar la foto ANTES de modificar nada (si es inválida, no se actualiza nada)
    try {
        Avatar::validar($_FILES['avatar'] ?? null);
    } catch (Exception $e) {
        header("Location: index.php?error=3");   // 3 = imagen no válida
        exit;
    }

    if ($id_editar > 0 && $usuarioModel->update($id_editar)) {
        // 2. Foto: se reemplaza si subió una nueva; si no, se quita solo si marcó la casilla
        $fotoOk = true;
        try {
            $subida = Avatar::guardar($id_editar, $_FILES['avatar'] ?? null);
            if (!$subida && !empty($_POST['quitar_avatar'])) {
                Avatar::eliminar($id_editar);
            }
        } catch (Exception $e) {
            error_log("Avatar: " . $e->getMessage());
            $fotoOk = false;
        }

        // Redireccionamos pasándole un parámetro de éxito de edición por URL
        header("Location: index.php?edit_success=1" . ($fotoOk ? '' : '&foto_error=1'));
        exit;
    }

    header("Location: index.php?error=1");
    exit;
}
?>

<!-- ========================================================
     MODAL DINÁMICO: EDITAR USUARIO (Se genera uno por usuario)
     ======================================================== -->
<div class="modal fade" id="modalEditarUsuario<?= $row->id ?>" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel<?= $row->id ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalEditarUsuarioLabel<?= $row->id ?>">Modificar Usuario: <?= htmlspecialchars($row->nombre) ?></h5>
        <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="modules/users/edit.php" method="post" enctype="multipart/form-data">
        <div class="modal-body text-start">
          <input type="hidden" name="actualizar_usuario" value="1">
          <input type="hidden" name="id" value="<?= $row->id ?>">

          <div class="mb-3">
            <label class="form-label fw-bold">Nombre Completo:</label>
            <input class="form-control" type="text" name="nombre" value="<?= htmlspecialchars($row->nombre) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Cargo / Función:</label>
            <input class="form-control" type="text" name="cargo" value="<?= htmlspecialchars($row->cargo ?? '') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Cuenta / Usuario:</label>
            <?php if ($UserConnected->nivel == 'A'): ?>
                <!-- Si es administrador puede editar la cuenta -->
                <input class="form-control" type="text" name="cuenta" value="<?= htmlspecialchars($row->usuario) ?>" required>
            <?php else: ?>
                <!-- Si no, solo lectura -->
                <input class="form-control" type="text" name="cuenta" value="<?= htmlspecialchars($row->usuario) ?>" readonly>
                <div class="form-text text-muted small">El identificador de cuenta de acceso no puede modificarse.</div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Nueva Contraseña (Opcional):</label>
            <div class="input-group">
              <!-- ID dinámico por fila para que la función JavaScript no se confunda de input -->
              <input class="form-control" type="password" id="edit_password_field_<?= $row->id ?>" name="password" placeholder="Dejar en blanco para mantener la clave actual">
              <button class="btn btn-outline-secondary" type="button" onclick="conmutarOjo('edit_password_field_<?= $row->id ?>', this)">
                👁️
              </button>
            </div>
            <div class="form-text text-muted small mt-1">Por seguridad, solo escribe si deseas cambiar la clave actual.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Seleccione el Nivel / Rol:</label>
            <select name="nivel" class="form-control" required>
              <option value="A" <?= ($row->nivel == 'A') ? 'selected' : '' ?>>Administrador</option>
              <option value="O" <?= ($row->nivel == 'O') ? 'selected' : '' ?>>Operador</option>
              <option value="V" <?= ($row->nivel == 'V') ? 'selected' : '' ?>>Visualizador</option>
            </select>
          </div>

          <!-- FOTO: muestra la actual (si existe) y permite cambiarla o quitarla -->
          <?php $avatarUrl = Avatar::url((int) $row->id); ?>
          <div class="mb-3">
            <label class="form-label fw-bold">Foto:</label>
            <div class="d-flex align-items-center gap-3">
              <?php if ($avatarUrl) { ?>
                <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES) ?>" width="64" height="64"
                     class="rounded-circle border" style="object-fit:cover;" alt="">
              <?php } else { ?>
                <i class="fas fa-user-circle fa-4x text-muted"></i>
              <?php } ?>
              <div class="flex-grow-1">
                <input class="form-control" type="file" name="avatar" accept="image/*">
                <div class="form-text">Déjalo vacío para conservar la foto actual. JPG, PNG o WEBP, máx. 2 MB.</div>
              </div>
            </div>
            <?php if ($avatarUrl) { ?>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="quitar_avatar" value="1" id="quitar_avatar_<?= $row->id ?>">
                <label class="form-check-label" for="quitar_avatar_<?= $row->id ?>">Quitar foto actual</label>
              </div>
            <?php } ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning fw-bold text-white">Actualizar Datos</button>
        </div>
      </form>
    </div>
  </div>
</div>