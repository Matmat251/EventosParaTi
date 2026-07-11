<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo "NO_LOGUEADO";
    exit;
}
require_once __DIR__ . '/conexion.php';

/** @var mysqli $conn */

// Recibir datos del formulario
$nombre = $_POST['ticket-form-name'];
$email = $_POST['ticket-form-email'];
$telefono = $_POST['ticket-form-phone'];
$tipo = $_POST['ticket-form-type'];
$mensaje = $_POST['ticket-form-message'];
$card_number = $_POST['card-number'];
$card_expiry = $_POST['card-expiry'];
$card_cvv = $_POST['card-cvv'];

// Insertar en la base de datos
$sql = "INSERT INTO tickets (nombre, email, telefono, tipo, mensaje, card_number, card_expiry, card_cvv)
VALUES ('$nombre', '$email', '$telefono', '$tipo', '$mensaje', '$card_number', '$card_expiry', '$card_cvv')";

if ($conn->query($sql) === TRUE) {
    echo <<<HTML
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Exitoso</title>
    <!-- Fuente Roboto desde Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        /* Estilos generales del cuerpo */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Estilos de la tarjeta */
        .card {
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }

        .card h2 {
            color: #28a745;
            margin-bottom: 15px;
        }

        .card p {
            color: #333;
        }

        /* Estilos del botón */
        .btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        .btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>¡Registro Exitoso!</h2>
        <p>Gracias por registrarte. Tus datos han sido guardados correctamente.</p>
        <a class="btn" href="index.html">Volver al inicio</a>
    </div>
</body>
</html>
HTML;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}


$conn->close();
?>
