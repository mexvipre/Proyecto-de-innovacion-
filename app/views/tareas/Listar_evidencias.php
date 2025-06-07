<?php
require_once '../../config/conexion.php';  
// Obtener el ID del servicio desde la URL
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Obtener la conexión usando la clase Conexion
        $pdo = Conexion::conectar();

        // Llamar al procedimiento almacenado para obtener las evidencias
        $stmt = $pdo->prepare("CALL listar_evidencias_por_servicio(:id_servicio)");
        $stmt->bindParam(':id_servicio', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Obtener las evidencias devueltas por el procedimiento
        $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Verificar si hay evidencias
        if (!empty($evidencias)) {
            // Agrega este echo para verificar el HTML
            echo '<div class="row">';
            foreach ($evidencias as $evidencia) {
                echo '<div class="col-md-4 mb-3">';
                echo '<img src="' . htmlspecialchars($evidencia['imagen_tecnico']) . '" class="img-fluid rounded" />';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p class="text-muted">No hay evidencias disponibles.</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="text-danger">Error en la conexión o en la consulta: ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p class="text-danger">ID de servicio no válido.</p>';
}
?>
