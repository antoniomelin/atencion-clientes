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

// Verifica si ya existe el contacto
$checkContactoQuery = $mysqli->prepare('SELECT id FROM contactos WHERE rut = ? AND email = ?');
$checkContactoQuery->bind_param('ss', $data['rut'], $data['email']);
$checkContactoQuery->execute();
$checkContactoQuery->store_result();

if ($checkContactoQuery->num_rows > 0) {
    $checkContactoQuery->bind_result($contactoId);
    $checkContactoQuery->fetch();
    $checkContactoQuery->close();
} else {
    // Inserta el contacto si no existe
    $insertContactoQuery = $mysqli->prepare('INSERT INTO contactos (nombre, apellidos, rut, empresa, email, telefono) VALUES (?, ?, ?, ?, ?, ?)');
    $insertContactoQuery->bind_param(
        'ssssss',
        $data['nombre'],
        $data['apellidos'],
        $data['rut'],
        $data['empresa'],
        $data['email'],
        $data['telefono']
    );

    if ($insertContactoQuery->execute()) {
        $contactoId = $insertContactoQuery->insert_id;
        $insertContactoQuery->close();
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al registrar el contacto.', 'error' => $mysqli->error]);
        exit;
    }
}

// Verifica si ya existe una solicitud pendiente para este RUT
$checkPendienteQuery = $mysqli->prepare('
    SELECT 1 
    FROM interacciones i
    JOIN seguimientos s ON i.id = s.interaccion_id
    WHERE i.contacto_id = ? AND s.estado = "pendiente"
');
$checkPendienteQuery->bind_param('i', $contactoId);
$checkPendienteQuery->execute();
$checkPendienteQuery->store_result();

if ($checkPendienteQuery->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['message' => 'Ya existe una solicitud pendiente para este RUT.']);
    $checkPendienteQuery->close();
    exit;
}
$checkPendienteQuery->close();

// Genera código de seguimiento
function generarCodigoSeguimiento($longitud = 6) {
    return strtoupper(substr(bin2hex(random_bytes($longitud)), 0, $longitud));
}

$codigoSeguimiento = generarCodigoSeguimiento();

// Inserta la interacción en la base de datos
$insertInteraccionQuery = $mysqli->prepare('
    INSERT INTO interacciones (contacto_id, tipo, canal, codigo_seguimiento)
    VALUES (?, ?, ?, ?)
');
$tipo = 'contacto'; // Tipo de interacción
$insertInteraccionQuery->bind_param('isss', $contactoId, $tipo, $data['canal'], $codigoSeguimiento);

if ($insertInteraccionQuery->execute()) {
    $interaccionId = $insertInteraccionQuery->insert_id;

    // Inserta el estado inicial en la tabla de seguimientos
    $insertSeguimientoQuery = $mysqli->prepare('INSERT INTO seguimientos (interaccion_id, estado) VALUES (?, ?)');
    $estadoInicial = 'pendiente';
    $insertSeguimientoQuery->bind_param('is', $interaccionId, $estadoInicial);

    if ($insertSeguimientoQuery->execute()) {
        $insertSeguimientoQuery->close();

        // Enviar correo de confirmación
        try {
            $asunto = "Contacto desde Friosur";
            $contenido = "
                <h1>¡Gracias por tu contacto!</h1>
                <p>Tu solicitud fue registrada exitosamente.</p>
                <p>Tu código de seguimiento es: <strong>$codigoSeguimiento</strong></p>
            ";
            #enviarCorreo($data['email'], $asunto, $contenido);
        } catch (Exception $e) {
            error_log('Error al enviar el correo: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'message' => 'Contacto registrado, pero ocurrió un error al enviar el correo.',
                'codigo_seguimiento' => $codigoSeguimiento
            ]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['message' => 'Contacto registrado con éxito.', 'codigo_seguimiento' => $codigoSeguimiento]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al registrar el seguimiento.', 'error' => $insertSeguimientoQuery->error]);
    }
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error al registrar la interacción.', 'error' => $insertInteraccionQuery->error]);
}

// Cierra las conexiones
$insertInteraccionQuery->close();
$mysqli->close();
?>
