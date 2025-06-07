<?php
require_once '../../config/conexion.php'; 
session_start(); // Asegúrate de iniciar sesión

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['idusuario'])) {
        throw new Exception("Sesión no iniciada correctamente.");
    }

    $idtecnico = $_SESSION['idusuario']; // ID del técnico autenticado

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
        WHERE ds.idusuario_soporte = :idtecnico
          AND ds.observaciones IS NOT NULL
          AND TRIM(ds.observaciones) <> ''
        ORDER BY ds.fechahorafin DESC
        LIMIT 25;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idtecnico', $idtecnico, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["data" => $resultados]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
