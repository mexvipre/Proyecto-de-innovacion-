<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../../index.php');
    exit();
}

// Obtener datos de sesión
$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

// Definir la vista solicitada
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

// Variable de error
$error_message = '';

// Cargar modelos según la vista
try {
    switch ($view) {
        case 'orden_servicio':
            require_once './../../models/ordenes/OrdenServicioModel.php';
            $ordenModel = new OrdenServicioModel();
            $ordenes = $ordenModel->obtenerOrden();
            break;
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($usuario); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/styles.css">
</head>
<body>


<!--<?php echo htmlspecialchars($rol); ?></p>-->

<!-- Menú lateral -->
<nav class="sidebar" id="sidebar">
    <div>
         <?php if (strtolower($rol) === 'super administrador'): ?>
            <a href="dashboard.php?view=inicio">📚 Gestión De Orden</a>

            <!-- Gestión de Distritos -->
            <a href="javascript:void(0)" class="dropdown-btn">🌆 Gestión de Distritos</a>
            <div class="dropdown-container">
                <a href="dashboard.php?view=agregar_distrito">💒 Agregar Distrito</a>
                <a href="dashboard.php?view=mostrar_distrito">🙈 Ver Distrito</a>
            </div>

            <!-- Gestión de Usuarios -->
            <a href="javascript:void(0)" class="dropdown-btn">👥 Gestión de Usuarios</a>
            <div class="dropdown-container">
                <a href="dashboard.php?view=insertar_rol">⚔ Agregar un Rol</a>
                <a href="dashboard.php?view=mostrar_roles">🙈 Ver Roles</a>
                 <a href="dashboard.php?view=mostrar_contratos">🤝 Contratos</a>
            </div>

            <!-- Gestión de Clientes -->
            <a href="javascript:void(0)" class="dropdown-btn">🧑 Gestión de Clientes</a>
            <div class="dropdown-container">
                <a href="dashboard.php?view=insertar_personas">🙍‍♂️ Agregar Persona</a>
            
                <a href="dashboard.php?view=agregar_empresas">👨‍💼 Agregar Empresa</a>
                <a href="dashboard.php?view=mostrar_personas">👀 Ver Personas</a>
                <a href="dashboard.php?view=empresas">👀 Ver Empresas</a>
               
            </div>

            <a href="dashboard.php?view=reportes">📃 Reportes</a>
            <a href="dashboard.php?view=tracking">📍 Tracking</a>
        <?php endif; ?>

        <?php if (strtolower($rol) === 'técnico'): ?>
            <a href="dashboard.php?view=tareas">👷🏼‍♂️ Mis Tareas</a>
            <a href="dashboard.php?view=tareasfinalizadas">✅ Tareas Finalizadas</a>
            <a href="dashboard.php?view=CambiarContraseña">🔏 Cambiar Contraseña</a>
            
            
            
        <?php endif; ?>

             
    </div>






            <?php if (strtolower($rol) === 'admisión'): ?>
            <a href="dashboard.php?view=inicio">📚 Gestión De Orden</a>
            <a href="dashboard.php?view=insertar_personas">🙍‍♂️ Agregar Persona</a>
            <a href="dashboard.php?view=CambiarContraseña">🔏 Cambiar Contraseña</a>
            <?php endif; ?>



        <?php if (strtolower($rol) === 'administrador'): ?>
            <a href="dashboard.php?view=inicio">📚 Gestión De Orden</a>
            <a href="dashboard.php?view=ListarTareas">📊 Tareas </a>
            <a href="dashboard.php?view=equiposenespera">📃 Equipos en espera</a>
            <a href="dashboard.php?view=equiposenproceso">🧰 Equipos en Proceso</a>
            <a href="dashboard.php?view=equiposenfinalizado">✅ Equipos en Finalizados</a>



            <!-- Gestión de Distritos -->
            <a href="javascript:void(0)" class="dropdown-btn">🌆 Gestión de Distritos</a>
            <div class="dropdown-container">
                <a href="dashboard.php?view=agregar_distrito">💒 Agregar Distrito</a>
                <a href="dashboard.php?view=mostrar_distrito">🙈 Ver Distrito</a>
            </div>

            <!-- Gestión de Usuarios -->
            <a href="javascript:void(0)" class="dropdown-btn">👥 Gestión de Usuarios</a>
            <div class="dropdown-container">
                <a href="dashboard.php?view=mostrar_contratos">🤝 Contratos</a>
                <a href="dashboard.php?view=insertar_rol">⚔ Agregar un Rol</a>
                <a href="dashboard.php?view=mostrar_roles">🙈 Ver Roles</a>
                
            </div>

            <!-- Gestión de Clientes -->
            <a href="javascript:void(0)" class="dropdown-btn">🧑 Gestión de Clientes</a>
            <div class="dropdown-container">
                <a href="dashboard.php?view=insertar_personas">🙍‍♂️ Agregar Persona</a>
            
                <a href="dashboard.php?view=agregar_empresas">👨‍💼 Agregar Empresa</a>
                <a href="dashboard.php?view=mostrar_personas">👀 Ver Personas</a>
                <a href="dashboard.php?view=empresas">👀 Ver Empresas</a>
               
            </div>

          
            <a href="dashboard.php?view=Estadosdemiequipo">📍 Tracking</a>
            <a href="dashboard.php?view=CambiarContraseña">🔏 Cambiar Contraseña</a>
        <?php endif; ?>

    </div>

    <form method="POST" action="../../controllers/logout.php">
        <button type="submit" class="logout">🚪 Salir</button>
    </form>
</nav>

<!-- Contenido Principal -->
<main class="main-content">
    <header class="header">
        <span class="menu-toggle" onclick="toggleMenu()">☰</span>
        <img src="../dashboard/logo-removebg-preview.png" alt="Logo">
        <h1 style="font-size: 14px; font-weight: bold; color: #333;">
            👋 Bienvenido, <?php echo htmlspecialchars($usuario); ?>
            <span style="color: #FF5722;">(<?php echo htmlspecialchars($rol); ?>)</span>
        </h1>

    </header>
    

<div class="content">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

<?php if ($view === 'dashboard'): ?>
    <div style="text-align: center; margin-top: 40px;">
        
        <h2 style="font-size: 28px;">Bienvenido, <?php echo htmlspecialchars($usuario); ?>!</h2>
        <img src="../dashboard/logo-removebg-preview.png" alt="Logo" style="width: 500px; height: auto; margin-bottom: 20px;">
        <p style="text-align: justify; max-width: 600px; margin: 0 auto;">
            Si eres un usuario nuevo, te recomendamos cambiar tu contraseña para asegurar la privacidad de tu cuenta.
            Para hacerlo, dirígete a las opciones y busca el botón <strong>"Cambiar Contraseña"</strong>, donde podrás actualizar tu clave de forma sencilla y segura.
        </p>
    </div>
<?php else: ?>




 <?php
        // Cargar las vistas según el parámetro GET 'view'
        switch ($view) {
            case 'orden_servicio': include('../ordenes/ordenes.php'); break;
            case 'agregar_distrito': include('../distritos/agregar_distrito.php'); break;
            case 'mostrar_distrito': include('../distritos/mostrar_distrito.php'); break;
            case 'ordenTecnico': include('../ordenTecnico/ordenTecnico.php'); break;
            case 'insertar_rol': include('../roles/insertar_rol.php'); break;
            case 'mostrar_roles': include('../roles/mostrar_roles.php'); break;
            case 'insertar_personas': include('../personas/insertar_personas.php'); break;
            case 'mostrar_personas': include('../personas/mostrar_personas.php'); break;
            case 'agregar_empresas': include('../empresas/agregar_empresas.php'); break;
            case 'editar_empresas': include('../empresas/editar_empresas.php'); break;
            case 'empresas': include('../empresas/empresas.php'); break;
            case 'serviciotecnico': include('../serviciotecnico/formulario.php'); break;
            case 'marcasasoc': include('../serviciotecnico/marcasasoc.php'); break;
            case 'caracteristicas': include('../caracteristicas/caracteristicas.php'); break;
            case 'detequipos': include('../detequipos/detequipos.php'); break;
            case 'evidencias': include('../imagenes/index.html'); break;
            case 'tareas': include('../tareas/index.php'); break;
            case 'inicio': include('../general/Listar_OS.php'); break;
            case 'detallesequipos': include('../general/detallesequipos.php'); break;
     
            case 'tareasfinalizadas': include('../tareas/tareasfinalizadas.php'); break;
            
            case 'mostrar_contratos': include('../Contratos/index.php');break;
            case 'CambiarContraseña': include('../usuarios/index.php'); break;
            case 'equiposenespera': include('../reportes/muestraEespera.php'); break;
            case 'equiposenproceso': include('../reportes/muestraEproceso.php'); break;
            case 'equiposenfinalizado': include('../reportes/muestraEfinalizado.php'); break;
            case 'Estadosdemiequipo': include('../Tracking/Estadosdemiequipo.php'); break;
            case 'ListarTareas': include('../Tracking/graficos.php'); break;

            
            


    case 'registrar_revision': include('../tareas/registrar_revision.php'); break;
            default: break;
        }
        ?>
    <?php endif; ?>
</div>


    </div>
</main>

<script>

const dropdownBtns = document.querySelectorAll('.dropdown-btn');

dropdownBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const container = btn.nextElementSibling;
    const isOpen = container.classList.contains('show');

    // Cerrar todos los dropdowns abiertos antes de abrir otro
    document.querySelectorAll('.dropdown-container').forEach(cont => {
      cont.classList.remove('show');
      cont.previousElementSibling.classList.remove('active');
    });

    // Si no estaba abierto, abrir este
    if (!isOpen) {
      container.classList.add('show');
      btn.classList.add('active');
    }
  });
});


    
function toggleMenu() {
    document.getElementById("sidebar").classList.toggle("active");
}


</script>

</body>
</html>
