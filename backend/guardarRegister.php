<?php
require_once __DIR__ . '/conexion.php';

try {
    

    //if ($conexion->connect_error) {
    //   die("Error de conexión: " . $conexion->connect_error);}

    // Recibir datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';

    // Validación mínima
    if (empty($nombre) || empty($genero) || empty($email) || empty($password)) {
        echo "FALTAN_DATOS";
        exit;
    }

    // 1. Verificar si el correo ya existe
    $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $consulta->bind_param("s", $email);
    $consulta->execute();
    $consulta->store_result();

    if ($consulta->num_rows > 0) {
        echo "EXISTE";    
        exit;
    }

    // Inserción segura dentro de try/catch
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, genero, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $genero, $email, $password);
    $stmt->execute();

    echo "OK";

} catch (mysqli_sql_exception $e) {
    // Manejo de errores
    echo "ERROR_BD: " . $e->getMessage();
} finally {
    // Cerrar conexiones
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($consulta)) {
        $consulta->close();
    }
    if (isset($conexion)) {
        $conexion->close();
    }
}

