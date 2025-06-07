<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../../config/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idcontrato = $_POST['idcontrato'] ?? '';

    if (empty($idcontrato)) {
        echo json_encode(['success' => false, 'message' => 'ID de contrato no proporcionado.']);
        exit;
    }

    try {
        $conexion = Conexion::conectar();


            // Eliminar primero servicios relacionados con ese contrato
            $stmt = $conexion->prepare("DELETE FROM servicios WHERE idcontrato = :idcontrato");
            $stmt->bindParam(':idcontrato', $idcontrato);
            $stmt->execute();

            // Luego eliminar el contrato
            $stmt = $conexion->prepare("DELETE FROM contratos WHERE idcontrato = :idcontrato");
            $stmt->bindParam(':idcontrato', $idcontrato);
            $stmt->execute();


        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el contrato o ya fue eliminado.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
