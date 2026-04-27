<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$data = json_decode(file_get_contents('php://input'), true);

$respuesta = [
    "mensaje" => "Procesado correctamente",
    "recibido" => $data
];

echo json_encode(["status" => "OK"]);

?>