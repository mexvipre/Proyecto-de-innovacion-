<?php
require_once '../../controllers/empresas/empresasController.php';

$empresaController = new EmpresaController();
$mensaje = "";

// Si es una solicitud POST, actualiza la empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idempresa = $_POST['idempresa'];
    $ruc = $_POST['ruc'];
    $razon_social = $_POST['razon_social'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $iddistrito = $_POST['iddistrito'];
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $usuario_id = $_SESSION['idpersona'] ?? null;

    $resultado = $empresaController->actualizarEmpresa($idempresa, $ruc, $razon_social, $telefono, $email, $direccion, $iddistrito, $usuario_id);

    if ($resultado['success']) {
        $mensaje = "✅ Empresa actualizada correctamente.";
    } else {
        $mensaje = "❌ Error: " . $resultado['message'] . "<br>Debug: " . print_r($resultado, true);
    }

    // Obtener los datos actualizados
    $empresa = $empresaController->obtenerEmpresaPorId($idempresa);
    $distritos = $empresaController->obtenerDistritos();
} 
// Si es una solicitud GET, muestra el formulario con datos actuales
elseif (isset($_GET['idempresa'])) {
    $idempresa = $_GET['idempresa'];
    $empresa = $empresaController->obtenerEmpresaPorId($idempresa);
    $distritos = $empresaController->obtenerDistritos();

    if (!$empresa) {
        echo "No se encontró la empresa.";
        exit;
    }
} else {
    echo "No se proporcionó un ID de empresa.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center mb-4">Editar Empresa</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST">
        <input type="hidden" name="idempresa" value="<?= htmlspecialchars($empresa['idempresa']) ?>">

        <div class="row mb-3">
            <!-- Columna 1 -->
            <div class="col-md-6">
                <label for="ruc" class="form-label">RUC</label>
                <input type="text" class="form-control" id="ruc" name="ruc" value="<?= htmlspecialchars($empresa['ruc']) ?>" required autofocus>
            </div>

            <!-- Columna 2 -->
            <div class="col-md-6">
                <label for="razon_social" class="form-label">Razón Social</label>
                <input type="text" class="form-control" id="razon_social" name="razon_social" value="<?= htmlspecialchars($empresa['razon_social']) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <!-- Columna 1 -->
            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($empresa['telefono']) ?>" required>
            </div>

            <!-- Columna 2 -->
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($empresa['email']) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <!-- Columna 1 -->
            <div class="col-md-6">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="direccion" class="form-control" id="direccion" name="direccion" value="<?= htmlspecialchars($empresa['direccion']) ?>" required>
            </div>

            <!-- Columna 2 -->
            <div class="col-md-6">
                <label for="iddistrito" class="form-label">Distrito</label>
                <select class="form-control" id="iddistrito" name="iddistrito" required>
                    <?php foreach ($distritos as $d): ?>
                        <option value="<?= $d['iddistrito'] ?>" <?= $empresa['iddistrito'] == $d['iddistrito'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Empresa</button>
        <a href="dashboard.php?view=empresas" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
