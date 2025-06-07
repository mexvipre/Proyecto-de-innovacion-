<?php
require_once __DIR__ . '../../../config/conexion.php'; // Ruta absoluta al archivo de configuración

class Contrato {
    private $db;

    public function __construct() {
        // Obtener la conexión a la base de datos
        $this->db = Conexion::conectar();
    }

    // Insertar un nuevo contrato
    public function crearContrato($idPersona, $idRol, $fechaInicio, $fechaFin, $observaciones) {
        try {
            $sql = "INSERT INTO contratos (idpersona, idrol, fecha_inicio, fecha_fin, observaciones, fecha_creacion) 
                    VALUES (:idPersona, :idRol, :fechaInicio, :fechaFin, :observaciones, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idPersona', $idPersona);
            $stmt->bindParam(':idRol', $idRol);
            $stmt->bindParam(':fechaInicio', $fechaInicio);
            $stmt->bindParam(':fechaFin', $fechaFin);
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error al agregar contrato: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }

    // Verificar si la persona ya tiene un contrato activo
    public function obtenerContratoPorPersona($idpersona) {
        try {
            $sql = "SELECT * FROM contratos WHERE idpersona = :idpersona AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idpersona', $idpersona);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);  // Si encuentra un contrato activo, lo devuelve
        } catch (PDOException $e) {
            error_log("Error al verificar contrato: " . $e->getMessage(), 3, "errors.log");
            return null;
        }
    }

    // Obtener todos los contratos
    public function obtenerContratos() {
    try {
        $sql = "SELECT 
                    c.idcontrato,
                    c.idpersona,
                    p.nombres,
                    p.Primer_Apellido,
                    p.Segundo_Apellido,
                    r.rol,
                    c.fecha_inicio,
                    c.fecha_fin,
                    c.observaciones,
                    c.fecha_creacion,
                    u.estado
                FROM contratos c
                LEFT JOIN personas p ON c.idpersona = p.idpersona
                LEFT JOIN roles r ON c.idrol = r.idrol
                LEFT JOIN usuarios u ON u.idcontrato = c.idcontrato";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener contratos: " . $e->getMessage(), 3, "errors.log");
        return [];
    }
}

    // Eliminar contrato por ID
    public function eliminarContrato($idContrato) {
        try {
            $sql = "DELETE FROM contratos WHERE idcontrato = :idContrato";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idContrato', $idContrato);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error al eliminar contrato: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }

    
}
?>
