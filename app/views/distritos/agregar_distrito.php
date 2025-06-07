<?php

require_once('../../controllers/distritos/DistritoController.php');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Agregar Distrito</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .container-form {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            padding: 40px;
            transition: transform 0.3s ease;
            width: 100%;
            max-width: 550px;
        }

        .card-title {
            font-size: 26px;
            font-weight: 700;
            color: #343a40;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-label {
            font-size: 16px;
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 14px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 12px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<!-- MENSAJE DE ÉXITO O ERROR -->
<?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert" id="alert">
        <?php echo $mensaje; ?>
    </div>
<?php endif; ?>

<div class="container container-form">
    <div class="card">
        <h2 class="card-title">Agregar Distrito</h2>
        <form action="" method="POST">
            <!-- Nombre -->
            <div class="mb-4">
                <label for="nombre" class="form-label">Nombre del distrito:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required />
            </div>

            <!-- Provincia -->
            <div class="mb-4">
                <label for="provincia" class="form-label">Provincia:</label>
                <input type="text" id="provincia" name="provincia" class="form-control" required />
            </div>

            <!-- Departamento -->
            <div class="mb-4">
                <label for="departamento" class="form-label">Departamento:</label>
                <input type="text" id="departamento" name="departamento" class="form-control" required />
            </div>

            <!-- Botón -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alert = document.getElementById('alert');
        if (alert) {
            setTimeout(() => {
                alert.classList.remove('show');
                alert.classList.add('fade');
            }, 5000);
        }
    });
</script>

</body>
</html>
