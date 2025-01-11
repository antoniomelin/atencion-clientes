<?php
require_once '../includes/auth.php';
requireAuth(); // Verifica si el usuario está autenticado
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="../assets/js/admin.js" defer></script>
</head>
<body>
    <header class="admin-header">
        <h1>Panel de Administración</h1>
        <a href="logout.php">Cerrar Sesión</a>
    </header>
    <main class="admin-container">
        <section class="stats">
            <h2>Estadísticas Generales</h2>
            <div id="stats-container"></div>
        </section>
        <section class="interactions">
            <h2>Interacciones</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="interactions-list">
                    <!-- Contenido dinámico -->
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
