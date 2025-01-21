<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$emailDestino = $input['email'] ?? null;
$mensaje = $input['message'] ?? null;
$id = $input['id'] ?? null;
$type = $input['type'] ?? 'generic'; // Tipo de correo (contacto, reclamo, sugerencia, etc.)

if (!$emailDestino || !$mensaje) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Configuración general
$config = require __DIR__ . '/../api/config.php';

// Personalizar asunto y contenido basado en el tipo
$asunto = "Notificación";
$contenido = "<p>$mensaje</p>";

switch ($type) {
    case 'contact':
        $asunto = "Nuevo Contacto";
        $contenido = "<h1>Nuevo mensaje de contacto</h1><p>$mensaje</p>";
        break;

    case 'reclamo':
        $asunto = "Reclamo recibido";
        $contenido = "<h1>Reclamo registrado</h1><p>$mensaje</p>";
        break;

    case 'sugerencia':
        $asunto = "Sugerencia enviada";
        $contenido = "<h1>Gracias por tu sugerencia</h1><p>$mensaje</p>";
        break;

    case 'generic':
    default:
        $asunto = "Notificación del sistema";
        $contenido = "<h1>Mensaje del sistema</h1><p>$mensaje</p>";
        break;
}

function enviarCorreo($emailDestino, $asunto, $contenido, $config = null) {
    if(!$config){
        $config = require __DIR__ . '/../api/config.php';
    }
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $config['MAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['MAIL_USERNAME'];
        $mail->Password = $config['MAIL_PASSWORD'];
        $mail->SMTPSecure = $config['MAIL_ENCRYPTION'];
        $mail->Port = $config['MAIL_PORT'];

        // Configuración del correo
        $mail->setFrom($config['MAIL_FROM_ADDRESS'], $config['MAIL_FROM_NAME']);
        $mail->addAddress($emailDestino);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $contenido;

        // Enviar el correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar el correo: " . $mail->ErrorInfo); // Log del error
        return false;
    }
}

// if (enviarCorreo($emailDestino, $asunto, $contenido, $config)) {
//     echo json_encode(['success' => true]);
// } else {
//     echo json_encode(['success' => false, 'error' => 'No se pudo enviar el correo']);
// }