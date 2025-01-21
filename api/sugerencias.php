<?php
require_once __DIR__ . '/../includes/mailer.php';
$config = require __DIR__ . '/config.php';

header('Content-Type: application/json'); // Asegura que todas las respuestas sean JSON

// Conexión a la base de datos
$mysqli = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['port']
);

// Verifica conexión
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al conectar a la base de datos.', 'error' => $mysqli->connect_error]);
    exit;
}

// Obtiene datos enviados por el cliente
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validación básica
if (!isset($data['nombre'], $data['apellidos'], $data['rut'], $data['email'], $data['telefono'], $data['motivo'], $data['mensaje'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Faltan campos obligatorios.']);
    exit;
}

// Genera un código único de seguimiento
function generarCodigoSeguimiento($longitud = 6) {
    return strtoupper(substr(bin2hex(random_bytes($longitud)), 0, $longitud));
}

$codigoSeguimiento = generarCodigoSeguimiento();

try {
    // Verifica si el contacto ya existe
    $contactoQuery = $mysqli->prepare('SELECT id FROM contactos WHERE rut = ? AND email = ?');
    $contactoQuery->bind_param('ss', $data['rut'], $data['email']);
    $contactoQuery->execute();
    $contactoQuery->store_result();

    if ($contactoQuery->num_rows > 0) {
        // Contacto ya existe, obtenemos su ID
        $contactoQuery->bind_result($contactoId);
        $contactoQuery->fetch();
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

        if (!$insertContactoQuery->execute()) {
            throw new Exception('Error al registrar el contacto: ' . $insertContactoQuery->error);
        }

        $contactoId = $insertContactoQuery->insert_id;
        $insertContactoQuery->close();
    }

    $contactoQuery->close();

    // Inserta la interacción
    $interaccionQuery = $mysqli->prepare('
        INSERT INTO interacciones (contacto_id, tipo, motivo, mensaje, codigo_seguimiento)
        VALUES (?, "sugerencia", ?, ?, ?)
    ');
    $interaccionQuery->bind_param(
        'isss',
        $contactoId,
        $data['motivo'],
        $data['mensaje'],
        $codigoSeguimiento
    );

    if (!$interaccionQuery->execute()) {
        throw new Exception('Error al registrar la interacción: ' . $interaccionQuery->error);
    }

    $interaccionId = $interaccionQuery->insert_id;
    $interaccionQuery->close();

    // Inserta el estado inicial en la tabla de seguimientos
    $seguimientoQuery = $mysqli->prepare('
        INSERT INTO seguimientos (interaccion_id, estado)
        VALUES (?, "pendiente")
    ');
    $seguimientoQuery->bind_param('i', $interaccionId);

    if (!$seguimientoQuery->execute()) {
        throw new Exception('Error al registrar el seguimiento: ' . $seguimientoQuery->error);
    }

    $seguimientoQuery->close();

    // Envía correo de confirmación
    try {
        $asunto = "Nueva Sugerencia!";
        $contenido = generarPlantillaCorreo($codigoSeguimiento, $asunto);

        enviarCorreo($data['email'], $asunto, $contenido);

        http_response_code(200);
        echo json_encode([
            'message' => 'Sugerencia registrada con éxito.',
            'codigo_seguimiento' => $codigoSeguimiento
        ]);
    } catch (Exception $e) {
        error_log('Error al enviar el correo: ' . $e->getMessage());
        http_response_code(200);
        echo json_encode([
            'message' => 'Sugerencia registrada, pero ocurrió un error al enviar el correo.',
            'codigo_seguimiento' => $codigoSeguimiento
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Ocurrió un error.', 'error' => $e->getMessage()]);
}

// Cierra la conexión
$mysqli->close();
?>
