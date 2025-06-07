<?php
require_once '../config/conexion.php';

$conexion = new Conexion();
$conn = $conexion->conectar();

if ($conn) {
    echo "Conexión exitosa";
} else {
    echo "Error en la conexión";
}
?>
