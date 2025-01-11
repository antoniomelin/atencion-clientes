<?php
require_once '../includes/auth.php';
require_once '../config.php';
requireAuth();

// Conexión a la base de datos
$mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
if ($mysqli->connect_error) {
    die('Error al conectar con la base de datos');
}

// Manejo de filtros
$tipo = $_GET['tipo'] ?? ''; // contacto, sugerencia, reclamo
$search = $_GET['search'] ?? '';

// Consulta base
$query = "SELECT interacciones.id, interacciones.tipo, interacciones.codigo_seguimiento, interacciones.fecha_creacion, contactos.nombre, contactos.apellidos, contactos.email
          FROM interacciones
          JOIN contactos ON interacciones.contacto_id = contactos.id
          WHERE 1=1";

// Agregar filtros a la consulta
if ($tipo) {
    $query .= " AND interacciones.tipo = ?";
}
if ($search) {
    $query .= " AND (contactos.nombre LIKE ? OR contactos.apellidos LIKE ? OR interacciones.codigo_seguimiento LIKE ?)";
}

$query .= " ORDER BY interacciones.fecha_creacion DESC";

$stmt = $mysqli->prepare($query);

if ($tipo && $search) {
    $likeSearch = "%$search%";
    $stmt->bind_param('ssss', $tipo, $likeSearch, $likeSearch, $likeSearch);
} elseif ($tipo) {
    $stmt->bind_param('s', $tipo);
} elseif ($search) {
    $likeSearch = "%$search%";
    $stmt->bind_param('sss', $likeSearch, $likeSearch, $likeSearch);
}

$stmt->execute();
$result = $stmt->get_result();

$interacciones = [];
while ($row = $result->fetch_assoc()) {
    $interacciones[] = $row;
}
$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Interacciones</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<header class="admin-header">
    <h1>Gestión de Interacciones</h1>
    <a href="logout.php">Cerrar Sesión</a>
</header>
<main class="admin-container">
    <section class="filter-section">
        <form method="GET" action="interactions.php">
            <label for="tipo">Filtrar por tipo:</label>
            <select id="tipo" name="tipo">
                <option value="">Todos</option>
                <option value="contacto" <?= $tipo === 'contacto' ? 'selected' : '' ?>>Contacto</option>
                <option value="sugerencia" <?= $tipo === 'sugerencia' ? 'selected' : '' ?>>Sugerencia</option>
                <option value="reclamo" <?= $tipo === 'reclamo' ? 'selected' : '' ?>>Reclamo</option>
            </select>
            <label for="search">Buscar:</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nombre, apellido, código...">
            <button type="submit">Aplicar</button>
        </form>
    </section>

    <section class="interactions-list">
        <h2>Lista de Interacciones</h2>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($interacciones) > 0): ?>
                    <?php foreach ($interacciones as $interaccion): ?>
                        <tr>
                            <td><?= htmlspecialchars($interaccion['codigo_seguimiento']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($interaccion['tipo'])) ?></td>
                            <td><?= htmlspecialchars($interaccion['nombre'] . ' ' . $interaccion['apellidos']) ?></td>
                            <td><?= htmlspecialchars($interaccion['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($interaccion['fecha_creacion'])) ?></td>
                            <td>
                                <a href="view_interaction.php?id=<?= $interaccion['id'] ?>">Ver</a>
                                <a href="delete_interaction.php?id=<?= $interaccion['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta interacción?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No se encontraron resultados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
