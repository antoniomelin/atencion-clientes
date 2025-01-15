<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if ($id) {
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
            echo json_encode(['success' => false, 'error' => 'Error al conectar con la base de datos']);
            exit;
        }

        $stmt = $mysqli->prepare("UPDATE interacciones SET estado = 'en_procesado' WHERE codigo_seguimiento = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se encontró la interacción']);
        }

        $stmt->close();
        $mysqli->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
