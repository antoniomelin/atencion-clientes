<?php
require_once '../includes/auth.php';
$config = require __DIR__ . '/api/config.php';

session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Conexión a la base de datos
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
    if ($mysqli->connect_error) {
        die('Error al conectar con la base de datos');
    }

    // Consulta del usuario
    $query = $mysqli->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
    $query->bind_param('s', $username);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        $query->bind_result($adminId, $passwordHash);
        $query->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['admin_id'] = $adminId;
            header('Location: index.php');
            exit;
        } else {
            $errorMessage = 'Contraseña incorrecta.';
        }
    } else {
        $errorMessage = 'Usuario no encontrado.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="login-container">
        <form id="login-form" action="login.php" method="POST">
            <h1>Iniciar Sesión</h1>
            <?php if ($errorMessage): ?>
                <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>
            <label for="username">Usuario</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
