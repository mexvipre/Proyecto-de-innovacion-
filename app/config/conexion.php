<?php
date_default_timezone_set('America/Lima'); // O tu zona horaria local

class Conexion {
    private static $host = "localhost";
    private static $dbname = "compuservic";
    private static $username = "root";
    private static $password = "";
    private static $conn;

    public static function conectar() {
        try {
            // Establecer la conexión con el charset utf8 para evitar problemas con caracteres especiales
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8";
            self::$conn = new PDO($dsn, self::$username, self::$password);
            
            // Establecer el modo de errores de PDO a excepción
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return self::$conn; // Retorna la conexión
        } catch (PDOException $e) {
            // Si ocurre un error, lo mostramos y lo registramos en un archivo log
            error_log("Error de conexión: " . $e->getMessage(), 3, "errors.log");
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>
