<?php
require_once '../../config/conexion.php'; 
header('Content-Type: application/json');

try {
    $conn = Conexion::conectar();

    $sql = "
SELECT 
    CONCAT(p.nombres, ' ', p.Primer_Apellido, ' ', p.Segundo_Apellido) AS cliente,
    u_tecnico.namuser AS tecnico,
    d.modelo AS equipo_modelo,
    d.numserie AS numero_serie,
    ds.observaciones,
    ds.fechahorainicio,
    ds.fechahorafin,
    os.fecha_recepcion
FROM detalle_servicios ds
JOIN detequipos d ON ds.iddetequipo = d.iddetequipo
JOIN orden_de_servicios os ON d.idorden_servicio = os.idorden_Servicio
JOIN clientes c ON os.idcliente = c.idcliente
JOIN personas p ON c.idpersona = p.idpersona
JOIN usuarios u_tecnico ON ds.idusuario_soporte = u_tecnico.idusuario
WHERE ds.fechahorainicio IS NOT NULL
  AND ds.fechahorafin IS NULL
ORDER BY ds.fechahorainicio DESC
LIMIT 25;

    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["data" => $resultados], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
