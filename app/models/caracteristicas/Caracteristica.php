<?php
require_once '../../config/conexion.php';

class Caracteristica {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para obtener el último id_caracteristica
    public function obtenerUltimaCaracteristica() {
        try {
            // Consulta para obtener la última característica registrada
            $query = "SELECT id_caracteristica, valor FROM caracteristicas ORDER BY id_caracteristica DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve solo el último registro
        } catch (PDOException $e) {
            error_log("Error al obtener la última característica: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }
}
?>
