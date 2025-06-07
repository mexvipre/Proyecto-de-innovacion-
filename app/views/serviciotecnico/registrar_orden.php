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
        // Insertar orden de servicio
        $stmt = $conexion->prepare("INSERT INTO orden_de_servicios (fecha_recepcion, idusuario_crea, idcliente) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $fecha_recepcion, $idusuario_crea, $idcliente);

        try {
            $stmt->execute();
            $idorden = $stmt->insert_id; // Obtener ID de la orden creada
            $stmt->close();

            // Subida de archivo de evidencia
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $directorio = "evidencias/";
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $nombreOriginal = basename($_FILES['archivo']['name']);
                $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                $nombreFinal = 'evid_' . $idorden . '_' . time() . '.' . $extension;
                $rutaCompleta = $directorio . $nombreFinal;

                if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaCompleta)) {
                    // Guardar en tabla evidencia_entrada
                    $stmt2 = $conexion->prepare("INSERT INTO evidencia_entrada (nombre_archivo, idorden) VALUES (?, ?)");
                    $stmt2->bind_param("si", $nombreFinal, $idorden);
                    $stmt2->execute();
                    $stmt2->close();
                } else {
                    $errores[] = "❌ No se pudo subir el archivo de evidencia.";
                }
            }

            $mensaje = "✅ Orden registrada correctamente con evidencia.";
        } catch (Exception $e) {
            $errores[] = "❌ Error al registrar: " . htmlspecialchars($e->getMessage());
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { font-family: Arial; margin: 30px; }
        label { display: block; margin-top: 15px; }
        input, select, button { padding: 8px; width: 300px; }
        .alert { padding: 10px; margin-top: 10px; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
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

<form action="" method="POST" enctype="multipart/form-data">
    <label>Creado por:</label>
    <input type="text" value="<?= $nombreUsuario ?>" readonly>

    <label>Cliente:</label>
    <select name="idcliente" id="cliente" required>
        <option value="">-- Selecciona un cliente --</option>
        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= htmlspecialchars($cliente['idcliente']) ?>"
                <?= $cliente['idcliente'] == $primerCliente['idcliente'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cliente['cliente_nombre']) ?>
                / <?= htmlspecialchars($cliente['persona_numerodoc']) ?>
                <?= htmlspecialchars($cliente['empresa_ruc']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Subir Evidencia (imagen o PDF):</label>
    <input type="file" name="archivo" accept="image/*,application/pdf">

    <button type="submit">Registrar Orden de Servicio</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#cliente').select2({
            placeholder: "Buscar cliente",
            allowClear: true
        });
    });
</script>

<?php $conexion->close(); ?>
</body>
</html>
