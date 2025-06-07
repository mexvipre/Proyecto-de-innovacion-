<?php
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar que la sesión tenga el ID de la persona
if (!isset($_SESSION['idpersona'])) {
    die("Error: No se encontró el ID de la persona en la sesión. Verifica que iniciaste sesión correctamente.");
}

// Importar los modelos y conexión
require_once '../../models/detequipos/Equipo.php';
require_once '../../models/marcasasoc/MarcaSoc.php';
require_once '../../models/OrdenServicio/Ordenservicio.php';
require_once '../../models/caracteristicas/Caracteristica.php';
require_once '../../models/evidencias_entrada/Evidencia.php';

// Conexión
$db = Conexion::conectar();

// Instancias de modelos
$ordenServicioModel = new Ordenservicio($db);
$evidenciaModel = new Evidencia($db);
$caracteristicaModel = new Caracteristica($db);
$equipoModel = new Equipo($db);
$marcaSocModel = new MarcaSoc($db);

// Datos para mostrar en el formulario
$ultimaOrden = $ordenServicioModel->listarUltimaOrden();
$ultimaEvidencia = $evidenciaModel->obtenerUltimaEvidencia();
$ultimaCaracteristica = $caracteristicaModel->obtenerUltimaCaracteristica();
$marcas = $marcaSocModel->obtenerMarcas();
$ultimaMarca = (!empty($marcas) && is_array($marcas)) ? end($marcas) : [];

// Procesamiento del formulario
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recolección de datos
    $idmarcasoc = $_POST['idmarcasoc'] ?? null;
    $modelo = $_POST['modelo'];
    $numserie = $_POST['numserie'];
    $condicionentrada = $_POST['condicionentrada'];
    $descripcionentrada = $_POST['descripcionentrada'];
    $fechaentrega = $_POST['fechaentrega'];
    $condicionsalida = "en proceso"; // Valor fijo por defecto
    $idEvidencia = $ultimaEvidencia['idEvidencia'] ?? null;
    $id_caracteristica = $ultimaCaracteristica['id_caracteristica'] ?? null;
    $idorden_servicio = $ultimaOrden['idorden_Servicio'] ?? null;

    // Validar existencia de datos requeridos
    if ($idEvidencia && $id_caracteristica && $idorden_servicio) {
        try {
            $insertado = $equipoModel->insertarEquipo(
                $idmarcasoc, $modelo, $numserie, $condicionentrada,
                $descripcionentrada, $fechaentrega, $condicionsalida,
                $idEvidencia, $id_caracteristica, $idorden_servicio
            );

            if ($insertado) {
                $mensaje = "Equipo registrado correctamente.";
            } else {
                $mensaje = "Error al registrar el equipo.";
            }
        } catch (PDOException $e) {
            // Capturamos el error de duplicado y mostramos el mensaje
            if ($e->getCode() == 23000) {
                $mensaje = "Error: El ID de marca ya existe. Por favor, verifique la información ingresada.";
            } else {
                $mensaje = "Error al procesar la solicitud: " . $e->getMessage();
            }
        }
    } else {
        $mensaje = "Error: Datos necesarios no encontrados (Evidencia, Característica u Orden de Servicio).";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar Equipo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</head>
<body>

<style>
    /* Ocultar completamente el div que contiene el campo idmarcasoc */
    .hidden-container {
        display: none;
    }
</style>

<div class="container mt-4">
    <?php if (!empty($mensaje)): ?>
        <script type="text/javascript">
            alert("<?= htmlspecialchars($mensaje) ?>");
        </script>
    <?php endif; ?>

    <!-- Botón de registrar equipo en la parte superior izquierda -->


    <form method="POST" action="">


            <!-- Botón de envío del formulario en la parte inferior -->
            <div class="form-row mt-4">
            <div class="col-md-12 text-right">
            
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button class="btn btn-success btn-verde" onclick="confirmarAgregar(event);">+</button>
            <button class="btn btn-success btn-verde" onclick="window.location.href='../dashboard/dashboard.php?view=serviciotecnico';">...</button>
        </div>


        </div>
        <div class="form-row">
                <div class="col-md-6 hidden-container">
            <label>Idmarcasoc:</label>
            <!-- Campo oculto para enviar el idmarcasoc -->
            <input type="hidden" name="idmarcasoc" value="<?= $ultimaMarca['idmarcasoc'] ?? '' ?>" class="hidden-field">
        </div>


            <div class="col-md-6">
                <label>Modelo:</label>
                <input type="text" class="form-control" name="modelo" required>
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col-md-6">
                <label>Número de Serie:</label>
                <input type="text" class="form-control" name="numserie" required>
            </div>

            <div class="col-md-6">
                <label>Problema Reportado por el cliente:</label>
                <textarea class="form-control" name="condicionentrada"></textarea>
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col-md-6">
                <label>Descripción de Entrada:</label>
                <textarea class="form-control" name="descripcionentrada"></textarea>
            </div>

            <div class="col-md-6">
            <label>Fecha de Entregaaaaaaaaaaaaaaaaaaaaaaa:</label>
            <input type="datetime-local" class="form-control" name="fechaentrega" required id="fechaentrega">
        </div>
        </div>

        <div class="form-row mt-3">
            <div class="col-md-6">
                <!-- Campo oculto para enviar la condición de salida -->
                <input type="hidden" name="condicionsalida" value="en proceso" class="hidden-field">
            </div>

            <div class="col-md-6">
                <!-- Campo oculto para enviar el idEvidencia -->
                <input type="hidden" name="idEvidencia" value="<?= $ultimaEvidencia['idEvidencia'] ?? '' ?>" class="hidden-field">
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col-md-6">
                <!-- Campo oculto para enviar el id_caracteristica -->
                <input type="hidden" name="id_caracteristica" value="<?= $ultimaCaracteristica['id_caracteristica'] ?? '' ?>" class="hidden-field">
            </div>

            <div class="col-md-6">
                <!-- Campo oculto para enviar el idorden_servicio -->
                <input type="hidden" name="idorden_servicio" value="<?= $ultimaOrden['idorden_Servicio'] ?? '' ?>" class="hidden-field">
            </div>
        </div>


    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Obtener la fecha y hora actuales
        var today = new Date();
        today.setMinutes(today.getMinutes() - today.getTimezoneOffset()); // Ajuste de la zona horaria

        // Convertir la fecha a formato "yyyy-MM-ddTHH:mm"
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); // Los meses comienzan desde 0
        var yyyy = today.getFullYear();
        var hours = String(today.getHours()).padStart(2, '0');
        var minutes = String(today.getMinutes()).padStart(2, '0');

        var formattedDate = yyyy + '-' + mm + '-' + dd + 'T' + hours + ':' + minutes;

        // Establecer la fecha mínima para el campo de fecha de entrega
        document.getElementById("fechaentrega").setAttribute("min", formattedDate);
    });
</script>



<script>
    function confirmarAgregar(event) {
        // Prevenir la acción predeterminada del botón
        event.preventDefault();

        // Mostrar el cuadro de confirmación
        var confirmar = confirm("¿Deseas agregar otro equipo a la orden?");
        
        // Si el usuario elige "Sí"
        if (confirmar) {
            // Redirigir a la nueva ruta
            window.location.href = '../dashboard/dashboard.php?view=marcasasoc';
        } else {
            // Si el usuario elige "No", solo cerrar el alert y no hacer nada más
            // No se hace nada, el formulario no se envía
        }
    }
</script>

</body>
</html>
