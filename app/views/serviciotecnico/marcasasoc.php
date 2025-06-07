<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asociar Marca y Subcategoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-verde {
            background-color: #00BF63;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-verde:hover {
            background-color: #008C4A;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="header-container mb-3">
        <h2>Describe al Equipo</h2>
        <div class="button-container">
            <button type="button" class="btn btn-primary" id="agregar">Guardar</button>
            <!-- El botón de "Continuar" ya no está visible directamente -->
        </div>
    </div>

    <form id="form_asociacion" method="post">
        <div class="mb-3">
            <label for="categoria" class="form-label">Categoría:</label>
            <select id="categoria" name="categoria" class="form-select" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php
                $conexion = new mysqli('localhost', 'root', '', 'compuservic');
                $query = "SELECT id_categoria, NombreCategoria FROM categorias";
                $resultado = $conexion->query($query);
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<option value='" . $fila['id_categoria'] . "'>" . $fila['NombreCategoria'] . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="subcategoria" class="form-label">Subcategoría:</label>
            <select id="subcategoria" name="subcategoria" class="form-select" required>
                <option value="">-- Selecciona una subcategoría --</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="marca" class="form-label">Marca:</label>
            <select id="marca" name="marca" class="form-select" required>
                <option value="">-- Selecciona una marca --</option>
                <?php
                $queryMarcas = "SELECT id_marca, Nombre_Marca FROM marcas";
                $resultadoMarcas = $conexion->query($queryMarcas);
                while ($fila = $resultadoMarcas->fetch_assoc()) {
                    echo "<option value='" . $fila['id_marca'] . "'>" . $fila['Nombre_Marca'] . "</option>";
                }
                $conexion->close();
                ?>
            </select>
        </div>
    </form>
</div>

<script>
$(document).ready(function(){
    $('#categoria').change(function(){
        let categoriaID = $(this).val();

        // Obtener subcategorías
        $.ajax({
            url: "../serviciotecnico/obtener_subcategorias.php",
            method: "POST",
            data: { categoria_id: categoriaID },
            success: function(data){
                $('#subcategoria').html(data);
            }
        });

        // Obtener marcas filtradas
        $.ajax({
            url: "../serviciotecnico/obtener_marcas.php",
            method: "POST",
            data: { categoria_id: categoriaID },
            success: function(data){
                $('#marca').html(data);
            }
        });
    });

    $('#agregar').click(function() {
        let categoria = $('#categoria').val();
        let subcategoria = $('#subcategoria').val();
        let marca = $('#marca').val();

        if (categoria && subcategoria && marca) {
            // Enviar datos al servidor
            $.ajax({
                url: "../serviciotecnico/guardar_asociacion.php",
                method: "POST",
                data: {
                    categoria: categoria,
                    subcategoria: subcategoria,
                    marca: marca
                },
                success: function(response) {
                    // Alerta con botón de continuar
                    Swal.fire({
                    title: '¡Guardado exitosamente!',
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#00BF63'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../dashboard/dashboard.php?view=caracteristicas';
                    }
                    });


                    // Limpiar los campos
                    $('#categoria').val('');
                    $('#subcategoria').html('<option value="">-- Selecciona una subcategoría --</option>');
                    $('#marca').html('<option value="">-- Selecciona una marca --</option>');
                },
                error: function(xhr, status, error) {
                    console.error("Error AJAX:", error);
                    Swal.fire('Error', 'Ocurrió un error al guardar la asociación.', 'error');
                }
            });
        } else {
            Swal.fire('Campos incompletos', 'Por favor, selecciona una categoría, subcategoría y marca.', 'warning');
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
