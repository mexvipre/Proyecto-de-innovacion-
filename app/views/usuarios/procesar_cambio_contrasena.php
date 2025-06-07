<?php
session_start();
header('Content-Type: application/json');

$tiempoBloqueo = 30; // 30 segundos de bloqueo
$maxIntentos = 3;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Verificar bloqueo (consulta desde frontend)
    if (isset($data['check_block']) && $data['check_block'] === true) {
        $bloqueo_inicio = $_SESSION['bloqueo_inicio'] ?? null;
        if ($bloqueo_inicio !== null) {
            $tiempoPasado = time() - $bloqueo_inicio;
            if ($tiempoPasado < $tiempoBloqueo) {
                $restante = $tiempoBloqueo - $tiempoPasado;
                echo json_encode([
                    'bloqueado' => true,
                    'tiempo_restante' => $restante,
                    'success' => false,
                    'message' => 'Usuario bloqueado, espera el tiempo restante'
                ]);
                exit;
            } else {
                // Termina bloqueo: reiniciar intentos
                unset($_SESSION['intentos_fallidos']);
                unset($_SESSION['bloqueo_inicio']);
            }
        }
        echo json_encode(['bloqueado' => false, 'success' => true]);
        exit;
    }

    // Validar campos
    if (empty($data['actual_contrasena']) || empty($data['nueva_contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Por favor completa ambos campos.']);
        exit;
    }

    $idusuario = $_SESSION['idusuario'] ?? null;
    if (!$idusuario) {
        echo json_encode(['success' => false, 'message' => 'No se pudo identificar al usuario.']);
        exit;
    }

    $actual_contrasena = $data['actual_contrasena'];
    $nueva_contrasena = $data['nueva_contrasena'];

    require_once '../../config/conexion.php';

    try {
        $conexion = Conexion::conectar();

        // Obtener la contraseña actual almacenada
        $stmt = $conexion->prepare("SELECT passuser FROM usuarios WHERE idusuario = :idusuario");
        $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
            exit;
        }

        // Comparar contraseñas en texto plano
        if ($usuario['passuser'] !== $actual_contrasena) {
            // Intentos fallidos
            if (!isset($_SESSION['intentos_fallidos'])) {
                $_SESSION['intentos_fallidos'] = 0;
            }
            $_SESSION['intentos_fallidos']++;

            if ($_SESSION['intentos_fallidos'] >= $maxIntentos) {
                $_SESSION['bloqueo_inicio'] = time();
                echo json_encode([
                    'success' => false,
                    'message' => "Has excedido los intentos permitidos. Intenta nuevamente en {$tiempoBloqueo} segundos."
                ]);
                exit;
            }

            echo json_encode([
                'success' => false,
                'message' => 'La contraseña actual no es correcta. Intento ' . $_SESSION['intentos_fallidos'] . " de $maxIntentos."
            ]);
            exit;
        }

        // Si contraseña correcta, resetear intentos
        unset($_SESSION['intentos_fallidos']);
        unset($_SESSION['bloqueo_inicio']);

        // Actualizar la contraseña
        $stmtUpdate = $conexion->prepare("UPDATE usuarios SET passuser = :nueva_contrasena, creado_por = :modificado_por WHERE idusuario = :idusuario");
        $stmtUpdate->bindParam(':nueva_contrasena', $nueva_contrasena);
        $stmtUpdate->bindParam(':modificado_por', $idusuario); // quien modifica es el mismo usuario
        $stmtUpdate->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);

        if ($stmtUpdate->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la contraseña.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
