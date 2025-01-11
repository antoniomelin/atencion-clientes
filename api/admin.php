<?php
require_once '../includes/auth.php';
require_once '../config.php';

header('Content-Type: application/json');

// Verifica acción
$action = $_GET['action'] ?? null;

// Conexión a la base de datos
$mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al conectar con la base de datos.']);
    exit;
}

session_start();
if ($action === 'login') {
    // Manejo del inicio de sesión
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = $mysqli->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
    $query->bind_param('s', $username);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        $query->bind_result($adminId, $passwordHash);
        $query->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['admin_id'] = $adminId;
            echo json_encode(['message' => 'Inicio de sesión exitoso.']);
            exit;
        }
    }

    http_response_code(401);
    echo json_encode(['message' => 'Credenciales incorrectas.']);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'No autorizado.']);
    exit;
}

// Más acciones como listar interacciones, cambiar estados, etc.
?>
