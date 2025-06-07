<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Lima');

$conexion = new mysqli('localhost', 'root', '', 'compuservic');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$errores = [];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['idpersona'])) {
        die("❌ No se ha identificado al usuario. Inicie sesión.");
    }

    $idusuario_crea = (int)$_SESSION['idpersona'];
    $idcliente = isset($_POST['idcliente']) ? (int)$_POST['idcliente'] : null;
    $fecha_recepcion = date('Y-m-d H:i:s');

    if (!$idcliente) {
        $errores[] = "❌ El campo 'Cliente' es obligatorio.";
    }

    if (empty($errores)) {
        $stmt = $conexion->prepare("INSERT INTO orden_de_servicios (fecha_recepcion, idusuario_crea, idcliente) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $fecha_recepcion, $idusuario_crea, $idcliente);

        try {
            $stmt->execute();
            $mensaje = "✅ Orden de servicio registrada correctamente.";
            $stmt->close();
        } catch (Exception $e) {
            $errores[] = "❌ Error al registrar la orden: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Consultar clientes
$clientes = [];
if ($stmt = $conexion->prepare("CALL obtenerClientesV4()")) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    $result->free();
    $stmt->close();
}

$nombreUsuario = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Invitado';
$primerCliente = $clientes[0] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Orden de Servicio</title>
    <!-- Incluir el CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Incluir SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ... el mismo estilo que ya tienes ... */
    </style>
</head>
<body>

<?php if (!empty($errores)): ?>
    <?php foreach ($errores as $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($mensaje): ?>
    <div class="alert alert-success"><?= $mensaje ?></div>
<?php endif; ?>

<div class="container mt-4 p-4 border rounded shadow-sm bg-light">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0">Registrar Orden de Servicio</h4>
        <div class="d-flex gap-2">
            <button type="submit" form="form-orden" class="btn btn-primary">Guardar</button>
           
        </div>
    </div>

    <form action="" method="POST" id="form-orden">
        <div class="mb-3">
            <label class="form-label fw-bold">Creado por:</label>
            <input type="text" class="form-control" value="<?= $nombreUsuario ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="cliente" class="form-label fw-bold">Cliente:</label>
            <select name="idcliente" id="cliente" class="form-select" required>
                <option value="">-- Selecciona un cliente --</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= htmlspecialchars($cliente['idcliente']) ?>" 
                        <?= $cliente['idcliente'] == $primerCliente['idcliente'] ? 'selected' : '' ?> >
                        <?= htmlspecialchars($cliente['cliente_nombre']) ?> / 
                        <?= htmlspecialchars($cliente['persona_numerodoc']) ?> 
                        <?= htmlspecialchars($cliente['empresa_ruc']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

</div>

<!-- Incluir los scripts de Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
    // Activar Select2 en el select de clientes
    $(document).ready(function() {
        $('#cliente').select2({
            placeholder: "Buscar cliente",
            allowClear: true
        });
    });

    // Verificar si la variable 'mensaje' está definida y mostrar un alerta de SweetAlert
    <?php if ($mensaje): ?>
        Swal.fire({
            title: '¡<?= $mensaje ?>!',
            icon: 'success',
            confirmButtonText: 'Continuar',
            confirmButtonColor: '#00BF63'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../dashboard/dashboard.php?view=marcasasoc';  // Redirigir al continuar
            }
        });
    <?php endif; ?>
</script>

<?php $conexion->close(); ?>
</body>
</html>
