<?php
session_start();
require_once __DIR__ . '/conexion.php';

/** @var mysqli $conexion */

try {
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