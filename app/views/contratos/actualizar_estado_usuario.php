<?php
// Asegurate de que la ruta sea correcta según tu estructura
require_once __DIR__ . '/../../models/UsuarioModel.php';

// Establecer cabecera JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idcontrato = $_POST['idcontrato'] ?? null;
    $estado = $_POST['estado'] ?? null;

    // Validación de parámetros
    if (!is_numeric($idcontrato) || !in_array($estado, ['0', '1'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros inválidos']);
        exit();
    }

    // Instancia del modelo correcto
    $usuarioModel = new UsuarioModel();
    $resultado = $usuarioModel->actualizarEstadoPorContrato((int)$idcontrato, (int)$estado);

    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo actualizar el estado']);
    }
} else {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido']);
}
