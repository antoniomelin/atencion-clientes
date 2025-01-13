<?php
require_once '../includes/auth.php';
requireAuth();

$config = require '../api/config.php';

// Conexión a la base de datos
$mysqli = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['port']
);

if ($mysqli->connect_error) {
    die('Error al conectar con la base de datos: ' . $mysqli->connect_error);
}

// Consulta de interacciones
$query = "
    SELECT 
        i.tipo,
        i.codigo_seguimiento,
        s.estado,
        i.mensaje
    FROM interacciones i
    LEFT JOIN seguimientos s ON i.id = s.interaccion_id
    GROUP BY i.id, s.estado
    ORDER BY i.fecha_creacion DESC
";
$result = $mysqli->query($query);

$interacciones = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $interacciones[] = $row;
    }
}
$mysqli->close();
?>

<?php
// Iconos para los estados
$estado_iconos = [
    'pendiente' => '❗', // Exclamación
    'en_proceso' => '⏳', // Reloj
    'resuelto' => '✔️', // Tick
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script defer src="../assets/js/admin.js"></script>
</head>
<body>
    <!-- Header con logo -->
    <header class="header">
        <a href="https://www.friosur.cl" target="_blank">
            <img class="logo" src="https://www.friosur.cl/wp-content/uploads/2023/11/image-6.png" alt="Friosur Logo">
        </a>
    </header>

    <!-- Contenedor principal -->
    <main class="admin-container">
        <h1>Panel de Administración</h1>
        <a href="logout.php" class="logout-link">Cerrar Sesión</a>

        <!-- Contenedor de tarjeta -->
        <div class="card">
            <ul class="interaction-list">
              <?php foreach ($interacciones as $interaccion): ?>
                <li class="interaction-item <?= $interaccion['tipo'] ?? 'default'; ?>">
                      <span class="interaction-icon">
                        <?= $estado_iconos[$interaccion['estado']] ?? '❓'; ?>
                      </span>
                      <span class="interaction-type"><?= htmlspecialchars($interaccion['tipo']); ?></span>
                      <span class="tracking-code"><?= htmlspecialchars($interaccion['codigo_seguimiento']); ?></span>
                      <span class="interaction-status"><?= htmlspecialchars($interaccion['estado']); ?></span>
                      <div class="details-content" style="display: none;">
                          <p><?= htmlspecialchars($interaccion['mensaje']); ?></p>
                      </div>
                  </li>
              <?php endforeach; ?>
            </ul>
        </div>
    </main>
</body>
</html>
