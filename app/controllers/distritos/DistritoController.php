<?php
// Activar reporte de errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir el archivo de la clase Distrito
require_once('../../models/distritos/Distrito.php');

// Variables para mensajes de éxito o error
$mensaje = "";
$tipoMensaje = ""; // Puede ser 'success' o 'danger'

// Verificar que los datos del formulario se hayan enviado por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $provincia = $_POST['provincia'];
    $departamento = $_POST['departamento'];

    // Crear una instancia de la clase Distrito
    $distrito = new Distrito();

    // Intentar agregar el distrito a la base de datos
    try {
        $resultado = $distrito->agregarDistrito($nombre, $provincia, $departamento);

        // Si la inserción fue exitosa
        if ($resultado) {
            $mensaje = "✅ ¡Distrito agregado correctamente!";
            $tipoMensaje = "success";
        } else {
            // Si algo falló, mostrar mensaje de error
            $mensaje = "❌ Error al agregar el distrito. Inténtalo nuevamente.";
            $tipoMensaje = "danger";
        }
    } catch (Exception $e) {
        // Si ocurre una excepción, mostrar el error en el log y en la página
        error_log($e->getMessage()); // Loguear el error
        $mensaje = "❌ Error al agregar el distrito: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}
?>