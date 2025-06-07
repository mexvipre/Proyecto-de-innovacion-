<?php
session_start();

// Si el usuario ya está logueado, redirigir al Dashboard
if (isset($_SESSION['usuario'])) {
    header("Location: app/views/dashboard/dashboard.php");
    exit();
}

// Incluimos las clases necesarias
require_once 'app/config/conexion.php';
require_once 'app/models/UsuarioModel.php';
require_once 'app/models/PersonaModel.php';

$conexion = new Conexion();
$conn = $conexion->conectar();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($usuario) && !empty($password)) {
        $usuarioModel = new UsuarioModel($conn);
        $usuarioValido = $usuarioModel->validarUsuario($usuario, $password);

        if ($usuarioValido) {
            if (isset($usuarioValido['idpersona']) && isset($usuarioValido['idusuario'])) {
                $personaModel = new PersonaModel($conn);
                $personaData = $personaModel->getDatosPersona($usuarioValido['idpersona']);

                if ($personaData) {
                    // Guardar datos en la sesión
                    $_SESSION['usuario'] = $personaData['nombres'] . " " . $personaData['Primer_Apellido'] . " " . $personaData['Segundo_Apellido'];
                    $_SESSION['rol'] = $personaData['rol'];
                    $_SESSION['idpersona'] = $personaData['idpersona'];

                    // Guardar idusuario también
                    $_SESSION['idusuario'] = $usuarioValido['idusuario'];

                    // Puedes eliminar este var_dump cuando funcione
                    // var_dump($_SESSION);

                    header("Location: app/views/dashboard/dashboard.php");
                    exit();
                } else {
                    $error = 'No se encontraron datos del usuario';
                }
            } else {
                $error = 'Datos de usuario incompletos (idpersona o idusuario faltantes)';
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        $error = 'Por favor ingrese usuario y contraseña';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Compuservic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }
        .login-container {
            display: flex;
            align-items: center;
            gap: 200px;
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 1s ease-out, transform 1s ease-out;
        }
        .splash-image {
            width: 500px;
            height: auto;
            transition: width 1s ease, height 1s ease, opacity 1s ease;
            opacity: 1;
        }
        .login-box {
            width: 300px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .hidden {
            display: none;
        }
        .alert {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- Splash Screen -->
<div id="splash-screen">
    <img id="splash-img" src="logo.png" class="splash-image" alt="Logo" />
</div>

<!-- Contenedor de Login -->
<div class="login-container hidden" id="login-container">
    <img id="mini-logo" src="logo.png" class="splash-image" style="width: 400px; height: 100px; opacity: 0;" alt="Logo" />
    
    <div class="login-box">
        <h2 class="text-center">Iniciar sesión</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario:</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required aria-required="true" aria-label="Usuario" autofocus />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" name="password" id="password" class="form-control" required aria-required="true" aria-label="Contraseña" />
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            const splashScreen = document.getElementById("splash-screen");
            const splashImg = document.getElementById("splash-img");
            const loginContainer = document.getElementById("login-container");
            const miniLogo = document.getElementById("mini-logo");

            // Animar el logo splash
            splashImg.style.width = "100px";
            splashImg.style.height = "100px";
            splashImg.style.opacity = "0";

            setTimeout(() => {
                splashScreen.style.display = "none";
                loginContainer.classList.remove("hidden");
                loginContainer.style.opacity = "1";
                loginContainer.style.transform = "translateY(0)";

                setTimeout(() => {
                    miniLogo.style.opacity = "1";
                }, 500);
            }, 1000);
        }, 2000);
    });
</script>

</body>
</html>
