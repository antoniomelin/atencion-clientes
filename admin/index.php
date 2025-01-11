<?php
require_once '../includes/auth.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <header class="admin-header">
        <h1>Panel de Administración</h1>
        <a href="logout.php">Cerrar Sesión</a>
    </header>
    <main class="admin-container">
        <section class="dashboard-options">
            <h2>Opciones</h2>
            <ul>
                <li><a href="interactions.php">Gestión de Interacciones</a></li>
                <li><a href="stats.php">Estadísticas</a></li>
            </ul>
        </section>
    </main>
</body>
</html>
