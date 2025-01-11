<?php
$config = require __DIR__ . '/config.php';

header('Content-Type: application/json');

// Configuración de límite de consultas
define('MAX_REQUESTS', 10); // Máximo de consultas permitidas
define('WINDOW_TIME', 60); // Tiempo en segundos para reiniciar el conteo

// Inicia o actualiza el contador de consultas por IP
$ip = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['rate_limit'])) {
    $_SESSION['rate_limit'] = [];
}
if (!isset($_SESSION['rate_limit'][$ip])) {
    $_SESSION['rate_limit'][$ip] = ['count' => 0, 'start_time' => time()];
}

$timeElapsed = time() - $_SESSION['rate_limit'][$ip]['start_time'];
if ($timeElapsed > WINDOW_TIME) {
    $_SESSION['rate_limit'][$ip] = ['count' => 0, 'start_time' => time()];
}

if ($_SESSION['rate_limit'][$ip]['count'] >= MAX_REQUESTS) {
    http_response_code(429); // Too Many Requests
    echo json_encode(['message' => 'Demasiadas consultas desde esta IP. Inténtalo más tarde.']);
    exit;
}

// Incrementa el contador
$_SESSION['rate_limit'][$ip]['count']++;

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

// Obtiene el código de seguimiento de la URL
$trackingCode = $_GET['code'] ?? null;

if (!$trackingCode || strlen($trackingCode) !== 6 || !preg_match('/^[A-Za-z0-9]{6}$/', $trackingCode)) {
    http_response_code(400);
    echo json_encode(['message' => 'Código de seguimiento no válido.']);
    exit;
}

try {
    // Consulta para obtener el estado y la fecha del seguimiento
    $query = $mysqli->prepare('
        SELECT s.estado, s.fecha
        FROM interacciones i
        JOIN seguimientos s ON i.id = s.interaccion_id
        WHERE i.codigo_seguimiento = ?
        ORDER BY s.fecha DESC
        LIMIT 1
    ');
    $query->bind_param('s', $trackingCode);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        http_response_code(200);
        echo json_encode([
            'estado' => $row['estado'],
            'fecha' => $row['fecha'],
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'No se encontró ningún seguimiento para este código.']);
    }

    $query->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al buscar el seguimiento.', 'error' => $e->getMessage()]);
}

// Cierra la conexión
$mysqli->close();
?>
