<?php
require_once __DIR__ . '/../includes/mailer.php';
$config = require __DIR__ . '/../config.php';

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
if (!isset($data['nombre'], $data['apellidos'], $data['rut'], $data['email'], $data['telefono'], $data['motivo'])) {
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
    // Inserta la sugerencia
    $query = $mysqli->prepare('
        INSERT INTO sugerencias (nombre, apellidos, rut, empresa, email, telefono, motivo, mensaje, codigo_seguimiento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $query->bind_param(
        'sssssssss',
        $data['nombre'],
        $data['apellidos'],
        $data['rut'],
        $data['empresa'],
        $data['email'],
        $data['telefono'],
        $data['motivo'],
        $data['mensaje'],
        $codigoSeguimiento
    );

    if (!$query->execute()) {
      http_response_code(500);
      echo json_encode(['message' => 'Error al registrar la sugerencia.', 'error' => $query->error]);
      exit;
    }

    $sugerenciaId = $query->insert_id;

    // Inserta el estado inicial en la tabla de seguimientos
    $seguimientoQuery = $mysqli->prepare('
        INSERT INTO seguimientos_sugerencias (sugerencia_id, estado)
        VALUES (?, ?)
    ');
    $estadoInicial = 'pendiente';
    $seguimientoQuery->bind_param('is', $sugerenciaId, $estadoInicial);

    if (!$seguimientoQuery->execute()) {
        throw new Exception('Error al registrar el seguimiento: ' . $seguimientoQuery->error);
    }

    $seguimientoQuery->close();

    // Envía correo de confirmación
    try {
        $asunto = "Gracias por tu Sugerencia!";
        $contenido = "
            <h1>¡Gracias por tu sugerencia!</h1>
            <p>Hemos recibido tu sugerencia y será analizada por nuestro equipo.</p>
            <p>Tu código de seguimiento es: <strong>$codigoSeguimiento</strong></p>
        ";
        # enviarCorreo($data['email'], $asunto, $contenido);

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
