<?php
require_once __DIR__ . '/../../models/UsuarioModel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idcontrato'])) {
    $idcontrato = intval($_POST['idcontrato']);

    $usuarioModel = new UsuarioModel();
    $usuario = $usuarioModel->obtenerUsuarioPorContrato($idcontrato);

    if ($usuario && isset($usuario['idusuario'])) {
        echo json_encode([
            'success' => true,
            'idusuario' => $usuario['idusuario'],
            'namuser' => $usuario['namuser'],
            'passuser' => $usuario['passuser'],
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Petición inválida']);
exit;
