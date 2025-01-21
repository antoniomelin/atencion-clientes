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
    echo json_encode(['success' => false, 'message' => 'Error al conectar a la base de datos.', 'error' => $mysqli->connect_error]);
    exit;
}

// Verifica si se envió el formulario como multipart/form-data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida. Asegúrate de enviar los archivos.']);
    exit;
}

// Validación de campos obligatorios
$requiredFields = ['nombre', 'apellidos', 'rut', 'email', 'telefono', 'boleta', 'lugar-compra', 'mensaje'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "El campo '$field' es obligatorio."]);
        exit;
    }
}

// Validación de archivos
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 8 * 1024 * 1024; // 8 MB

function validateFile($file, $allowedMimeTypes, $maxFileSize, $fieldName) {
    if ($file['size'] > $maxFileSize) {
        throw new Exception("El archivo '$fieldName' supera el tamaño permitido de 8 MB.");
    }

    // Verificar el tipo MIME
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        throw new Exception("El archivo '$fieldName' tiene un formato no permitido ($mimeType). Solo se permiten imágenes JPEG, PNG y GIF.");
    }
}

// Función para renombrar archivos de forma segura
function renameFile($file, $prefix) {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)); // Sanitiza el nombre
    return uniqid($prefix . '_') . '_' . $safeName . '.' . $extension;
}

try {
    validateFile($_FILES['foto-boleta'], $allowedMimeTypes, $maxFileSize, 'Foto Boleta');
    validateFile($_FILES['foto-producto'], $allowedMimeTypes, $maxFileSize, 'Foto Producto');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

// Guardar archivos en el servidor
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fotoBoletaName = renameFile($_FILES['foto-boleta'], 'boleta');
$fotoProductoName = renameFile($_FILES['foto-producto'], 'producto');

$fotoBoletaPath = $uploadDir . $fotoBoletaName;
$fotoProductoPath = $uploadDir . $fotoProductoName;

if (!move_uploaded_file($_FILES['foto-boleta']['tmp_name'], $fotoBoletaPath) ||
    !move_uploaded_file($_FILES['foto-producto']['tmp_name'], $fotoProductoPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al subir los archivos.']);
    exit;
}

// Generar código único de seguimiento
function generarCodigoSeguimiento($longitud = 6) {
    return strtoupper(substr(bin2hex(random_bytes($longitud)), 0, $longitud));
}

$codigoSeguimiento = generarCodigoSeguimiento();

try {
    // Verifica si el contacto ya existe
    $contactoQuery = $mysqli->prepare('SELECT id FROM contactos WHERE rut = ? AND email = ?');
    $contactoQuery->bind_param('ss', $_POST['rut'], $_POST['email']);
    $contactoQuery->execute();
    $contactoQuery->store_result();

    if ($contactoQuery->num_rows > 0) {
        $contactoQuery->bind_result($contactoId);
        $contactoQuery->fetch();
    } else {
        // Insertar nuevo contacto
        $insertContactoQuery = $mysqli->prepare('INSERT INTO contactos (nombre, apellidos, rut, empresa, email, telefono) VALUES (?, ?, ?, ?, ?, ?)');
        $insertContactoQuery->bind_param(
            'ssssss',
            $_POST['nombre'],
            $_POST['apellidos'],
            $_POST['rut'],
            $_POST['empresa'],
            $_POST['email'],
            $_POST['telefono']
        );

        if (!$insertContactoQuery->execute()) {
            throw new Exception('Error al registrar el contacto: ' . $insertContactoQuery->error);
        }
        $contactoId = $insertContactoQuery->insert_id;
        $insertContactoQuery->close();
    }

    $contactoQuery->close();

    // Insertar el reclamo
    $interaccionQuery = $mysqli->prepare('
        INSERT INTO interacciones (contacto_id, tipo, mensaje, codigo_seguimiento, lugar_compra, boleta, foto_boleta, foto_producto)
        VALUES (?, "reclamo", ?, ?, ?, ?, ?, ?)
    ');
    $interaccionQuery->bind_param(
        'issssss',
        $contactoId,
        $_POST['mensaje'],
        $codigoSeguimiento,
        $_POST['lugar-compra'],
        $_POST['boleta'],
        $fotoBoletaName,
        $fotoProductoName
    );

    if (!$interaccionQuery->execute()) {
        throw new Exception('Error al registrar el reclamo: ' . $interaccionQuery->error);
    }

    $interaccionId = $interaccionQuery->insert_id;
    $interaccionQuery->close();

    // Insertar el estado inicial en la tabla de seguimientos
    $seguimientoQuery = $mysqli->prepare('INSERT INTO seguimientos (interaccion_id, estado) VALUES (?, "pendiente")');
    $seguimientoQuery->bind_param('i', $interaccionId);
    $seguimientoQuery->execute();
    $seguimientoQuery->close();

    // Enviar correo de confirmación
    $asunto = "Gracias por tu Reclamo!";
    $contenido = "
        <h1>¡Gracias por tu reclamo!</h1>
        <p>Hemos recibido tu reclamo y será revisado por nuestro equipo.</p>
        <p>Tu código de seguimiento es: <strong>$codigoSeguimiento</strong></p>
    ";
    enviarCorreo($_POST['email'], $asunto, $contenido);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Reclamo registrado con éxito.',
        'codigo_seguimiento' => $codigoSeguimiento
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ocurrió un error.', 'error' => $e->getMessage()]);
}

$mysqli->close();
