<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventosparati";
$port = 3306;

try {
    $conexion = new mysqli($servername, $username, $password, $dbname, $port);
    $conexion->set_charset("utf8");
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "FALTAN_DATOS";
        exit;
    }

    // Buscar usuario
    $consulta = $conexion->prepare("SELECT nombre, password FROM usuarios WHERE email = ?");
    $consulta->bind_param("s", $email);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows === 0) {
        echo "NO_EXISTE";
        exit;
    }

    $usuario = $resultado->fetch_assoc();

    if (!password_verify($password, $usuario['password'])) {
        echo "INCORRECTO";
        exit;
    }
    
    // GUardar el nombre en sesión
    $_SESSION['username'] = $usuario['nombre'];

    // Respuesta exitosa
    echo "OK";

} catch (mysqli_sql_exception $e) {
    echo "ERROR_BD: " . $e->getMessage();
}