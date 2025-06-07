<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../../config/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idusuario = $_POST['idusuario'] ?? '';

    if (empty($idusuario)) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado.']);
        exit;
    }

    try {
        $conexion = Conexion::conectar();

        // Eliminar usuario (credencial)
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE idusuario = :idusuario");
        $stmt->bindParam(':idusuario', $idusuario);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la credencial o ya fue eliminada.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
