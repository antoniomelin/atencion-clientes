<?php

error_log("Datos recibidos en \$_POST: " . print_r($_POST, true));
error_log("Archivos recibidos en \$_FILES: " . print_r($_FILES, true));

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

// Verifica si se envió el formulario como multipart/form-data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES)) {
    http_response_code(400);
    echo json_encode(['message' => 'Solicitud no válida. Asegúrate de enviar los archivos.']);
    exit;
}

// Validación de campos
$requiredFields = ['nombre', 'apellidos', 'rut', 'email', 'telefono', 'boleta', 'lugar-compra'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['message' => "El campo '$field' es obligatorio."]);
        exit;
    }
}

// Validación de archivos
if (empty($_FILES['foto-boleta']['tmp_name']) || empty($_FILES['foto-producto']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Debes subir la foto de la boleta y la foto del producto.']);
    exit;
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 8 * 1024 * 1024; // 2 MB

function validateFile($file, $allowedMimeTypes, $maxFileSize, $fieldName) {
    if ($file['size'] > $maxFileSize) {
        throw new Exception("El archivo '$fieldName' supera el tamaño permitido de 2 MB.");
    }

    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedMimeTypes)) {
        throw new Exception("El archivo '$fieldName' tiene un formato no permitido. Se permiten imágenes JPEG, PNG y GIF.");
    }
}

// Función para renombrar archivos
function renameFile($file, $prefix) {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = substr(pathinfo($file['name'], PATHINFO_FILENAME), 0, 50); // Limita el nombre a 50 caracteres
    $uniqueName = uniqid($prefix . '_') . '_' . $safeName . '.' . $extension; // Genera un nombre único
    return $uniqueName;
}

try {
    validateFile($_FILES['foto-boleta'], $allowedMimeTypes, $maxFileSize, 'Foto Boleta');
    validateFile($_FILES['foto-producto'], $allowedMimeTypes, $maxFileSize, 'Foto Producto');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
    exit;
}

// Guardar archivos en el servidor
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fotoBoletaPath = $uploadDir . renameFile($_FILES['foto-boleta'], 'boleta');
$fotoProductoPath = $uploadDir . renameFile($_FILES['foto-producto'], 'producto');

if (!move_uploaded_file($_FILES['foto-boleta']['tmp_name'], $fotoBoletaPath) ||
    !move_uploaded_file($_FILES['foto-producto']['tmp_name'], $fotoProductoPath)) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al subir los archivos.']);
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
    $contactoQuery->bind_param('ss', $_POST['rut'], $_POST['email']);
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
            $_POST['nombre'],
            $_POST['apellidos'],
            $_POST['rut'],
            $_POST['empresa'],
            $_POST['email'],
            $_POST['telefono']
        );

        if (!$insertContactoQuery->execute()) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al registrar el contacto.', 'details' => $insertContactoQuery->error]);
            exit;
        }

        $contactoId = $insertContactoQuery->insert_id;
        $insertContactoQuery->close();
    }

    $contactoQuery->close();

    // Inserta la interacción (reclamo)
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
        $fotoBoletaPath,
        $fotoProductoPath
    );

    if (!$interaccionQuery->execute()) {
        throw new Exception('Error al registrar el reclamo: ' . $interaccionQuery->error);
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
        $asunto = "Gracias por tu Reclamo!";
        $contenido = "
            <h1>¡Gracias por tu reclamo!</h1>
            <p>Hemos recibido tu reclamo y será revisado por nuestro equipo.</p>
            <p>Tu código de seguimiento es: <strong>$codigoSeguimiento</strong></p>
        ";
        # enviarCorreo($_POST['email'], $asunto, $contenido);

        http_response_code(200);
        echo json_encode([
            'message' => 'Reclamo registrado con éxito.',
            'codigo_seguimiento' => $codigoSeguimiento
        ]);
    } catch (Exception $e) {
        error_log('Error al enviar el correo: ' . $e->getMessage());
        http_response_code(200);
        echo json_encode([
            'message' => 'Reclamo registrado, pero ocurrió un error al enviar el correo.',
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
