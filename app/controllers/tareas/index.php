<?php
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['idpersona'])) {
    die('No se ha iniciado sesión correctamente.');
}

// Incluir archivos necesarios
require_once '../../config/conexion.php';
require_once '../../models/tareas/Tarea.php';

// Conectar a la base de datos
$conexion = Conexion::conectar();
$tarea = new Tarea($conexion);

// Obtener el ID del usuario logueado
$idUsuario = $_SESSION['idpersona']; // El ID del técnico



$tareas = $tarea->obtenerPorTecnico($idUsuario);

foreach ($tareas as &$t) {
    $t['puede_finalizar'] = $tarea->tieneEvidenciasYServicios($t['iddetservicio']);
}




?>
