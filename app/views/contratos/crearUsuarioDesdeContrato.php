<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../../config/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idcontrato = $_POST['idcontrato'] ?? '';
    $namuser = trim($_POST['namuser'] ?? '');
    $passuser = $_POST['passuser'] ?? '';
    $fecha_creacion = $_POST['fecha_creacion'] ?? '';
    $idpersona_crea = $_SESSION['idpersona'] ?? null;

    if (empty($idcontrato) || empty($namuser) || empty($passuser) || empty($fecha_creacion) || empty($idpersona_crea)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    try {
        $conexion = Conexion::conectar();

        // Verificar si ya existe un usuario para ese contrato
        $verificarContrato = $conexion->prepare("SELECT idusuario FROM usuarios WHERE idcontrato = ?");
        $verificarContrato->execute([$idcontrato]);
        $usuarioExistente = $verificarContrato->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {
            // Si existe, actualizar los datos
            $sqlUpdate = "UPDATE usuarios 
                          SET namuser = :namuser, passuser = :passuser, creado_por = :creado_por, fecha_creacion = :fecha_creacion
                          WHERE idcontrato = :idcontrato";

            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':namuser', $namuser);
            $stmtUpdate->bindParam(':passuser', $passuser);
            $stmtUpdate->bindParam(':creado_por', $idpersona_crea);
            $stmtUpdate->bindParam(':fecha_creacion', $fecha_creacion);
            $stmtUpdate->bindParam(':idcontrato', $idcontrato);

            $stmtUpdate->execute();

            echo json_encode(['success' => true, 'message' => 'Credenciales actualizadas correctamente.']);
        } else {
            // Si no existe, insertar nuevo usuario
            $sqlInsert = "INSERT INTO usuarios (idcontrato, namuser, passuser, creado_por, fecha_creacion)
                          VALUES (:idcontrato, :namuser, :passuser, :creado_por, :fecha_creacion)";

            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bindParam(':idcontrato', $idcontrato);
            $stmtInsert->bindParam(':namuser', $namuser);
            $stmtInsert->bindParam(':passuser', $passuser);
            $stmtInsert->bindParam(':creado_por', $idpersona_crea);
            $stmtInsert->bindParam(':fecha_creacion', $fecha_creacion);

            $stmtInsert->execute();

            echo json_encode(['success' => true, 'message' => 'Usuario registrado correctamente.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
