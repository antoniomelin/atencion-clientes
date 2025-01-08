<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Composer Autoload

/**
 * Envía un correo utilizando PHPMailer.
 *
 * @param string $emailDestino Dirección de correo del destinatario.
 * @param string $asunto Título del correo.
 * @param string $contenido Contenido HTML del correo.
 * @param array $config Configuración del correo SMTP (opcional, por defecto se cargan desde el proyecto).
 * @return bool True si el correo fue enviado con éxito, False si ocurrió un error.
 */
function enviarCorreo($emailDestino, $asunto, $contenido, $config = null) {
    if (!$config) {
        // Si no se pasa configuración, carga la configuración predeterminada desde el archivo config.php
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
        $mail->addAddress($emailDestino); // Correo del destinatario
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
