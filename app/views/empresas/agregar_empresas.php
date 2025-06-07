<?php
require_once '../../controllers/empresas/empresasController.php';

$empresaController = new EmpresaController();
$distritos = $empresaController->obtenerDistritos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ruc = $_POST['ruc'];
    $razonsocial = $_POST['razon_social'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $createBy = 1; // Puedes cambiar esto según el usuario
    $iddistritos = $_POST['distritos'];
    $modifiedBy = 1;

    $resultado = $empresaController->agregarEmpresa($ruc, $razonsocial, $telefono, $email, $direccion, $createBy, $iddistritos, $modifiedBy);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agregar Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: -800px;
            margin-top: 50px;
        }


    </style>
</head>
<body>
    <div class="container">
    
            <div class="card-header text-center">
                <h1>Registro de Nueva Empresa</h1>
                <br>
            </div>
      
                <?php if (isset($resultado)): ?>
                    <div class="alert <?= $resultado['success'] ? 'alert-success' : 'alert-danger' ?>" role="alert">
                        <?= htmlspecialchars($resultado['message']) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" novalidate>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="ruc" class="form-label">RUC</label>
                            <input type="text" class="form-control" id="ruc" name="ruc" required autofocus />
                        </div>

                        <div class="col-md-4">
                            <label for="razon_social" class="form-label">Razón Social</label>
                            <input type="text" class="form-control" id="razon_social" name="razon_social" required />
                        </div>

                        <div class="col-md-4">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required />
                        </div>

                        <div class="col-md-4">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required />
                        </div>

                        <div class="col-md-4">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required />
                        </div>

                        <div class="col-md-4">
                            <label for="iddistrito" class="form-label">Distrito</label>
                            <select name="distritos" class="form-select" required>
                                <option value="">Seleccione un distrito</option>
                                <?php foreach ($distritos as $distrito): ?>
                                    <option value="<?= htmlspecialchars($distrito['iddistrito']) ?>">
                                        <?= htmlspecialchars($distrito['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary px-5">Guardar Empresa</button>
                    </div>
                </form>
 
    </div>

<script>
document.getElementById('ruc').addEventListener('change', function() {
  const ruc = this.value;
  if (ruc.length > 0) {
    fetch('/andream/app/views/empresas/consulta_ruc.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'ruc=' + encodeURIComponent(ruc)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.getElementById('razon_social').value = data.data.razonSocial || '';
        document.getElementById('direccion').value = data.data.direccion || '';

        document.getElementById('telefono').value = data.data.telefono || '';
        document.getElementById('email').value = data.data.email || '';

        // Aquí también puedes setear distrito si tienes el ID
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Error en la consulta:', err);
    });
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
