<?php
header('Content-Type: application/json');

// Verifica que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['message' => 'Método no permitido.']);
    exit;
}

// Obtiene los datos enviados en formato JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verifica que los campos obligatorios estén presentes
if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['email']) || empty($data['telefono']) || empty($data['rut']) || empty($data['canal'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['message' => 'Todos los campos son obligatorios.']);
    exit;
}

// Aquí puedes guardar los datos en una base de datos o enviarlos por correo
// Ejemplo: conexión a una base de datos
/*
$mysqli = new mysqli('localhost', 'root', '', 'nombre_base_de_datos');
if ($mysqli->connect_error) {
    http_response_code(500); // Error interno del servidor
    echo json_encode(['message' => 'Error al conectar con la base de datos.']);
    exit;
}

$query = $mysqli->prepare('INSERT INTO contactos (nombre, apellidos, email, telefono, rut, canal) VALUES (?, ?, ?, ?, ?, ?)');
$query->bind_param('ssssss', $data['nombre'], $data['apellidos'], $data['email'], $data['telefono'], $data['rut'], $data['canal']);
$query->execute();
$query->close();
$mysqli->close();
*/

http_response_code(200); // Todo salió bien
echo json_encode(['message' => 'Formulario procesado con éxito.']);
exit;
?>
