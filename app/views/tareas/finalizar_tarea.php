<?php
require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iddetservicio']) && isset($_POST['observaciones'])) {
    $id = intval($_POST['iddetservicio']);
    $observaciones = trim($_POST['observaciones']);
    $fechaFin = date('Y-m-d H:i:s');

    try {
        $conexion = Conexion::conectar();

        $sql = "UPDATE detalle_servicios SET fechahorafin = ?, observaciones = ? WHERE iddetservicio = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$fechaFin, $observaciones, $id]);

        header("Location: ../../views/tareas/index.php");
        exit;
    } catch (PDOException $e) {
        echo "Error al actualizar: " . $e->getMessage();
    }
} else {
    echo "Solicitud inválida.";
}
