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
            <h2>Interacciones</h2>
            <ul class="interaction-list">
                <!-- Fila de ejemplo -->
                <li class="interaction-item pending">
                    <span class="interaction-icon">📞</span>
                    <span class="tracking-code">A98F46</span>
                    <span class="interaction-type">Contacto</span>
                    <span class="interaction-status">Pendiente</span>
                    <button class="toggle-details">Detalles</button>
                </li>
                <li class="interaction-item in-progress">
                    <span class="interaction-icon">💡</span>
                    <span class="tracking-code">783903</span>
                    <span class="interaction-type">Sugerencia</span>
                    <span class="interaction-status">En Proceso</span>
                    <button class="toggle-details">Detalles</button>
                </li>
                <li class="interaction-item resolved">
                    <span class="interaction-icon">⚠️</span>
                    <span class="tracking-code">546A6F</span>
                    <span class="interaction-type">Reclamo</span>
                    <span class="interaction-status">Resuelto</span>
                    <button class="toggle-details">Detalles</button>
                </li>
            </ul>
        </div>
    </main>
</body>
</html>
