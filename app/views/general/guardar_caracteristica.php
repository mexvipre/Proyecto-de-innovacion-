<?php
// Incluir el archivo de conexión a la base de datos
require_once __DIR__ . '../../../config/conexion.php';

// Establecer conexión a la base de datos
$conn = Conexion::conectar();

// Verificar que la solicitud sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados desde el formulario
    $iddetequipo = isset($_POST['iddetequipo']) ? (int)$_POST['iddetequipo'] : 0;
    $tipo_equipo = isset($_POST['tipo_equipo']) ? trim($_POST['tipo_equipo']) : '';
    $caracteristicas = isset($_POST['caracteristicas']) ? $_POST['caracteristicas'] : [];
    $idorden_servicio = $_GET['id'] ?? null;
    $nombre_cliente = $_GET['cliente'] ?? 'Cliente no especificado';

    // Validar los datos recibidos
    if ($iddetequipo <= 0 || empty($caracteristicas) || empty($tipo_equipo)) {
        error_log("Error: Faltan datos - iddetequipo: $iddetequipo, tipo_equipo: $tipo_equipo, caracteristicas: " . json_encode($caracteristicas));
        header("Location: /AndreaM/app/views/dashboard/dashboard.php?view=detallesequipos&id=$idorden_servicio&cliente=" . urlencode($nombre_cliente) . "&error=Faltan datos para guardar las características");
        exit;
    }

    // Iniciar una transacción
    $conn->beginTransaction();
    try {
        // Obtener especificaciones válidas para este tipo de equipo
        $stmt_valid_specs = $conn->prepare("CALL obtener_especificaciones_por_categoria(?)");
        $stmt_valid_specs->execute([$tipo_equipo]);
        $valid_specs = $stmt_valid_specs->fetchAll(PDO::FETCH_ASSOC);
        $valid_spec_ids = array_column($valid_specs, 'id_especificacion');
        $valid_spec_names = array_map('strtolower', array_column($valid_specs, 'especificacion'));
        error_log("Valid spec IDs for $tipo_equipo: " . json_encode($valid_spec_ids));
        error_log("Valid spec names for $tipo_equipo: " . json_encode($valid_spec_names));
        $stmt_valid_specs->closeCursor();

        // Procesar cada característica enviada
        foreach ($caracteristicas as $index => $car) {
            $idcaracteristica = isset($car['idcaracteristica']) ? (int)$car['idcaracteristica'] : 0;
            $id_especificacion = isset($car['id_especificacion']) ? (int)$car['id_especificacion'] : 0;
            $valor = trim($car['valor'] ?? '');
            $especificacion = trim($car['especificacion'] ?? '');

            error_log("Procesando característica [$index]: idcaracteristica=$idcaracteristica, id_especificacion=$id_especificacion, especificacion=$especificacion, valor=$valor");

            // Validar que la especificación sea válida para el tipo de equipo
            if ($id_especificacion > 0 && !in_array($id_especificacion, $valid_spec_ids)) {
                error_log("Especificación no válida para $tipo_equipo: ID $id_especificacion, Nombre: $especificacion");
                continue;
            }

            // Si no hay id_especificacion, buscar la especificación por nombre
            if ($id_especificacion <= 0 && !empty($especificacion)) {
                $stmt = $conn->prepare("SELECT id_especificacion FROM especificaciones WHERE LOWER(especificacion) = LOWER(?)");
                $stmt->execute([$especificacion]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                if ($resultado && in_array($resultado['id_especificacion'], $valid_spec_ids)) {
                    $id_especificacion = $resultado['id_especificacion'];
                    error_log("Especificación encontrada por nombre: $especificacion, ID: $id_especificacion");
                } else {
                    error_log("Especificación no encontrada o no válida para $tipo_equipo: $especificacion");
                    continue;
                }
            }

            // Guardar o actualizar la característica
            if ($id_especificacion > 0 && !empty($valor)) {
                if ($idcaracteristica > 0) {
                    $stmt = $conn->prepare("UPDATE caracteristicas SET id_especificacion = ?, valor = ? WHERE id_caracteristica = ? AND iddetequipo = ?");
                    $stmt->execute([$id_especificacion, $valor, $idcaracteristica, $iddetequipo]);
                    error_log("Actualizada característica ID $idcaracteristica: Especificación ID $id_especificacion, Valor: $valor, Equipo ID $iddetequipo");
                } else {
                    $stmt = $conn->prepare("INSERT INTO caracteristicas (id_especificacion, valor, iddetequipo) VALUES (?, ?, ?)");
                    $stmt->execute([$id_especificacion, $valor, $iddetequipo]);
                    error_log("Insertada nueva característica: Especificación ID $id_especificacion, Valor: $valor, Equipo ID $iddetequipo");
                }
            } else {
                error_log("Omitida característica [$index]: id_especificacion=$id_especificacion, valor='$valor' (no válida o sin valor)");
            }
        }

        $conn->commit();
        header("Location: /AndreaM/app/views/dashboard/dashboard.php?view=detallesequipos&id=$idorden_servicio&cliente=" . urlencode($nombre_cliente) . "&success=Características guardadas correctamente");
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error al guardar características: " . $e->getMessage());
        header("Location: /AndreaM/app/views/dashboard/dashboard.php?view=detallesequipos&id=$idorden_servicio&cliente=" . urlencode($nombre_cliente) . "&error=Error al guardar las características: " . urlencode($e->getMessage()));
        exit;
    }
}
?>