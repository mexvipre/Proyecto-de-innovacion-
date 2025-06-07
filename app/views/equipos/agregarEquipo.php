<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "compuservic";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomEquipo = $_POST['NomEquipo'];
    $idTipoEquipo = $_POST['idTipoEquipo'];
    $idMarca = isset($_POST['idMarca']) ? $_POST['idMarca'] : null;
    $unidadAlmacenamiento = isset($_POST['unidad_almacenamiento']) ? $_POST['unidad_almacenamiento'] : null;
    $ram = isset($_POST['ram']) ? $_POST['ram'] : null;
    $idUsuario = 1; // ID del usuario (ajústalo según tu lógica)

    // Insertar especificaciones solo si es Laptop o Computadora
    $idComputer = 'NULL';

    if ($idTipoEquipo == 1 || $idTipoEquipo == 2) {
        $queryComputer = "INSERT INTO computer (unidad_de_almacenamiento, Ram, idusuario, dateCreated) 
                          VALUES ('$unidadAlmacenamiento', '$ram', '$idUsuario', NOW())";

        if (mysqli_query($conn, $queryComputer)) {
            $idComputer = mysqli_insert_id($conn); // Obtenemos el id generado
        } else {
            echo "Error al insertar en 'computer': " . mysqli_error($conn);
            exit();
        }
    }

    // Insertar en equipos
    $queryInsert = "INSERT INTO equipos (NomEquipo, idTipo_Equipos, idComputer, idMarca, idCategoria) 
                    VALUES ('$nomEquipo', '$idTipoEquipo', $idComputer, " . ($idMarca ? "'$idMarca'" : "NULL") . ", 1)";

    if (mysqli_query($conn, $queryInsert)) {
        echo "<script>alert('Equipo agregado exitosamente'); window.location.href = 'agregar_equipo.php';</script>";
    } else {
        echo "Error al insertar en 'equipos': " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Nuevo Equipo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        #campoMarca,
        #campoEspecificaciones {
            display: none;
        }
    </style>
</head>
<body>

<h1>Agregar Nuevo Equipo</h1>

<form action="" method="POST">
    <!-- Seleccionar el Nombre del Equipo -->
    <label for="NomEquipo">Nombre del Equipo:</label>
    <select id="NomEquipo" name="NomEquipo" required>
        <option value="" disabled selected>Seleccionar nombre de equipo</option>
        <?php
        $queryEquipos = "SELECT idEquipo, NomEquipo FROM equipos";
        $resultEquipos = mysqli_query($conn, $queryEquipos);

        while ($row = mysqli_fetch_assoc($resultEquipos)) {
            echo '<option value="' . $row['idEquipo'] . '">' . $row['NomEquipo'] . '</option>';
        }
        ?>
    </select>

    <!-- Seleccionar el Tipo de Equipo -->
    <label for="idTipoEquipo">Tipo de Equipo:</label>
    <select id="idTipoEquipo" name="idTipoEquipo" required>
        <option value="" disabled selected>Seleccionar tipo de equipo</option>
        <?php
        $queryTipoEquipos = "SELECT idTipo_Equipos, NomTipoEquipo FROM tipo_equipos";
        $resultTipoEquipos = mysqli_query($conn, $queryTipoEquipos);

        while ($row = mysqli_fetch_assoc($resultTipoEquipos)) {
            echo '<option value="' . $row['idTipo_Equipos'] . '">' . $row['NomTipoEquipo'] . '</option>';
        }
        ?>
    </select>

    <!-- Seleccionar la Marca -->
    <div id="campoMarca">
        <label for="idMarca">Marca:</label>
        <select id="idMarca" name="idMarca">
            <option value="" disabled selected>Seleccionar marca</option>
            <?php
            $queryMarcas = "SELECT idMarca, NomMarca FROM marcas";
            $resultMarcas = mysqli_query($conn, $queryMarcas);

            while ($row = mysqli_fetch_assoc($resultMarcas)) {
                echo '<option value="' . $row['idMarca'] . '">' . $row['NomMarca'] . '</option>';
            }
            ?>
        </select>
    </div>

    <!-- Especificaciones (solo si es Computadora o Laptop) -->
    <div id="campoEspecificaciones">
        <label for="unidad_almacenamiento">Unidad de Almacenamiento:</label>
        <input type="text" id="unidad_almacenamiento" name="unidad_almacenamiento">

        <label for="ram">RAM:</label>
        <input type="text" id="ram" name="ram">
    </div>

    <input type="submit" value="Agregar Equipo">
</form>

<script>
    document.getElementById('idTipoEquipo').addEventListener('change', function() {
        var tipoEquipo = this.value;
        var campoMarca = document.getElementById('campoMarca');
        var campoEspecificaciones = document.getElementById('campoEspecificaciones');

        // Mostrar solo si el tipo es Computadora (1) o Laptop (2)
        if (tipoEquipo === '1' || tipoEquipo === '2') {
            campoMarca.style.display = 'block';
            campoEspecificaciones.style.display = 'block';
        } else {
            campoMarca.style.display = 'none';
            campoEspecificaciones.style.display = 'none';
        }
    });
</script>

</body>
</html>

<?php
mysqli_close($conn);
?>
