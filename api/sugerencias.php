<?php
require_once __DIR__ . '/../includes/mailer.php';
$config = require __DIR__ . '/../config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Genera un código único de seguimiento
function generarCodigoSeguimiento($longitud = 6) {
    return strtoupper(substr(bin2hex(random_bytes($longitud)), 0, $longitud));
}

$codigoSeguimiento = generarCodigoSeguimiento();

// Inserta la sugerencia en la base de datos
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

if ($query->execute()) {
    $sugerenciaId = $query->insert_id;

    // Inserta el estado inicial en la tabla de seguimientos
    $seguimientoQuery = $mysqli->prepare('
        INSERT INTO seguimientos_sugerencias (sugerencia_id, estado)
        VALUES (?, ?)
    ');
    $estadoInicial = 'pendiente';
    $seguimientoQuery->bind_param('is', $sugerenciaId, $estadoInicial);

    if ($seguimientoQuery->execute()) {
        $seguimientoQuery->close();

        // Envía correo de confirmación
        try {
            $asunto = "Gracias por tu Sugerencia!";
            $contenido = "
                <h1>¡Gracias por tu sugerencia!</h1>
                <p>Hemos recibido tu sugerencia y será analizada por nuestro equipo.</p>
                <p>Tu código de seguimiento es: <strong>$codigoSeguimiento</strong></p>
            ";
            #enviarCorreo($data['email'], $asunto, $contenido);
            http_response_code(200);
            echo json_encode(['message' => 'Sugerencia registrada con éxito.', 'codigo_seguimiento' => $codigoSeguimiento]);
        } catch (Exception $e) {
            error_log('Error al enviar el correo: ' . $e->getMessage());
            http_response_code(200);
            echo json_encode([
                'message' => 'Sugerencia registrada, pero ocurrió un error al enviar el correo.',
                'codigo_seguimiento' => $codigoSeguimiento
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al registrar el seguimiento.', 'error' => $seguimientoQuery->error]);
    }
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error al registrar la sugerencia.', 'error' => $query->error]);
}
?>
