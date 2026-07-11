<?php
require_once __DIR__ . '/conexion.php';

/** @var mysqli $conn */

// Recibir datos del formulario
$nombre = $_POST['contact-name'];
$telefono = $_POST['contact-phone'];
$direccion = $_POST['contact-address'];
$postal = $_POST['contact-postal'];
$ciudad = $_POST['contact-city'];
$departamento = $_POST['contact-department'];
$email = $_POST['contact-email'];
$evento = $_POST['contact-event'];
$zona = $_POST['contact-zone'];

// Insertar en la base de datos
$sql = "INSERT INTO contactos (nombre, telefono, direccion, postal, ciudad, departamento, email, evento, zona)
        VALUES ('$nombre', '$telefono', '$direccion', '$postal', '$ciudad', '$departamento', '$email', '$evento', '$zona')";

if ($conn->query($sql) === TRUE) {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Mensaje Enviado</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: "Roboto", sans-serif;
                background-color: #fff4e6;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .card {
                background-color: #fff;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
                max-width: 400px;
            }
            .card h2 {
                color: #ff7f00;
                margin-bottom: 15px;
            }
            .card p {
                color: #333;
            }
            .btn {
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #ff7f00;
                color: white;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                font-weight: bold;
            }
            .btn:hover {
                background-color: #e36d00;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>¡Registro enviado correctamente se le informara por su correo!</h2>
            <p>Gracias por registrarte, ' . htmlspecialchars($nombre) . '.</p>
            <a class="btn" href="index.html">Volver al inicio</a>
        </div>
    </body>
    </html>';
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>