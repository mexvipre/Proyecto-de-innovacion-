<?php
require_once '../../config/conexion.php';

class EquipoModel {
    private $db;

    public function __construct() {
        // Obtener la conexión a la base de datos
        $this->db = Conexion::conectar();
    }

    // Insertar un nuevo equipo
    public function agregarEquipo($nomEquipo, $idTipoEquipo, $idComputer = null) {
        try {
            if ($idTipoEquipo == 'computadora' || $idTipoEquipo == 'laptop') {
                $sql = "INSERT INTO equipos (NomEquipo, idTipo_Equipos, idComputer) VALUES (:nomEquipo, :idTipoEquipo, :idComputer)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':nomEquipo', $nomEquipo);
                $stmt->bindParam(':idTipoEquipo', $idTipoEquipo);
                $stmt->bindParam(':idComputer', $idComputer);
            } else {
                $sql = "INSERT INTO equipos (NomEquipo, idTipo_Equipos) VALUES (:nomEquipo, :idTipoEquipo)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':nomEquipo', $nomEquipo);
                $stmt->bindParam(':idTipoEquipo', $idTipoEquipo);
            }
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error al agregar equipo: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }

    // Insertar una nueva marca
    public function agregarMarca($idEquipo, $nomMarca, $idTipoEquipo) {
        try {
            $sql = "INSERT INTO marcas (idequipo, NomMarca, idTipo_Equipos) VALUES (:idEquipo, :nomMarca, :idTipoEquipo)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEquipo', $idEquipo);
            $stmt->bindParam(':nomMarca', $nomMarca);
            $stmt->bindParam(':idTipoEquipo', $idTipoEquipo);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error al agregar marca: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }

    // Obtener todos los tipos de equipo
    public function obtenerTiposEquipos() {
        try {
            $sql = "SELECT * FROM tipo_equipos";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener tipos de equipos: " . $e->getMessage(), 3, "errors.log");
            return [];
        }
    }

    // Obtener todos los equipos
    public function obtenerEquipos() {
        try {
            $sql = "SELECT * FROM equipos";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener equipos: " . $e->getMessage(), 3, "errors.log");
            return [];
        }
    }
     //Obtener un equipo por ID
    public function obtenerEquipoPorId($idEquipo) {
        try {
            $sql = "
                SELECT 
                    e.iddetequipo,
                    e.modelo,
                    e.numserie,
                    e.descripcionentrada,
                    e.fechaentrega,
                    c.NombreCategoria AS categoria,
                    s.Nombre_SubCategoria AS subcategoria,
                    m.Nombre_Marca AS marca,
                    ma.idmarcasoc
                FROM detequipos e
                LEFT JOIN marcasasoc ma ON e.idmarcasoc = ma.idmarcasoc
                LEFT JOIN marcas m ON ma.id_marca = m.id_marca
                LEFT JOIN subcategoria s ON ma.id_subcategoria = s.id_subcategoria
                LEFT JOIN categorias c ON s.id_categoria = c.id_categoria
                WHERE e.iddetequipo = :idEquipo
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEquipo', $idEquipo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error al obtener equipo ID {$idEquipo}: " . $e->getMessage(), 3, "errors.log");
            return [];
        }
    }

    // ✅ Actualizar un equipo usando el procedimiento almacenado
    public function actualizarEquipo($idEquipo, $modelo, $numserie, $descripcionentrada) {
        try {
            if ($idEquipo <= 0) {
                return ["status" => "error", "message" => "ID de equipo no válido."];
            }

            if (empty($modelo) && empty($numserie) && empty($descripcionentrada)) {
                return ["status" => "error", "message" => "No se proporcionaron datos para actualizar."];
            }

            $this->db->beginTransaction();

            $sql = "CALL ActualizarEquipo(:id, :modelo, :numserie, :descripcion)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $idEquipo, PDO::PARAM_INT);
            $stmt->bindParam(':modelo', $modelo, PDO::PARAM_STR, 255);
            $stmt->bindParam(':numserie', $numserie, PDO::PARAM_STR, 255);
            $stmt->bindParam(':descripcion', $descripcionentrada, PDO::PARAM_STR);

            $stmt->execute();
            $this->db->commit();

            return ["status" => "success", "message" => "Equipo actualizado correctamente."];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al actualizar equipo ID {$idEquipo}: " . $e->getMessage(), 3, "errors.log");
            return ["status" => "error", "message" => "Error al actualizar: " . $e->getMessage()];
        }
    }
}
?>
