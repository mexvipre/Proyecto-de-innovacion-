<?php
require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iddetservicio'])) {
    $id = intval($_POST['iddetservicio']);
    $fechaInicio = date('Y-m-d H:i:s');

    try {
        $conexion = Conexion::conectar(); // Llama a tu clase para obtener la conexión

        $sql = "UPDATE detalle_servicios SET fechahorainicio = ? WHERE iddetservicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$fechaInicio, $id]);

        header("Location: ../../views/tareas/index.php");
        exit;
    } catch (PDOException $e) {
        echo "Error al actualizar: " . $e->getMessage();
    }
} else {
    echo "Solicitud inválida.";
}
