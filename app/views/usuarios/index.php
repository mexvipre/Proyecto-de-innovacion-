<h2>Cambiar Contraseña</h2>

<div id="form-container" style="max-width: 700px; padding: 20px; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); background:#f9f9f9;">
    <form id="formCambiarContrasena" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 1 1 200px; min-width: 180px;">
            <label for="actual_contrasena" style="display: block; font-weight: bold; margin-bottom: 4px;">Contraseña Actual:</label>
            <input type="password" name="actual_contrasena" id="actual_contrasena" required style="width: 100%; padding: 8px;">
        </div>

        <div style="flex: 1 1 200px; min-width: 180px;">
            <label for="nueva_contrasena" style="display: block; font-weight: bold; margin-bottom: 4px;">Nueva Contraseña:</label>
            <input type="password" name="nueva_contrasena" id="nueva_contrasena" required style="width: 100%; padding: 8px;">
        </div>

        <div style="flex: 0 0 auto;">
        <button type="submit" style="padding: 10px 20px; background-color: rgb(35, 149, 226); color: white; border: none; border-radius: 4px; cursor: pointer; height: 42px; margin-top: 24px;">
            Actualizar
        </button>

        </div>
    </form>
</div>

<div id="contador-regresivo" style="display:none; font-weight:bold; margin-top:15px; font-size: 1.1rem; color:#cc0000;"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>



const form = document.getElementById('formCambiarContrasena');
const formContainer = document.getElementById('form-container');
const contadorDiv = document.getElementById('contador-regresivo');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    const actual_contrasena = document.getElementById('actual_contrasena').value.trim();
    const nueva_contrasena = document.getElementById('nueva_contrasena').value.trim();

    fetch('http://localhost/andream/app/views/usuarios/procesar_cambio_contrasena.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ actual_contrasena, nueva_contrasena })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
            });
            form.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
            });

            if (data.message.includes('excedido los intentos permitidos')) {
                formContainer.style.display = 'none';
                iniciarCuentaRegresiva(30); // bloqueo 30 segundos
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Ocurrió un error al actualizar.', 'error');
    });
});

// Verifica bloqueo al cargar la página
async function verificarBloqueo() {
    try {
        const response = await fetch('http://localhost/andream/app/views/usuarios/procesar_cambio_contrasena.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ check_block: true })
        });
        const data = await response.json();

        if (data.bloqueado) {
            formContainer.style.display = 'none';
            iniciarCuentaRegresiva(data.tiempo_restante);
        } else {
            formContainer.style.display = 'block';
            contadorDiv.style.display = 'none';
        }
    } catch (error) {
        console.error('Error al verificar bloqueo:', error);
    }
}

function iniciarCuentaRegresiva(segundos) {
    contadorDiv.style.display = 'block';
    let tiempoRestante = segundos;
    actualizarContador(tiempoRestante);

    const intervalo = setInterval(() => {
        tiempoRestante--;
        if (tiempoRestante <= 0) {
            clearInterval(intervalo);
            contadorDiv.style.display = 'none';
            formContainer.style.display = 'block';
        } else {
            actualizarContador(tiempoRestante);
        }
    }, 1000);
}

function actualizarContador(segundos) {
    const minutos = Math.floor(segundos / 60);
    const segs = segundos % 60;
    contadorDiv.textContent = `Intenta de nuevo en ${minutos} min ${segs} seg`;
}

// Al cargar la página, verificar bloqueo
window.addEventListener('load', verificarBloqueo);
</script>
