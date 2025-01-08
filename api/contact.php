<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Carga de Composer
require_once __DIR__ . '/../includes/mailer.php'; // Carga la función enviarCorreo

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

// Verifica si existe una solicitud "Pendiente" para el mismo RUT
$checkQuery = $mysqli->prepare('
    SELECT 1 
    FROM contactos c
    JOIN seguimientos s ON c.id = s.contacto_id
    WHERE c.rut = ? AND s.estado = "Pendiente"
');
$checkQuery->bind_param('s', $data['rut']);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['message' => 'Ya existe una solicitud pendiente para este RUT.']);
    $checkQuery->close();
    exit;
}
$checkQuery->close();

// Genera codigo seguimiento 6 alfa
function generarCodigoSeguimiento($longitud = 6) {
    return strtoupper(substr(bin2hex(random_bytes($longitud)), 0, $longitud));
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

    if (!$seguimientoQuery) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta para insertar el seguimiento.', 'error' => $mysqli->error]);
        exit;
    }

    if ($seguimientoQuery->execute()) {
        $seguimientoQuery->close();

        // Contenido del correo
        $asunto = "Confirmacion de Contacto";
        $contenido = "
            <h1>¡Gracias por tu contacto!</h1>
            <p>Tu solicitud fue registrada exitosamente.</p>
            <p>Tu código de seguimiento es: <strong>$codigoSeguimiento</strong></p>
        ";

        // Enviar correo
        if (enviarCorreo($data['email'], $asunto, $contenido)) {
            http_response_code(200);
            echo json_encode(['message' => 'Contacto registrado con éxito. Se envió un correo de confirmación.', 'codigo_seguimiento' => $codigoSeguimiento]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Contacto registrado, pero ocurrió un error al enviar el correo.']);
        }
    } else {
        $seguimientoQuery->close();
        http_response_code(500);
        echo json_encode(['message' => 'Error al registrar el estado inicial del seguimiento.', 'error' => $seguimientoQuery->error]);
    }
} else {
    // Captura el error específico de la inserción
    http_response_code(500);
    echo json_encode(['message' => 'Error al registrar el contacto.', 'error' => $query->error]);
}

// Cierra las conexiones
$query->close();
$mysqli->close();
?>
