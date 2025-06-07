<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../config/conexion.php';

use Google\Cloud\Storage\StorageClient;

putenv('GOOGLE_APPLICATION_CREDENTIALS=../../config/credencial.json');

$pdo = Conexion::conectar();

if (!isset($pdo)) {
    die("❌ Error: No se pudo conectar a la base de datos.");
}

$input = json_decode(file_get_contents("php://input"), true);

if (isset($input["imagen"]) && isset($input["nombre_imagen"]) && isset($input["iddetequipo"])) {
    $imagenBase64 = $input["imagen"];
    $nombreImagen = preg_replace("/[^a-zA-Z0-9_-]/", "_", $input["nombre_imagen"]);
    $iddetequipo = $input["iddetequipo"];

    if (preg_match('/^data:image\/(\w+);base64,/', $imagenBase64, $tipo)) {
        $tipo = strtolower($tipo[1]);
        if (!in_array($tipo, ['jpg', 'jpeg', 'png'])) {
            echo json_encode(["status" => "error", "message" => "❌ Error: Solo se permiten archivos JPG, JPEG o PNG."]);
            exit;
        }

        $nombreArchivo = $nombreImagen . "." . $tipo;

        $imagenBase64 = substr($imagenBase64, strpos($imagenBase64, ',') + 1);
        $imagenBinaria = base64_decode($imagenBase64);

        if ($imagenBinaria === false) {
            echo json_encode(["status" => "error", "message" => "❌ Error: No se pudo procesar la imagen."]);
            exit;
        }

        $rutaTemporal = sys_get_temp_dir() . "/" . $nombreArchivo;
        file_put_contents($rutaTemporal, $imagenBinaria);
    } else {
        echo json_encode(["status" => "error", "message" => "❌ Error: Formato de imagen inválido."]);
        exit;
    }

    try {
        $bucketName = "evidencias_general";
        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $objeto = $bucket->upload(fopen($rutaTemporal, 'r'), [
            'name' => "evidencia_salida/" . $nombreArchivo
        ]);
    
        $objeto->update([], ['predefinedAcl' => 'PUBLICREAD']);
        $url = "https://storage.googleapis.com/$bucketName/evidencia_salida/$nombreArchivo";

        // Insertar la evidencia en la tabla evidencia_tecnica
        $stmt = $pdo->prepare("INSERT INTO evidencia_tecnica (imagen_tecnico, iddetservicio) VALUES (:imagen_tecnico, :iddetservicio)");
        $stmt->execute([
            'imagen_tecnico' => $url,
            'iddetservicio' => $iddetequipo
        ]);

        $idEvidencia = $pdo->lastInsertId();

        // Vincular la evidencia al equipo
        $update = $pdo->prepare("UPDATE detequipos SET idEvidencia_Tecnica = :idEvidencia WHERE iddetequipo = :iddetequipo");
        $update->execute([
            'idEvidencia' => $idEvidencia,
            'iddetequipo' => $iddetequipo
        ]);

        if ($update->rowCount() > 0) {
            echo json_encode([
                "status" => "success", 
                "message" => "✅ Imagen subida y vinculada correctamente al equipo #$iddetequipo."
            ]);
        } else {
            echo json_encode([
                "status" => "warning", 
                "message" => "⚠️ Imagen guardada, pero no se vinculó a ningún equipo. Verifica si el ID $iddetequipo existe en la tabla `detequipos`."
            ]);
            error_log("❗ Posible fallo: no se encontró el iddetequipo $iddetequipo en la tabla detequipos.");
        }

        unlink($rutaTemporal);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error", 
            "message" => "❌ Error al subir la imagen: " . $e->getMessage()
        ]);
        error_log("❌ Excepción en subida: " . $e->getMessage());
    }
} else {
    echo json_encode(["status" => "error", "message" => "❌ Error: Se requiere una imagen, un nombre y un ID de equipo."]);
}
?>
