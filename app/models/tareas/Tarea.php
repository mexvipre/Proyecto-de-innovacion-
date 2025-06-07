<?php
class Tarea
{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion = $db;
    }


    

public function obtenerPorTecnico($idusuario) {
    $sql = "CALL obtener_servicios_por_persona(:idusuario)";
    $stmt = $this->conexion->prepare($sql);
    $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



public function tieneEvidenciasYServicios($iddetservicio) {
    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM evidencia_tecnica WHERE iddetservicio = :iddetservicio) AS cnt_evidencias,
            (SELECT COUNT(*) FROM servicios WHERE iddetservicio = :iddetservicio) AS cnt_servicios
    ";
    $stmt = $this->conexion->prepare($sql);
    $stmt->bindParam(':iddetservicio', $iddetservicio, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($result['cnt_evidencias'] > 0 && $result['cnt_servicios'] > 0);
}




    
}
?>
