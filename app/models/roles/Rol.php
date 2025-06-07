<?php
require_once '../../config/conexion.php';

class Rol {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function insertarRol($rol, $descripcion) {
        try {
            $stmt = $this->db->prepare("CALL sp_insertar_rol(:rol, :descripcion)");
            $stmt->bindParam(':rol', $rol, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al insertar rol: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerRoles() {
        try {
            $stmt = $this->db->query("SELECT * FROM roles");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar roles: " . $e->getMessage());
            return [];
        }
    }
}
?>
