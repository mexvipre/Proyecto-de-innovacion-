<?php
// models/evidencias_entrada/Evidencia.php
require_once '../../config/conexion.php';

class Evidencia {
    public function obtenerUltimaEvidencia() {
        $db = Conexion::conectar();
        $sql = "SELECT idEvidencia FROM evidencias_entrada ORDER BY idEvidencia DESC LIMIT 1"; // Ordenamos y limitamos a 1
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve solo el último registro
    }
}
?>
