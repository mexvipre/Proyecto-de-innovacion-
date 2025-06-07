<?php
require_once '../../config/conexion.php'; // Ajusta esta ruta si es diferente

$pdo = Conexion::conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iddetservicio = $_POST['iddetservicio'] ?? null;
    $nombre_servicio = trim($_POST['nombre_servicio'] ?? '');
    $precio_sugerido = $_POST['precio_sugerido'] ?? null;

    if (empty($iddetservicio) || empty($nombre_servicio) || !is_numeric($precio_sugerido)) {
        header("Location: ../dashboard/dashboard.php?view=registrar_revision&id=" . urlencode($iddetservicio) . "&error=Datos inválidos");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO servicios (nombre_servicio, precio_sugerido, iddetservicio) VALUES (?, ?, ?)");
        $stmt->execute([$nombre_servicio, $precio_sugerido, $iddetservicio]);

        header("Location: ../dashboard/dashboard.php?view=registrar_revision&id=" . urlencode($iddetservicio) . "&mensaje=Servicio registrado correctamente");
        exit;
    } catch (PDOException $e) {
        $errorMsg = urlencode("Error al registrar: " . $e->getMessage());
        header("Location: ../dashboard/dashboard.php?view=registrar_revision&id=" . urlencode($iddetservicio) . "&error=$errorMsg");
        exit;
    }
} else {
    header("Location: ../dashboard/dashboard.php?view=registrar_revision&id=" . urlencode($iddetservicio) . "&error=Acceso no permitido");
    exit;
}
