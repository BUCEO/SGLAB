CREATE DATABASE sglab;
USE sglab;

-- Tabla personas
CREATE TABLE personas (
    ci INT PRIMARY KEY,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    direccion VARCHAR(100),
    fechaNacimiento DATE
);

-- Tabla usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ci INT,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    tipo ENUM('Estudiante','Administrador','Asistente','Docente'),
    fechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fechaModificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ci) REFERENCES personas(ci)
);

-- Tabla laboratorios
CREATE TABLE laboratorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    comentario VARCHAR(255)
);

-- Tabla equipos
CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serialNumber VARCHAR(50),
    hostname VARCHAR(50),
    mac VARCHAR(17),
    cpu VARCHAR(50),
    ram INT,
    diskType VARCHAR(20),
    diskTotal INT,
    laboratorio_id INT,
    FOREIGN KEY (laboratorio_id) REFERENCES laboratorios(id)
);

-- Tabla registros
CREATE TABLE registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipo_id INT,
    usuario_id INT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado BOOLEAN,
    ip VARCHAR(15),
    descripcion TEXT,
    FOREIGN KEY (equipo_id) REFERENCES equipos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla perfiles
CREATE TABLE perfiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    rol ENUM('Estudiante','Administrador','Asistente','Docente'),
    permisos TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
