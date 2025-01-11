CREATE DATABASE friosur_atencion;

USE friosur_atencion;

-- Tabla de contactos
CREATE TABLE contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    rut VARCHAR(12) NOT NULL,
    empresa VARCHAR(100),
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    UNIQUE (rut, email) -- Evita duplicados de contactos
);

-- Tabla de interacciones
CREATE TABLE interacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contacto_id INT NOT NULL,
    tipo ENUM('contacto', 'sugerencia', 'reclamo') NOT NULL,
    canal VARCHAR(50) DEFAULT NULL, -- Solo para interacciones tipo contacto
    motivo ENUM('felicitaciones', 'sugerencia', 'otro') DEFAULT NULL, -- Solo para sugerencias
    mensaje TEXT, -- Mensaje de la interacción
    codigo_seguimiento VARCHAR(10) NOT NULL UNIQUE, -- Código único para seguimiento
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lugar_compra VARCHAR(255) DEFAULT NULL, -- Lugar de compra para reclamos
    boleta VARCHAR(50) DEFAULT NULL, -- Número de boleta o factura para reclamos
    foto_boleta VARCHAR(255) DEFAULT NULL, -- Ruta de la foto de la boleta
    foto_producto VARCHAR(255) DEFAULT NULL, -- Ruta de la foto del producto
    FOREIGN KEY (contacto_id) REFERENCES contactos(id) ON DELETE CASCADE
);

-- Tabla de seguimientos
CREATE TABLE seguimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    interaccion_id INT NOT NULL,
    estado ENUM('pendiente', 'en_proceso', 'resuelto') DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (interaccion_id) REFERENCES interacciones(id) ON DELETE CASCADE
);