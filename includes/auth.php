<?php
// Función para verificar si el usuario está autenticado
function requireAuth() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Función para cerrar sesión
function logout() {
    session_start();
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}
?>
