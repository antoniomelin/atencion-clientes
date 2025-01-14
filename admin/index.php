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
        i.mensaje,
        c.nombre,
        c.apellidos,
        c.rut,
        c.empresa,
        c.email,
        c.telefono,
        i.motivo,
        i.lugar_compra,
        i.boleta,
        i.foto_boleta,
        i.foto_producto
    FROM interacciones i
    LEFT JOIN contactos c ON i.contacto_id = c.id
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
    'pendiente' => '', // Exclamación
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
        <div class="menu-container">
            <div class="filters">
                <button class="filter-btn" data-filter="pendiente">Pendientes</button>
                <button class="filter-btn" data-filter="en_proceso">En Proceso</button>
                <button class="filter-btn" data-filter="resuelto">Resueltos</button>
            </div>
            <div class="date-picker">
                <label for="start-date">Desde:</label>
                <input type="date" id="start-date">
                <label for="end-date">Hasta:</label>
                <input type="date" id="end-date">
            </div>
            <a href="logout.php" class="logout-link">Cerrar Sesión</a>
        </div>

        <!-- Contenedor de tarjeta -->
        <div class="card">
            <ul class="interaction-list">
              <?php foreach ($interacciones as $interaccion): ?>
                <li class="interaction-item <?= $interaccion['tipo'] ?? 'default'; ?>">
                      <span class="interaction-icon">
                        <?= $estado_iconos[$interaccion['estado']] ?? '❓'; ?>
                      </span>
                      <span class="interaction-status"><?= htmlspecialchars($interaccion['estado']); ?></span>
                      <span class="tracking-code"><?= htmlspecialchars($interaccion['codigo_seguimiento']); ?></span>
                      <span class="interaction-type"><?= htmlspecialchars($interaccion['tipo']); ?></span>
                      <div class="details-content" style="display: none;">
                        <?php if ($interaccion['tipo'] === 'contacto'): ?>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($interaccion['nombre']); ?></p>
                            <p><strong>Apellidos:</strong> <?= htmlspecialchars($interaccion['apellidos']); ?></p>
                            <p><strong>RUT:</strong> <?= htmlspecialchars($interaccion['rut']); ?></p>
                            <p><strong>Empresa:</strong> <?= htmlspecialchars($interaccion['empresa'] ?? 'N/A'); ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($interaccion['email']); ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($interaccion['telefono']); ?></p>
                        <?php elseif ($interaccion['tipo'] === 'sugerencia'): ?>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($interaccion['nombre']); ?></p>
                            <p><strong>Apellidos:</strong> <?= htmlspecialchars($interaccion['apellidos']); ?></p>
                            <p><strong>RUT:</strong> <?= htmlspecialchars($interaccion['rut']); ?></p>
                            <p><strong>Empresa:</strong> <?= htmlspecialchars($interaccion['empresa'] ?? 'N/A'); ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($interaccion['email']); ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($interaccion['telefono']); ?></p>
                            <p><strong>Motivo:</strong> <?= htmlspecialchars($interaccion['motivo']); ?></p>
                            <p><strong>Mensaje:</strong> <?= htmlspecialchars($interaccion['mensaje']); ?></p>
                        <?php elseif ($interaccion['tipo'] === 'reclamo'): ?>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($interaccion['nombre']); ?></p>
                            <p><strong>Apellidos:</strong> <?= htmlspecialchars($interaccion['apellidos']); ?></p>
                            <p><strong>RUT:</strong> <?= htmlspecialchars($interaccion['rut']); ?></p>
                            <p><strong>Empresa:</strong> <?= htmlspecialchars($interaccion['empresa'] ?? 'N/A'); ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($interaccion['email']); ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($interaccion['telefono']); ?></p>
                            <p><strong>Lugar de compra:</strong> <?= htmlspecialchars($interaccion['lugar_compra']); ?></p>
                            <p><strong>Número de boleta:</strong> <?= htmlspecialchars($interaccion['boleta']); ?></p>
                            <p><strong>Mensaje:</strong> <?= htmlspecialchars($interaccion['mensaje']); ?></p>
                            <?php if (!empty($interaccion['foto_boleta'])): ?>
                                <p><strong>Foto Boleta:</strong> <a href="<?= htmlspecialchars($interaccion['foto_boleta']); ?>" target="_blank">Ver</a></p>
                            <?php endif; ?>
                            <?php if (!empty($interaccion['foto_producto'])): ?>
                                <p><strong>Foto Producto:</strong> <a href="<?= htmlspecialchars($interaccion['foto_producto']); ?>" target="_blank">Ver</a></p>
                            <?php endif; ?>
                        <?php endif; ?>
                      </div>
                  </li>
              <?php endforeach; ?>
            </ul>
        </div>
    </main>
</body>
</html>
