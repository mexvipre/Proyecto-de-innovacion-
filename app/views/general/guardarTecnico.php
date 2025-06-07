<?php
// guardarTecnico.php

$conexion = new mysqli("localhost", "root", "", "compuservic");
if ($conexion->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Conexión fallida: ' . $conexion->connect_error
    ]));
}

// Recibir los datos del formulario
$iddetequipo = isset($_POST['iddetequipo']) ? intval($_POST['iddetequipo']) : 0;
$idPersonaTecnico = isset($_POST['idTecnico']) ? intval($_POST['idTecnico']) : 0;

if ($iddetequipo > 0 && $idPersonaTecnico > 0) {
    
    // Primero buscar el idusuario relacionado al idPersonaTecnico
    $sqlBuscarUsuario = "
        SELECT u.idusuario
        FROM usuarios u
        INNER JOIN contratos c ON u.idcontrato = c.idcontrato
        WHERE c.idpersona = ?
        LIMIT 1
    ";
    $stmtBuscar = $conexion->prepare($sqlBuscarUsuario);
    $stmtBuscar->bind_param("i", $idPersonaTecnico);
    $stmtBuscar->execute();
    $stmtBuscar->bind_result($idUsuarioTecnico);
    $stmtBuscar->fetch();
    $stmtBuscar->close();

    if ($idUsuarioTecnico) {
        // Verificar si ya hay un técnico asignado
        $sqlCheck = "SELECT idusuario_soporte FROM detalle_servicios WHERE iddetequipo = ?";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $iddetequipo);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            // Actualizar técnico asignado
            $sqlUpdate = "UPDATE detalle_servicios SET idusuario_soporte = ? WHERE iddetequipo = ?";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            if ($stmtUpdate) {
                $stmtUpdate->bind_param("ii", $idUsuarioTecnico, $iddetequipo);
                if ($stmtUpdate->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Técnico actualizado correctamente.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al actualizar: ' . $stmtUpdate->error
                    ]);
                }
                $stmtUpdate->close();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error preparando actualización: ' . $conexion->error
                ]);
            }
        } else {
            // Insertar nuevo técnico
            $sqlInsert = "INSERT INTO detalle_servicios (iddetequipo, idusuario_soporte) VALUES (?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            if ($stmtInsert) {
                $stmtInsert->bind_param("ii", $iddetequipo, $idUsuarioTecnico);
                if ($stmtInsert->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Técnico asignado correctamente.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al insertar: ' . $stmtInsert->error
                    ]);
                }
                $stmtInsert->close();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error preparando inserción: ' . $conexion->error
                ]);
            }
        }
        $stmtCheck->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró un usuario asociado al técnico seleccionado.'
        ]);
    }

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos para asignar o actualizar técnico.'
    ]);
}

$conexion->close();
?>
