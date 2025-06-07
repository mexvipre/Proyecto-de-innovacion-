<?php
require_once __DIR__ . '/../../models/contratos/Contrato.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idpersona = $_POST['idpersona'] ?? null;
    $idrol = $_POST['idrol'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $observaciones = $_POST['observaciones'] ?? null;

    if (!$idpersona || !$idrol || !$fecha_inicio || !$fecha_fin) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    $contratoModel = new Contrato();
    $contratoExistente = $contratoModel->obtenerContratoPorPersona($idpersona);

    if ($contratoExistente) {
        echo json_encode(['success' => false, 'message' => 'Esta persona ya tiene un contrato activo.']);
    } else {
        $nuevoContrato = new Contrato();
        $nuevoContrato->crearContrato($idpersona, $idrol, $fecha_inicio, $fecha_fin, $observaciones);
        echo json_encode(['success' => true, 'message' => 'Contrato registrado con éxito.']);
    }
}
?>
