<!-- Incluye SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php $id = $tarea['iddetservicio']; ?>

<!-- Modal de Revisión para el servicio específico -->
<div class="modal fade" id="modalRevision<?= $id ?>" tabindex="-1" aria-labelledby="modalRevisionLabel<?= $id ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRevisionLabel<?= $id ?>">
          <i class="fa-solid fa-screwdriver-wrench me-2"></i>Revisión del Servicio #<?= $id ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p><strong>Problema Reportado:</strong> <?= htmlspecialchars($tarea['descripcionentrada']) ?></p>

        <!-- Formulario de Revisión -->
        <form id="formRevision<?= $id ?>" method="post" action="../tareas/procesar_registro.php">
          <input type="hidden" name="iddetservicio" value="<?= $id ?>">

          <div class="mb-3">
            <label class="form-label">Nombre del Servicio</label>
            <input type="text" class="form-control" name="nombre_servicio" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Precio Sugerido</label>
            <input type="number" class="form-control" name="precio_sugerido" step="0.01" required>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar Revisión</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Script para confirmación -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formRevision<?= $id ?>');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas registrar este servicio?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
</script>
