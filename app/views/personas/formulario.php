<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta DNI</title>
</head>
<body>

    <h1>Consulta de DNI</h1>
    
    <form id="consultaForm">
        <label for="dni">DNI:</label>
        <input type="text" name="dni" id="dni" maxlength="8" required>
        <button type="submit">Consultar</button>
    </form>

    <script>
        document.getElementById('consultaForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const dni = document.getElementById('dni').value;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'consulta_dni.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                console.log('Respuesta cruda:', xhr.responseText);

                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Respuesta parseada:', response);

                    if (response.success) {
                        alert('DNI encontrado: ' + response.data.nombre);
                    } else {
                        alert('Error: ' + response.message);
                    }
                } catch (error) {
                    console.error('Error al analizar JSON:', error);
                }
            };

            xhr.send('dni=' + encodeURIComponent(dni));
        });
    </script>

</body>
</html>
