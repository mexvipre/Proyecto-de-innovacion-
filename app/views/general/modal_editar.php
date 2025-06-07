<?php
require_once __DIR__ . '../../../config/conexion.php';
require_once __DIR__ . '../../../models/equipos/EquipoModel.php';

error_log("Usando el NUEVO modal_editar.php - " . date('Y-m-d H:i:s'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $idEquipo = isset($_POST['iddetequipo']) ? (int)$_POST['iddetequipo'] : 0;
    $modelo = trim($_POST['modelo'] ?? '');
    $numserie = trim($_POST['numserie'] ?? '');
    $descripcionentrada = trim($_POST['descripcionentrada'] ?? '');

    error_log("Datos recibidos - idEquipo: {$idEquipo}, modelo: '{$modelo}', numserie: '{$numserie}', descripcionentrada: '{$descripcionentrada}'");

    if ($idEquipo <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de equipo no válido."]);
        exit;
    }

    $equipoModel = new EquipoModel();
    $resultado = $equipoModel->actualizarEquipo($idEquipo, $modelo, $numserie, $descripcionentrada);
    echo json_encode($resultado);
    exit;
}

if (!isset($id) || $id <= 0) {
    echo "<p>ID de equipo no válido.</p>";
    exit;
}

$equipoModel = new EquipoModel();
$datosEquipo = $equipoModel->obtenerEquipoPorId($id);

if (empty($datosEquipo)) {
    echo "<p>Equipo no encontrado o error en consulta para ID {$id}</p>";
    exit;
}

$modal_id = "modalEditar{$id}";
?>

<style>
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    .text-display {
        display: block;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        cursor: not-allowed;
    }
</style>

<div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-labelledby="<?= $modal_id ?>Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?= $modal_id ?>Label">Actualizar Equipo "ID: <?= $id ?>"</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarEquipo<?= $id ?>">
                    <input type="hidden" name="iddetequipo" value="<?= $id ?>">
                    <div class="mb-3">
                        <label class="form-label">Categoría:</label>
                        <div class="text-display"><?= htmlspecialchars($datosEquipo['categoria'] ?? 'N/A') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategoría:</label>
                        <div class="text-display"><?= htmlspecialchars($datosEquipo['subcategoria'] ?? 'N/A') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Marca:</label>
                        <div class="text-display"><?= htmlspecialchars($datosEquipo['marca'] ?? 'N/A') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha aprox de Entrega:</label>
                        <div class="text-display"><?= !empty($datosEquipo['fechaentrega']) ? date('Y-m-d', strtotime($datosEquipo['fechaentrega'])) : 'N/A' ?></div>
                    </div>
                    <div class="mb-3">
                        <label for="modelo<?= $id ?>" class="form-label">Modelo:</label>
                        <input type="text" class="form-control" id="modelo<?= $id ?>" name="modelo" value="<?= htmlspecialchars($datosEquipo['modelo'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="numserie<?= $id ?>" class="form-label">Número de Serie:</label>
                        <input type="text" class="form-control" id="numserie<?= $id ?>" name="numserie" value="<?= htmlspecialchars($datosEquipo['numserie'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcionentrada<?= $id ?>" class="form-label">Problema reportado por el Cliente:</label>
                        <textarea class="form-control" id="descripcionentrada<?= $id ?>" name="descripcionentrada" required><?= htmlspecialchars($datosEquipo['descripcionentrada'] ?? '') ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-actualizar" data-id="<?= $id ?>">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalId = "<?= $modal_id ?>";
    const idEquipo = "<?= $id ?>";
    const form = document.getElementById('formEditarEquipo' + idEquipo);
    const btnActualizar = document.querySelector('#' + modalId + ' .btn-actualizar');

    btnActualizar.addEventListener('click', function () {
        const modelo = form.querySelector([name="modelo"]).value.trim();
        const numserie = form.querySelector([name="numserie"]).value.trim();
        const descripcionentrada = form.querySelector([name="descripcionentrada"]).value.trim();

        if (!modelo || !numserie || !descripcionentrada) {
            alert('Por favor, completa todos los campos editables.');
            return;
        }

        const formData = new FormData(form);

        console.log('Datos enviados:');
        for (let pair of formData.entries()) {
            console.log(${pair[0]}: ${pair[1]});
        }

        fetch('/AndreaM/app/views/general/modal_editar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(text || response.statusText); });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                // Mostrar alerta y cerrar modal después de aceptar
                if (confirm('Equipo actualizado correctamente. ¿Deseas cerrar?')) {
                    const modalElement = document.getElementById(modalId);
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modalInstance.hide();

                    // Eliminar backdrop y clases residuales después de la animación
                    setTimeout(() => {
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style = '';
                        console.log("Modal cerrado correctamente y backdrop eliminado.");
                    }, 300); // Esperar animación de cierre

                    // Recargar la tabla (ajusta la URL si es necesario)
                    const tablaContenedor = document.getElementById('contenedor-tabla');
                    if (tablaContenedor) {
                        fetch('/AndreaM/app/views/general/get_equipos.php?id=116')
                            .then(response => response.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const nuevaTabla = doc.querySelector('#contenedor-tabla');
                                if (nuevaTabla) {
                                    tablaContenedor.innerHTML = nuevaTabla.innerHTML;
                                }
                            })
                            .catch(error => console.error('Error al recargar tabla:', error));
                    }
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error en la solicitud:', error);
            alert('Error al actualizar el equipo: ' + error.message);
        });
    });
});
</script>