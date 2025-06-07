<?php
require_once '../../config/conexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<p>ID de servicio no proporcionado.</p>";
    exit;
}

// Conexión y obtención de datos
$conexion = new Conexion();
$pdo = $conexion->conectar();

// Obtener todas las revisiones registradas para ese iddetservicio
$sqlTodas = "SELECT idservicio, nombre_servicio, precio_sugerido FROM servicios WHERE iddetservicio = :id ORDER BY idservicio DESC";
$stmtTodas = $pdo->prepare($sqlTodas);
$stmtTodas->execute([':id' => $id]);
$revisiones = $stmtTodas->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($_GET['mensaje'])): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i>
        <?= htmlspecialchars($_GET['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


<!-- Bootstrap y FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-5">
    <h4>
        <i class="fa-solid fa-screwdriver-wrench me-2"></i> Revisión del Servicio #<?= htmlspecialchars($id) ?>
    </h4>

    <!-- Formulario limpio y enfocado -->
    <form method="post" action="../tareas/procesar_registro.php" onsubmit="return confirmarEnvio(event)">
        <input type="hidden" name="iddetservicio" value="<?= htmlspecialchars($id) ?>">

        <div class="mb-3">
            <label for="nombre_servicio" class="form-label">Nombre del Servicio</label>
            <input type="text" class="form-control" id="nombre_servicio" name="nombre_servicio" required autofocus>
        </div>

        <div class="mb-3">
            <label for="precio_sugerido" class="form-label">Precio Sugerido</label>
            <input type="number" class="form-control" id="precio_sugerido" name="precio_sugerido" step="0.01" required>
        </div>

        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success">Guardar Revisión</button>
    </form>

    <!-- Tabla de revisiones existentes -->
    <?php if (!empty($revisiones)): ?>
        <hr>
        <h5 class="mt-4"><i class="fa-solid fa-clock-rotate-left me-2"></i> Revisiones Registradas</h5>
        <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
    <thead class="table-light">
        <tr>
            <th>ID Servicio</th>
            <th>Nombre del Servicio</th>
            <th>Precio Sugerido</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        foreach ($revisiones as $rev):
            $total += $rev['precio_sugerido'];
        ?>
        <tr>
            <td><?= htmlspecialchars($rev['idservicio']) ?></td>
            <td><?= htmlspecialchars($rev['nombre_servicio']) ?></td>
            <td>S/. <?= number_format($rev['precio_sugerido'], 2) ?></td>
            <td class="text-center">
                <button class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?= $rev['idservicio'] ?>, <?= $id ?>)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="table-light fw-bold">
            <td colspan="2" class="text-end">Total</td>
            <td colspan="2">S/. <?= number_format($total, 2) ?></td>
        </tr>
    </tfoot>
</table>
        </div>
    <?php else: ?>
        <p class="text-muted mt-4">No hay revisiones registradas para este servicio.</p>
    <?php endif; ?>
</div>

<script>
function confirmarEnvio(e) {
    e.preventDefault();

    Swal.fire({
        title: '¿Registrar revisión?',
        text: 'Esta acción guardará el servicio revisado.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            e.target.submit();
        }
    });

    return false;
}

// Enfocar automáticamente el primer input al cargar
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('nombre_servicio').focus();
});
</script>


<script>
function confirmarEliminacion(idservicio, iddetservicio) {
    Swal.fire({
        title: '¿Eliminar revisión?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `http://localhost/andream/app/views/tareas/eliminar_revision.php?idservicio=${idservicio}&iddetservicio=${iddetservicio}`;
        }
    });
}
</script>

