<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fecha_recepcion = $_POST['fecha_recepcion'] ?? '';
    $documento = $_POST['dni'] ?? '';

    if (empty($fecha_recepcion) || empty($documento)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    // Validar que la sesión tenga idusuario
    if (!isset($_SESSION['idusuario'])) {
        echo json_encode(['success' => false, 'message' => 'Sesión inválida. No se pudo identificar al usuario.']);
        exit;
    }

    try {
        $conexion = Conexion::conectar();

        // Determinar tipo de documento
        if (strlen($documento) === 8) {
            $sql = "SELECT c.idcliente FROM clientes c 
                    INNER JOIN personas p ON c.idpersona = p.idpersona 
                    WHERE p.numerodoc = :documento 
                    LIMIT 1";
        } elseif (strlen($documento) === 11) {
            $sql = "SELECT c.idcliente FROM clientes c 
                    INNER JOIN empresas e ON c.idempresa = e.idempresa 
                    WHERE e.ruc = :documento 
                    LIMIT 1";
        } else {
            echo json_encode(['success' => false, 'message' => 'Documento inválido. Debe tener 8 (DNI) o 11 (RUC) dígitos.']);
            exit;
        }

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':documento', $documento);
        $stmt->execute();

        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $idcliente = $cliente['idcliente'];
            $idusuario_crea = $_SESSION['idusuario']; // ✅ Esta es la clave correcta

            $sql_procedimiento = "CALL crear_orden_servicio(:fecha_recepcion, :idcliente, :idusuario_crea)";
            $stmt = $conexion->prepare($sql_procedimiento);
            $stmt->bindParam(':fecha_recepcion', $fecha_recepcion);
            $stmt->bindParam(':idcliente', $idcliente);
            $stmt->bindParam(':idusuario_crea', $idusuario_crea);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Orden registrada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado con ese documento.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
