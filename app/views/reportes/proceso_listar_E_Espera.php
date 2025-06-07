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
    c.NombreCategoria AS categoria,
    s.Nombre_SubCategoria AS subcategoria,
    ds.observaciones,
    ds.fechahorainicio,
    ds.fechahorafin,
    os.fecha_recepcion 
FROM detalle_servicios ds 
JOIN detequipos d ON ds.iddetequipo = d.iddetequipo
JOIN orden_de_servicios os ON d.idorden_servicio = os.idorden_Servicio 
JOIN clientes c2 ON os.idcliente = c2.idcliente 
JOIN personas p ON c2.idpersona = p.idpersona 
JOIN usuarios u_tecnico ON ds.idusuario_soporte = u_tecnico.idusuario 
JOIN marcasasoc masoc ON d.idmarcasoc = masoc.idmarcasoc
JOIN subcategoria s ON masoc.id_subcategoria = s.id_subcategoria
JOIN categorias c ON s.id_categoria = c.id_categoria
WHERE ds.fechahorainicio IS NULL 
ORDER BY os.fecha_recepcion DESC;

 ";



    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["data" => $resultados]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
