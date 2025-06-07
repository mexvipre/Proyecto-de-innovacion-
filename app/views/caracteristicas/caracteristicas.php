<?php
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['especificacion'], $_POST['valor'])) {
    $especificacion = trim($_POST['especificacion']);
    $valor = $_POST['valor'];

    $conexion = new mysqli('localhost', 'root', '', 'compuservic');

    if ($conexion->connect_error) {
        die("❌ Error de conexión: " . $conexion->connect_error);
    }

    $stmt = $conexion->prepare("SELECT id_especificacion FROM especificaciones WHERE especificacion = ?");
    $stmt->bind_param("s", $especificacion);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $id_especificacion = $fila['id_especificacion'];

        $stmt_insert = $conexion->prepare("INSERT INTO caracteristicas (id_especificacion, valor) VALUES (?, ?)");
        $stmt_insert->bind_param("is", $id_especificacion, $valor);

        $mensaje = $stmt_insert->execute()
            ? "✅ Característica guardada correctamente."
            : "❌ Error al guardar la característica: " . $stmt_insert->error;

        $stmt_insert->close();
    } else {
        $stmt_insert = $conexion->prepare("INSERT INTO especificaciones (especificacion) VALUES (?)");
        $stmt_insert->bind_param("s", $especificacion);

        if ($stmt_insert->execute()) {
            $id_especificacion = $conexion->insert_id;

            $stmt_caracteristica = $conexion->prepare("INSERT INTO caracteristicas (id_especificacion, valor) VALUES (?, ?)");
            $stmt_caracteristica->bind_param("is", $id_especificacion, $valor);

            $mensaje = $stmt_caracteristica->execute()
                ? "✅ Característica guardada correctamente."
                : "❌ Error al guardar la característica: " . $stmt_caracteristica->error;

            $stmt_caracteristica->close();
        } else {
            $mensaje = "❌ Error al agregar la especificación: " . $stmt_insert->error;
        }

        $stmt_insert->close();
    }

    $stmt->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Característica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-verde {
            background-color: #00BF63;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-verde:hover {
            background-color: #008C4A;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="header-container mb-3">
        <h2>Agregar Característica</h2>
        <button form="form-caracteristica" type="submit" class="btn btn-primary">Guardar</button>
    </div>

    <form method="POST" action="" id="form-caracteristica" class="border p-4 rounded bg-light shadow-sm mt-4">
        <div class="mb-3">
            <label for="especificacion" class="form-label">Especificación:</label>
            <input type="text" name="especificacion" id="especificacion" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="valor" class="form-label">Descripción del Equipo:</label>
            <input type="text" name="valor" id="valor" class="form-control" required>
        </div>

    </form>
</div>

<?php if (!empty($mensaje)): ?>
    <script>
        // Mostrar SweetAlert2 con el mismo estilo que el ejemplo
        $(document).ready(function() {
            Swal.fire({
                title: '¡Operación completada!',
                text: '<?= $mensaje ?>',
                icon: 'success',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#00BF63',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../dashboard/dashboard.php?view=evidencias';
                }
            });
        });
    </script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
