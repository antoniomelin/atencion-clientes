<?php
$file = $_GET['file'] ?? null;

if ($file) {
    $filePath = realpath(__DIR__ . '/../uploads/' . $file);

    // Verifica que el archivo exista y esté dentro del directorio permitido
    if (file_exists($filePath) && strpos($filePath, realpath(__DIR__ . '/../uploads/')) === 0) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    }
}

http_response_code(404);
echo "Archivo no encontrado.";
