<?php
function generarPlantillaCorreo($codigoSeguimiento, $asunto = "Notificación de Friosur", $mensaje = "Tu solicitud fue registrada exitosamente") {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>$asunto</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: 600px;
                background-color: #ffffff;
                margin: 20px auto;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                text-align: center;
                padding: 20px;
                background-color: #0c3273;
                border-radius: 10% 10% 0 0;
            }
            .header img {
                width: 150px;
                background-color: #0c3273;
                border-radius: 10%;
                padding: 10px;
            }
            .content {
                padding: 20px;
                color: #333;
            }
            .content h1 {
                font-size: 22px;
                color: #0056b3;
                text-align: center;
            }
            .content p {
                font-size: 16px;
                line-height: 1.5;
                text-align: center;
            }
            .code {
                font-size: 18px;
                font-weight: bold;
                color: #0056b3;
                text-align: center;
            }
            .footer {
                text-align: center;
                padding: 10px;
                font-size: 14px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='https://www.friosur.cl/wp-content/uploads/2023/11/image-6.png' alt='Friosur Logo'>
            </div>
            <div class='content'>
                <h1>¡Gracias por tu $asunto!</h1>
                <p>$mensaje</p>
                <p>Tu código de seguimiento es:</p>
                <p class='code'>$codigoSeguimiento</p>
            </div>
            <div class='footer'>
                <p>Friosur - Atención al Cliente</p>
                <p>&copy; " . date('Y') . " Friosur. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>";
}
?>
