<?php
require_once __DIR__ . '/../../models/contratos/Contrato.php';
require_once __DIR__ . '/../../models/personas/Persona.php';
require_once __DIR__ . '/../../models/roles/Rol.php';

$contratoModel = new Contrato();
$contratos = $contratoModel->obtenerContratos();
// Asegúrate de que las consultas para obtener las personas y roles estén funcionando correctamente
$personaModel = new Persona();
$personas = $personaModel->obtenerPersonas();  // Esto debería devolver un array de personas

$rolModel = new Rol();
$roles = $rolModel->obtenerRoles();  // Esto debería devolver un array de roles


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contratos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .acciones-btn-group {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .acciones-btn-group button {
            min-width: 36px;
            padding: 6px 8px;
            font-size: 1rem;
        }
        table.table tbody tr td {
            vertical-align: middle;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">
<div class="container mt-5">
    <div class="card" style="background-color: #02505F; color: white;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Contratos Existentes</h3>
            <button class="btn btn-light text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalRegistrar">
                <strong>+ Agregar Contrato</strong>
            </button>
        </div>

        <div class="card-body bg-white text-dark rounded-bottom">
            <?php if (isset($mensajeError)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>

            <?php if (isset($mensajeExito)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensajeExito) ?></div>
            <?php endif; ?>

            <table class="table table-bordered table-striped table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID Contrato</th>
                        <th>Persona</th>
                        <th>Rol</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contratos)): ?>
                        <?php foreach ($contratos as $contrato): ?>
                            <tr>
                                <td><?= htmlspecialchars($contrato['idcontrato']) ?></td>
                                <td><?= htmlspecialchars(trim($contrato['nombres'] . ' ' . $contrato['Primer_Apellido'] . ' ' . $contrato['Segundo_Apellido'])) ?></td>
                                <td><?= htmlspecialchars($contrato['rol']) ?></td>
                                <td><?= htmlspecialchars($contrato['fecha_inicio']) ?></td>
                                <td><?= htmlspecialchars($contrato['fecha_fin']) ?></td>
                                <td><?= htmlspecialchars($contrato['observaciones']) ?></td>                     
                                <td>
                                    <?php if ($contrato['idpersona'] != 1): ?>
                                        <?php
                                            $estado = isset($contrato['estado']) ? intval($contrato['estado']) : 0;
                                            $btnClass = $estado === 1 ? 'btn-success' : 'btn-secondary';
                                            $iconClass = $estado === 1 ? 'fa-toggle-on' : 'fa-toggle-off';
                                        ?>
                                        <div class="acciones-btn-group">
                                            <button type="button"
                                                class="btn btn-sm <?= $btnClass ?>"
                                                data-idcontrato="<?= $contrato['idcontrato'] ?>"
                                                onclick="toggleButton(this)"
                                                title="Estado">
                                                <i class="fa-solid <?= $iconClass ?>"></i>
                                            </button>

                                            <button class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalRegistrarUsuario" 
                                                    data-id="<?= $contrato['idcontrato'] ?>" 
                                                    data-persona='<?= htmlspecialchars($contrato['nombres'] . ' ' . $contrato['Primer_Apellido'] . ' ' . $contrato['Segundo_Apellido'], ENT_QUOTES) ?>'
                                                    title="Registrar Usuario">
                                                <i class="fa-solid fa-key"></i>
                                            </button>

                                            <button class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <button class="btn btn-sm btn-danger" title="Eliminar" data-idcontrato="<?= $contrato['idcontrato'] ?>" onclick="eliminarContrato(this)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>


                                            <button class="btn btn-sm btn-info" 
                                                    title="Ver Credencial" 
                                                    data-idcontrato="<?= $contrato['idcontrato'] ?>" 
                                                    onclick="verCredencial(this)">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>



                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Sin acciones</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay contratos disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Función toggle con SweetAlert2 -->
<script>
function toggleButton(btn) {
    const icon = btn.querySelector('i');
    const isActive = btn.classList.contains('btn-success');
    const newEstado = isActive ? 0 : 1;
    const idContrato = btn.dataset.idcontrato;

    const mensaje = newEstado === 1
        ? "¿Deseas activar las credenciales del usuario ?"
        : "¿Deseas desactivar las credenciales del usuario ?";

    Swal.fire({
        title: 'Confirmación',
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('http://localhost/andream/app/views/contratos/actualizar_estado_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idcontrato=${encodeURIComponent(idContrato)}&estado=${newEstado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (newEstado === 1) {
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-success');
                        icon.classList.remove('fa-toggle-off');
                        icon.classList.add('fa-toggle-on');
                    } else {
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-secondary');
                        icon.classList.remove('fa-toggle-on');
                        icon.classList.add('fa-toggle-off');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Estado actualizado!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar el estado.'
                    });
                }
            })
            .catch(error => {
                console.error('Error en la solicitud:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo contactar con el servidor.'
                });
            });
        }
    });
}




function eliminarContrato(btn) {
    const idContrato = btn.dataset.idcontrato;

    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esta acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('http://localhost/andream/app/views/contratos/eliminar_contrato.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idcontrato=${encodeURIComponent(idContrato)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Contrato eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Opcional: recargar la página para actualizar la lista
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo eliminar el contrato.'
                    });
                }
            })
            .catch(error => {
                console.error('Error en la solicitud:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo contactar con el servidor.'
                });
            });
        }
    });
}

</script>



<?php include 'modalRegistrar.php'; ?>
<?php include 'modalRegistrarUsuario.php'; ?>
<?php include 'modalVerCredencial.php'; ?>
</body>
</html>
