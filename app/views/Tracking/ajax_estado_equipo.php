<?php
require_once '../../../app/config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajax']) && $_POST['ajax'] === 'estado_equipo') {
        header('Content-Type: application/json; charset=utf-8');
        $idEquipo = $_POST['id_equipo_ajax'] ?? '';

        if (!empty($idEquipo) && is_numeric($idEquipo)) {
            try {
                $conn = Conexion::conectar();
                $stmt = $conn->prepare("CALL ObtenerEstadoPorEquipo(?)");
                $stmt->execute([$idEquipo]);
                $estado = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($estado) {
                    echo json_encode(['success' => true, 'data' => $estado]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró estado para el equipo.']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de equipo inválido.']);
        }

        exit;
    }
}
