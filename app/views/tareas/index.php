<?php require_once '../../controllers/tareas/index.php'; ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap CSS y JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-list-ol me-2"></i> Mis Tareas
    </h2>

    <?php if (isset($tareas) && !empty($tareas)): ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Servicio</th>
                        <th>ID Equipo</th>
                        <th>Marca</th>
                        <th>Subcategoría</th>
                        <th>Categoría</th>
                        <th>Problema Reportado</th>
                        <th>Lista de Tareas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tareas as $tarea): ?>
                    <?php $id = htmlspecialchars($tarea['iddetservicio']); ?>
                    <tr>
                        <td><?= $id ?></td>
                        <td><?= htmlspecialchars($tarea['iddetequipo']) ?></td>
                        <td><?= htmlspecialchars($tarea['Nombre_Marca']) ?></td>
                        <td><?= htmlspecialchars($tarea['Nombre_SubCategoria']) ?></td>
                        <td><?= htmlspecialchars($tarea['NombreCategoria']) ?></td>
                        <td><?= htmlspecialchars($tarea['descripcionentrada']) ?></td>
                        
                        <td class="text-center">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalCondicion<?= $id ?>" title="Ver condición de entrada">
                                <i class="fa-solid fa-list-ul text-primary"></i>
                            </a>
                        </td>

                        
                        <td>
<?php if (empty($tarea['fechahorainicio'])): ?>
    <!-- Botón Empezar -->
    <a href="#" class="btn btn-sm btn-primary empezar-btn" data-id="<?= $id ?>" data-bs-toggle="modal" data-bs-target="#modalEmpezar<?= $id ?>">
        <i class="fa-solid fa-play"></i> Empezar
    </a>
<?php elseif (empty($tarea['fechahorafin'])): ?>
    <!-- Botones para registrar revisión, evidencias, etc -->
    <a href="dashboard.php?view=registrar_revision&id=<?= urlencode($id) ?>" class="me-2 text-decoration-none" title="Registrar Revisión">
        <i class="fa-solid fa-screwdriver-wrench"></i>
    </a>
    <a href="#" title="Evidencias" data-bs-toggle="modal" data-bs-target="#modalEvidencias<?= $id ?>" class="me-2 text-decoration-none">
        <i class="fa-solid fa-camera-retro"></i>
    </a>
    <a href="#" class="text-decoration-none listar-evidencias" title="Listar evidencias" data-id="<?= $id ?>" data-bs-toggle="modal" data-bs-target="#listarEvidenciasModal">
        <i class="fa-solid fa-eye"></i>
    </a>
<?php if ($tarea['puede_finalizar']): ?>
    <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalFinalizar<?= $id ?>">
        <i class="fa-solid fa-check"></i> Finalizar
    </a>
<?php else: ?>
    <span class="text-muted small" title="Esperando evidencias u/o servicios para finalizar"> 
        <i class="fa-solid fa-circle-exclamation text-warning me-1"></i>
        
    </span>
<?php endif; ?>

<?php else: ?>     
    <span class="badge bg-success">Tarea finalizada</span>
<?php endif; ?>


                        </td>
                    </tr>

                    <?php include 'modalRegistro.php'; ?>
                    <?php include 'modal_evidencias.php'; ?>

                    <!-- Modal Finalizar -->
                    <div class="modal fade" id="modalFinalizar<?= $id ?>" tabindex="-1" aria-labelledby="modalFinalizarLabel<?= $id ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form class="formFinalizar" data-id="<?= $id ?>">
                            <div class="modal-header">
                              <h5 class="modal-title" id="modalFinalizarLabel<?= $id ?>"><i class="fa-solid fa-check me-2"></i>Finalizar Reparación</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                              <div class="mb-3">
                                <label for="observaciones<?= $id ?>" class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" id="observaciones<?= $id ?>" rows="3" required></textarea>
                              </div>
                              <input type="hidden" name="iddetservicio" value="<?= $id ?>">
                              <div class="alert alert-danger d-none" id="errorFinMsg<?= $id ?>"></div>
                              <div class="alert alert-success d-none" id="successFinMsg<?= $id ?>"></div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-success">Finalizar Tarea</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <!-- Modal Empezar -->
                    <div class="modal fade" id="modalEmpezar<?= $id ?>" tabindex="-1" aria-labelledby="modalEmpezarLabel<?= $id ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form class="formEmpezar" data-id="<?= $id ?>">
                            <div class="modal-header">
                              <h5 class="modal-title" id="modalEmpezarLabel<?= $id ?>"><i class="fa-solid fa-play me-2"></i>Iniciar tarea</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                              <p>¿Estás seguro de que deseas comenzar esta tarea?</p>
                              <input type="hidden" name="iddetservicio" value="<?= $id ?>">
                              <div class="alert alert-danger d-none" id="errorMsg<?= $id ?>"></div>
                              <div class="alert alert-success d-none" id="successMsg<?= $id ?>"></div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-success">Iniciar</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                   <!-- Modal de Tareas -->
                    <div class="modal fade" id="modalCondicion<?= $id ?>" tabindex="-1" aria-labelledby="modalCondicionLabel<?= $id ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCondicionLabel<?= $id ?>">
                                        <i class="fa-solid fa-list-ul me-2"></i>Lista de Tareas
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <p><?= !empty($tarea['condicionentrada']) 
                                        ? nl2br(htmlspecialchars($tarea['condicionentrada'])) 
                                        : '<span class="text-muted">No tiene tareas registradas.</span>' 
                                    ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i>No tienes tareas asignadas.
        </div>
    <?php endif; ?>
</div>

<!-- Modal para mostrar evidencias -->
<div class="modal fade" id="listarEvidenciasModal" tabindex="-1" aria-labelledby="listarEvidenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="listarEvidenciasLabel">Evidencias del equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="listarEvidenciasContenido">
                <p class="text-muted">Cargando evidencias...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Manejo AJAX para listar evidencias
$(document).on('click', '.listar-evidencias', function () {
    var idDetServicio = $(this).data('id');
    $('#listarEvidenciasContenido').html('<p class="text-muted">Cargando evidencias...</p>');

    $.ajax({
        url: '/andream/app/views/tareas/listar_evidencias.php',
        type: 'GET',
        data: { id: idDetServicio },
        success: function (data) {
            $('#listarEvidenciasContenido').html(data.trim() === '' || data.includes("Error") ?
                '<p class="text-danger">No se encontraron evidencias o hubo un error al cargar.</p>' :
                data
            );
        },
        error: function () {
            $('#listarEvidenciasContenido').html('<p class="text-danger">Error al cargar las evidencias.</p>');
        }
    });
});

// Enviar formulario "Empezar"
document.querySelectorAll('.formEmpezar').forEach(form => {
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const id = form.dataset.id;
        const errorDiv = document.getElementById('errorMsg' + id);
        const successDiv = document.getElementById('successMsg' + id);
        errorDiv.classList.add('d-none');
        successDiv.classList.add('d-none');

        try {
            const res = await fetch('/andream/app/views/tareas/registrar_inicio.php', {
                method: 'POST',
                body: new FormData(form)
            });
            const text = await res.text();
            if (res.ok) {
                successDiv.textContent = 'Tarea iniciada correctamente.';
                successDiv.classList.remove('d-none');
                setTimeout(() => location.reload(), 1500);
            } else {
                errorDiv.textContent = 'Error: ' + text;
                errorDiv.classList.remove('d-none');
            }
        } catch (err) {
            errorDiv.textContent = 'Error en la conexión: ' + err.message;
            errorDiv.classList.remove('d-none');
        }
    });
});

// Enviar formulario "Finalizar"
document.querySelectorAll('.formFinalizar').forEach(form => {
    form.addEventListener('submit', async e => {
        e.preventDefault();

        const formElement = e.target;
        const id = formElement.dataset.id;
        const errorDiv = document.getElementById('errorFinMsg' + id);
        const successDiv = document.getElementById('successFinMsg' + id);

        errorDiv.classList.add('d-none');
        successDiv.classList.add('d-none');

        const formData = new FormData(formElement);

        try {
            const response = await fetch('/andream/app/views/tareas/finalizar_tarea.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();

            if (response.ok) {
                successDiv.textContent = 'Tarea finalizada correctamente.';
                successDiv.classList.remove('d-none');

                setTimeout(() => {
                    // Cerrar modal
                    const modalEl = document.getElementById('modalFinalizar' + id);
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();

                    // Recarga la página o actualizar tabla dinámicamente si quieres
                    location.reload();
                }, 1500);
            } else {
                errorDiv.textContent = 'Error: ' + text;
                errorDiv.classList.remove('d-none');
            }
        } catch (error) {
            errorDiv.textContent = 'Error en la conexión: ' + error.message;
            errorDiv.classList.remove('d-none');
        }
    });
});
</script>
