<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['idpersona'])) {
    die("Error: No se encontró el ID de la persona en la sesión. Verifica que iniciaste sesión correctamente.");
}

require_once '../../models/distritos/Distrito.php';
require_once '../../models/personas/Persona.php';

$distritoModel = new Distrito();
$personaModel = new Persona();

$distritos = $distritoModel->obtenerDistritos();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombres = $_POST['nombres'];
    $primerApellido = $_POST['Primer_Apellido'];
    $segundoApellido = $_POST['Segundo_Apellido'];
    $telefono = $_POST['telefono'];
    $tipodoc = $_POST['tipodoc'];
    $numerodoc = $_POST['numerodoc'];
    $direccion = $_POST['direccion'];
    $iddistrito = $_POST['iddistrito'];
    $correo = $_POST['correo'];
    $estado = $_POST['estado'];

    $usuario_id = $_SESSION['idpersona'];

    // Llamada al procedimiento almacenado para insertar la persona
    try {
        $insertado = $personaModel->insertarPersona($nombres, $primerApellido, $segundoApellido, $telefono, $tipodoc, $numerodoc, $direccion, $iddistrito, $correo, $estado, $usuario_id);

        if ($insertado) {
            $mensaje = "Persona registrada correctamente.";
        } else {
            $mensaje = "Error al registrar la persona.";
        }
    } catch (Exception $e) {
        $mensaje = "Error en el procedimiento: " . $e->getMessage();
    }
}
?>

<!-- Aquí continúa el HTML del formulario -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar Persona</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .header-container { display: flex; justify-content: space-between; align-items: center; }
        .obligatorio { color: red; margin-left: 2px; }
        .button-container { display: flex; gap: 10px; }
        .btn-verde { background-color: green; color: white; }
        .form-row { margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container mt-3">
    <div class="header-container mb-3">
        <h2 class="mb-0">Insertar Persona</h2>
        <div class="button-container">
            <button class="btn btn-success btn-verde" onclick="window.location.href='../dashboard/dashboard.php?view=serviciotecnico';">Continuar</button>
        </div>
    </div>

    <form id="formInsertarPersona" method="POST" action="">
        <div class="form-row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Número de Documento:<span class="obligatorio">*</span></label>
                    <input type="text" id="dni" name="numerodoc" class="form-control form-control-sm" placeholder="Ingrese DNI" maxlength="8" required autofocus >
                </div>
            </div>


            <div class="col-md-3">
                <div class="form-group">
                    <label>Nombres:<span class="obligatorio">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="nombres" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Apellido Paterno:<span class="obligatorio">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="Primer_Apellido" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Apellido Materno:<span class="obligatorio">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="Segundo_Apellido" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Teléfono:<span class="obligatorio">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="telefono" required>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tipo de Documento:<span class="obligatorio">*</span></label>
                    <select class="form-control form-control-sm" name="tipodoc" required>
                        <option value="">Seleccione...</option>
                        <option value="DNI" selected>DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Carnet de extranjería">Carnet de extranjería</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Dirección:<span class="obligatorio">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="direccion" required>
                </div>
            </div>
            <div class="col-md-3">
                <?php if (!empty($distritos)): ?>
                    <div class="form-group">
                        <label>Distrito:<span class="obligatorio">*</span></label>
                        <select name="iddistrito" class="form-control form-control-sm" required>
                            <option value="">Seleccionar distrito</option>
                            <?php foreach ($distritos as $distrito): ?>
                                <option value="<?= htmlspecialchars($distrito['iddistrito']) ?>"><?= htmlspecialchars($distrito['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <p>No se encontraron distritos.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Correo:</label>
                    <input type="email" class="form-control form-control-sm" name="correo">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Creado por:</label>
                    <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($_SESSION['usuario']); ?>" disabled>
                </div>
            </div>
            <div class="col-md-3 d-none">
                <div class="form-group">
                    <select class="form-control form-control-sm" name="estado" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 text-right d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">Guardar Persona</button>
            </div>
        </div>
    </form>
</div>


<script>
$(document).ready(function() {
    let debounceTimer;

    $('#dni').on('input', function() {
        const dni = $('#dni').val().trim();

        // Verificar si el DNI tiene exactamente 8 dígitos
        if (dni.length === 8 && !isNaN(dni)) {
            // Cancelar la ejecución anterior si existe
            clearTimeout(debounceTimer);

            // Establecer un nuevo temporizador de espera (500 ms)
            debounceTimer = setTimeout(function() {
                $.ajax({
                    url: 'http://localhost/andream/app/views/personas/consulta_dni.php',
                    type: 'POST',
                    data: { dni: dni },
                    dataType: 'json',
                    beforeSend: function() {
                        // Deshabilitar la entrada mientras se consulta
                        $('#dni').prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            $('[name="nombres"]').val(data.nombres);
                            $('[name="Primer_Apellido"]').val(data.apellidoPaterno);
                            $('[name="Segundo_Apellido"]').val(data.apellidoMaterno);
                            Swal.fire('Éxito', 'Datos encontrados correctamente.', 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo realizar la consulta.', 'error');
                    },
                    complete: function() {
                        // Habilitar la entrada nuevamente
                        $('#dni').prop('disabled', false);
                    }
                });
            }, 500);  // Tiempo de espera en milisegundos (500 ms)
        } else {
            // Si el DNI no tiene 8 dígitos, se limpia el formulario
            $('[name="nombres"]').val('');
            $('[name="Primer_Apellido"]').val('');
            $('[name="Segundo_Apellido"]').val('');
        }
    });

    // Manejar el envío del formulario con confirmación
    $('#formInsertarPersona').on('submit', function(event) {
        event.preventDefault(); // Evitar el envío normal del formulario

        // Mostrar un SweetAlert para confirmar el registro
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas agregar esta persona?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, agregar',
            cancelButtonText: 'No, cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario acepta, enviar el formulario
                $.ajax({
                    url: '', // El mismo archivo PHP para procesar
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        // Si el registro es exitoso, mostrar mensaje
                        Swal.fire('Éxito', 'Registro agregado correctamente.', 'success').then(() => {
                            window.location.href = "../dashboard/dashboard.php?view=inicio"; // Redirigir a la página de servicios
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un problema al registrar la persona.', 'error');
                    }
                });
            }
        });
    });
});
</script>


</body>
</html>
