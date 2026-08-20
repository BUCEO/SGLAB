-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sglab CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sglab;

-- Tabla perfiles
CREATE TABLE perfiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(50) NOT NULL,
    permisos TEXT NOT NULL
);

-- Tabla usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ci VARCHAR(15) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion VARCHAR(150),
    fechaNacimiento DATE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    perfil_id INT NOT NULL,
    FOREIGN KEY (perfil_id) REFERENCES perfiles(id) ON DELETE CASCADE
);

-- Tabla laboratorios
CREATE TABLE laboratorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    comentario TEXT
);

-- Tabla equipos
CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serialNumber VARCHAR(100) NOT NULL UNIQUE,
    hostname VARCHAR(100) NOT NULL,
    mac VARCHAR(50),
    cpu VARCHAR(100),
    ram VARCHAR(50),
    diskType VARCHAR(50),
    diskTotal VARCHAR(50),
    laboratorio VARCHAR(50) NOT NULL,
    FOREIGN KEY (laboratorio) REFERENCES laboratorios(nombre) ON DELETE CASCADE
);

-- Tabla registros
CREATE TABLE registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    estado VARCHAR(50),
    ip VARCHAR(50),
    descripcion TEXT,
    equipo_id INT NOT NULL,
    FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE
);

-- Insertar perfiles base
INSERT INTO perfiles (rol, permisos) VALUES
('Estudiante', 'Acceso básico al sistema'),
('Docente', 'Gestión de equipos y registros'),
('Administrador', 'Gestión completa del sistema');

-- Insertar un laboratorio de ejemplo
INSERT INTO laboratorios (nombre, comentario) VALUES
('LAB4', 'Laboratorio de informática 4');

-- Insertar un usuario administrador por defecto
INSERT INTO usuarios (ci, nombre, apellido, direccion, fechaNacimiento, email, password, perfil_id)
VALUES ('12345678', 'Admin', 'SGLAB', 'UTU Central', '1990-01-01', 'admin@sglab.com',
        '$2y$10$Kix8o5jDj8WGl/PlBPGvQO9o2GZ1aYFzP9yKMWXwKQK7jH8FNozF.', 3);
-- password: admin123
