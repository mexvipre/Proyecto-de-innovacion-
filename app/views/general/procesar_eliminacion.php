<?php
header('Content-Type: application/json');
require_once __DIR__ . '../../../config/conexion.php';
$conn = Conexion::conectar();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    var_dump($id); // Verificar el valor del ID

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Llamar al procedimiento almacenado
        $query = "CALL eliminar_equipo(?)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();

        // Verificar si el procedimiento se ejecutó correctamente
        if ($stmt->rowCount() > 0) {
            $conn->commit();
            echo json_encode(["status" => "success"]);
        } else {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "No se eliminó ningún registro. ID: $id"]);
        }
    } catch (Exception $e) {
        // En caso de error, revertir la transacción
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "ID no recibido"]);
}
?>
