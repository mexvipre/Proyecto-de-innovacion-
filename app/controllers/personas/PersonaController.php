<?php
// Iniciar la sesión
session_start();

// Incluir el modelo de Persona
require_once '../../models/personas/Persona.php';
require_once '../../models/distritos/Distrito.php';

class PersonaController {

    // Método para mostrar el formulario de registro de personas
    public function mostrarFormulario() {
        // Verificar si el usuario está logueado
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            // Redirigir al login si no está autenticado
            header('Location: ../../../index.php');
            exit();
        }

        // Crear una instancia del modelo Distrito y Persona
        $distritoModel = new Distrito();
        $personaModel = new Persona();

        // Obtener los distritos desde la base de datos
        $distritos = $distritoModel->obtenerDistritos();

        // Incluir la vista para insertar persona
        include '../../views/personas/insertar_personas.php';
    }

    // Método para insertar una nueva persona
    public function insertarPersona() {
        // Verificar si el usuario está logueado
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            // Redirigir al login si no está autenticado
            header('Location: ../../../index.php');
            exit();
        }

        // Obtener los datos enviados por el formulario
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

        // Obtener el ID del usuario desde la sesión
        $usuario_id = $_SESSION['usuario_id'];

        // Crear una instancia del modelo Persona
        $personaModel = new Persona();

        // Insertar la persona
        $insertado = $personaModel->insertarPersona($nombres, $primerApellido, $segundoApellido, $telefono, $tipodoc, $numerodoc, $direccion, $iddistrito, $correo, $estado, $usuario_id);

        // Verificar si la persona se insertó correctamente
        if ($insertado) {
            // Redirigir a la vista de lista de personas o mostrar mensaje de éxito
            header('Location: listado_personas.php?mensaje=Persona registrada correctamente.');
            exit();
        } else {
            // En caso de error, redirigir al formulario con un mensaje de error
            header('Location: insertar_personas.php?mensaje=Error al registrar la persona.');
            exit();
        }
    }
}

// Crear una instancia del controlador
$controller = new PersonaController();

// Verificar qué acción realizar según la solicitud
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombres'])) {
    // Llamar al método para insertar persona
    $controller->insertarPersona();
} else {
    // Llamar al método para mostrar el formulario
    $controller->mostrarFormulario();
}
?>
