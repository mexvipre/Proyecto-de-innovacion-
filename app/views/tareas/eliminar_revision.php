<?php
require_once '../../config/conexion.php';

$idservicio = $_GET['idservicio'] ?? null;
$iddetservicio = $_GET['iddetservicio'] ?? null;

if (!$idservicio || !$iddetservicio) {
    header("Location: registrar_revision.php?id=$iddetservicio&error=Datos incompletos");
    exit;
}

$pdo = Conexion::conectar();

try {
    $stmt = $pdo->prepare("DELETE FROM servicios WHERE idservicio = ?");
    $stmt->execute([$idservicio]);

    header("Location: ../dashboard/dashboard.php?view=registrar_revision&id=" . urlencode($iddetservicio) . "&mensaje=Revisión eliminada");

    exit;
} catch (PDOException $e) {
    $error = urlencode("Error al eliminar: " . $e->getMessage());
    header("Location: ../dashboard/dashboard.php?view=registrar_revision&id=" . urlencode($iddetservicio) . "&error=$error");

    exit;
}
