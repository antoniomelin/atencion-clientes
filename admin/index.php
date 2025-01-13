<?php
require_once '../includes/auth.php';
requireAuth();
require_once '../api/config.php'; // Archivo de configuración para la base de datos

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
$query = "SELECT tipo, codigo_seguimiento, estado, mensaje FROM interacciones";
$result = $mysqli->query($query);

$interacciones = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $interacciones[] = $row;
    }
}
$mysqli->close();
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
                  <li class="interaction-item <?= strtolower($interaccion['estado']); ?>">
                      <span class="interaction-icon">
                          <?php
                          // Define un icono para cada tipo
                          switch ($interaccion['tipo']) {
                              case 'Contacto':
                                  echo '📞';
                                  break;
                              case 'Sugerencia':
                                  echo '💡';
                                  break;
                              case 'Reclamo':
                                  echo '⚠️';
                                  break;
                              default:
                                  echo '❓';
                                  break;
                          }
                          ?>
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
