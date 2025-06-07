<?php
require_once 'app/models/UsuarioModel.php';

class UsuarioController {
    private $usuarioModel;

    public function __construct($conexion) {
        $this->usuarioModel = new UsuarioModel($conexion);
    }

    public function login($usuario, $password) {
        $resultado = $this->usuarioModel->validarUsuario($usuario, $password);

        if ($resultado) {
            echo "Login exitoso. ¡Bienvenido, " . $resultado['namuser'] . "!";
            // Aquí puedes redirigir o iniciar sesión
        } else {
            echo "Usuario o contraseña incorrectos";
        }
    }
}
