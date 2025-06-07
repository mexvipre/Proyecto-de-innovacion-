<?php
require_once '../../config/conexion.php'; // Conexión a la BD

$conn = Conexion::conectar(); 
session_start();  // Inicia la sesión

// Verifica si una variable de sesión está definida antes de usarla
if (isset($_SESSION['usuario'])) {
    echo "Usuario: " . $_SESSION['usuario'];
} else {
    echo "No hay usuario en la sesión.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo_registro'];

    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    if ($tipo === 'persona') {
        $nombres = $_POST['nombres'];
        $primerApellido = $_POST['Primer_Apellido'];
        $segundoApellido = $_POST['Segundo_Apellido'];
        $telefono = $_POST['telefono'];
        $tipodoc = $_POST['tipodoc'];
        $numerodoc = $_POST['numerodoc'];
        $direccion = $_POST['direccion'];
        $distrito = $_POST['iddistrito'];
        $correo = $_POST['correo'];
        $estado = $_POST['estado'];

        if (empty($distrito)) {
            echo "❌ El campo Distrito es obligatorio.";
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO personas (nombres, Primer_Apellido, Segundo_Apellido, telefono, tipodoc, numerodoc, direccion, iddistrito, correo, estado)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nombres,
                $primerApellido,
                $segundoApellido,
                $telefono,
                $tipodoc,
                $numerodoc,
                $direccion,
                $distrito,
                $correo,
                $estado
            ]);
            echo "✅ Persona registrada correctamente.";
        } catch (PDOException $e) {
            echo "❌ Error al registrar persona: " . $e->getMessage();
        }

    } elseif ($tipo === 'empresa') {
        $ruc = $_POST['ruc'];
        $razon = $_POST['razon_social'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['email'];
        $direccion = $_POST['direccion'];
        $distrito = $_POST['iddistrito'];

        if (empty($distrito)) {
            echo "❌ El campo Distrito es obligatorio.";
            exit;
        }

        try {
            // Registrar la empresa
            $stmt = $conn->prepare("INSERT INTO empresas (ruc, razon_social, telefono, email, direccion, iddistrito)
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ruc, $razon, $telefono, $correo, $direccion, $distrito]);

            // Obtener el idcliente (último insertado)
            $idcliente = $conn->lastInsertId();
            
            // Asegúrate de que el idcliente ha sido obtenido correctamente
            if (!$idcliente) {
                echo "❌ No se pudo obtener el ID del cliente.";
                exit;
            }

            // Verifica que la sesión del usuario esté activa
            if (!isset($_SESSION['idusuario'])) {
                echo "❌ Sesión de usuario no disponible.";
                exit;
            }
            
            // Obtener el idusuario de la sesión
            $idusuario = $_SESSION['idusuario'];

            // Insertar la orden de servicio
            $stmtOrden = $conn->prepare("INSERT INTO orden_de_servicios (idcliente, idusuario_crea, fecha_creacion, estado)
                                         VALUES (?, ?, NOW(), 'pendiente')");
            $stmtOrden->execute([$idcliente, $idusuario]);

            echo "✅ Empresa registrada correctamente y orden generada.";

        } catch (PDOException $e) {
            echo "❌ Error al registrar empresa: " . $e->getMessage();
        }

    } else {
        echo "❌ Tipo de registro inválido.";
    }
} else {
    echo "⚠️ Acceso no permitido.";
}
