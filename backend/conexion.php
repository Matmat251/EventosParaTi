<?php
// Activar excepciones para errores de mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Leer configuracion de base de datos desde el entorno (para Docker) o usar valores por defecto (para local)
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$dbname = getenv('DB_NAME') ?: "eventosparati";
$port = getenv('DB_PORT') ?: 3306;

try {
    // Conexión a MySQL
    $conexion = new mysqli($servername, $username, $password, $dbname, $port);
    $conexion->set_charset("utf8");
    
    // Compatibilidad para scripts que usan la variable $conn en lugar de $conexion
    $conn = $conexion;
} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
