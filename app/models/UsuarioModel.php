<?php
// Incluyendo la conexión a la base de datos
require_once __DIR__ . '/../config/conexion.php';


class UsuarioModel {
    private $pdo;

    public function __construct() {
        // Crear una instancia de la clase Conexion y obtener la conexión
        $conexion = new Conexion();
        $this->pdo = $conexion->conectar(); // Establecer la conexión PDO
    }

    public function validarUsuario($usuario, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.idusuario, u.namuser, u.passuser, u.estado, 
                       p.nombres, p.Primer_Apellido, p.Segundo_Apellido, p.idpersona
                FROM usuarios u
                JOIN contratos c ON u.idcontrato = c.idcontrato
                JOIN personas p ON c.idpersona = p.idpersona
                WHERE u.namuser = :usuario
                AND u.estado = 1
                LIMIT 1
            ");
            
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuarioValido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuarioValido && $usuarioValido['passuser'] === $password) {
                return $usuarioValido;
            }

            return false;

        } catch (PDOException $e) {
            error_log("Error al validar usuario: " . $e->getMessage());
            return false;
        }
    }


    public function obtenerUsuarioPorContrato($idcontrato) {
    $sql = "SELECT * FROM usuarios WHERE idcontrato = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$idcontrato]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    // ✅ Método nuevo: actualizar el estado del usuario basado en idcontrato
    public function actualizarEstadoPorContrato($idcontrato, $estado) {
        try {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET estado = :estado WHERE idcontrato = :idcontrato");
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->bindParam(':idcontrato', $idcontrato, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }
}
?>
