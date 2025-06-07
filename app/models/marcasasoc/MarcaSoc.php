<?php
// models/marcasasoc/MarcaSoc.php
require_once '../../config/conexion.php';

class MarcaSoc {
    public function obtenerMarcas() {
        $db = Conexion::conectar();
        $sql = "SELECT idmarcasoc FROM marcasasoc ORDER BY idmarcasoc ASC";


        $stmt = $db->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);


        return $resultados;
    }
}
?>
