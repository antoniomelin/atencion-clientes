<?php
header('Content-Type: application/json');

// Verifica que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido.']);
    exit;
}

// Cargar la configuración de la base de datos
$config = require 'config.php';

// Conexión a la base de datos
$mysqli = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['port']
);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al conectar con la base de datos.']);
    exit;
}

// Obtiene los datos enviados en formato JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validación básica
if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['rut']) || empty($data['email']) || empty($data['telefono']) || empty($data['canal'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Todos los campos obligatorios deben ser completados.']);
    exit;
}

// Genera codigo seguimiento 6 alfa
function generarCodigoSeguimiento($longitud = 6) {
    return strtoupper(substr(bin2hex(random_bytes($longitud)), 0, $longitud));
}

// Conexión a la base de datos
$mysqli = new mysqli('localhost', 'root', '', 'atencion_clientes');
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al conectar con la base de datos.']);
    exit;
}

$codigoSeguimiento = generarCodigoSeguimiento(6);

// Inserta los datos del contacto en la base de datos
$query = $mysqli->prepare('INSERT INTO contactos (nombre, apellidos, rut, empresa, email, telefono, canal, codigo_seguimiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$query->bind_param(
    'ssssssss',
    $data['nombre'],
    $data['apellidos'],
    $data['rut'],
    $data['empresa'],
    $data['email'],
    $data['telefono'],
    $data['canal'],
    $codigoSeguimiento
);

if ($query->execute()) {
    // Obtiene el ID del contacto insertado
    $contactoId = $query->insert_id;

    // Inserta el estado inicial en la tabla de seguimientos
    $estadoInicial = "Pendiente";
    $seguimientoQuery = $mysqli->prepare('INSERT INTO seguimientos (contacto_id, estado) VALUES (?, ?)');
    $seguimientoQuery->bind_param('is', $contactoId, $estadoInicial);
    $seguimientoQuery->execute();
    $seguimientoQuery->close();

    // Respuesta exitosa
    http_response_code(200);
    echo json_encode(['message' => 'Contacto registrado con éxito.', 'codigo_seguimiento' => $codigoSeguimiento]);
} else {
    // Respuesta de error
    http_response_code(500);
    echo json_encode(['message' => 'Error al registrar el contacto.']);
}

// Cierra las conexiones
$query->close();
$mysqli->close();
?>
