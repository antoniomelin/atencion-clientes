<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $codigoSeguimiento = $input['id'] ?? null;

    if (!$codigoSeguimiento) {
        echo json_encode(['success' => false, 'error' => 'Código de seguimiento no proporcionado']);
        exit;
    }

    require_once '../includes/auth.php';
    requireAuth();

    $config = require '../api/config.php';
    $mysqli = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port']
    );

    if ($mysqli->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Error al conectar con la base de datos: ' . $mysqli->connect_error]);
        exit;
    }

    // Verificar si el código de seguimiento existe
    $result = $mysqli->query("SELECT id FROM interacciones WHERE codigo_seguimiento = '$codigoSeguimiento'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'El código de seguimiento no existe']);
        exit;
    }

    $interaccion = $result->fetch_assoc();
    $interaccionId = $interaccion['id'];

    // Actualizar el estado en la tabla seguimientos
    $stmt = $mysqli->prepare("UPDATE seguimientos SET estado = 'en_proceso' WHERE interaccion_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Error en la consulta SQL: ' . $mysqli->error]);
        exit;
    }

    $stmt->bind_param('i', $interaccionId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el estado']);
    }

    $stmt->close();
    $mysqli->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
