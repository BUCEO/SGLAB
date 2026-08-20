-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS bddsglab6;
USE bddsglab6;

-- Tabla Usuarios 
CREATE TABLE Usuarios (
    id INT(5) PRIMARY KEY AUTO_INCREMENT,
    ci INT(8) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255),
    idTipoUsuario INT(1) NOT NULL,
    secret_2fa VARCHAR(255),
    github_id VARCHAR(100),
    github_username VARCHAR(100),
    github_email VARCHAR(150),
    debe_cambiar_password BOOLEAN DEFAULT FALSE,
    ultima_modificacion_password DATETIME,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME,
    activo BOOLEAN DEFAULT TRUE,
    fechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fechaModificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de contraseñas
CREATE TABLE historial_passwords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones
CREATE TABLE sesiones (
    id VARCHAR(64) PRIMARY KEY,
    usuario_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    datos_sesion TEXT,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de logs de autenticación
CREATE TABLE logs_autenticacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    username VARCHAR(100),
    tipo_evento ENUM(
        'login_exitoso', 
        'login_fallido', 
        '2fa_exitoso', 
        '2fa_fallido', 
        'oauth_exitoso',
        'oauth_fallido',
        'logout', 
        'password_cambiado',
        'cuenta_bloqueada',
        'cuenta_desbloqueada'
    ) NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    detalles TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla para laboratorios
CREATE TABLE Laboratorios (
    idLaboratorio INT(2) PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    comentario VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla Equipos
CREATE TABLE Equipos (
    idEquipo INT(5) PRIMARY KEY AUTO_INCREMENT,
    serialNumber INT(8) UNIQUE,
    hostname VARCHAR(100) NOT NULL UNIQUE,
    CPU VARCHAR(100) NOT NULL,
    RAM INT(4) NOT NULL,
    diskType VARCHAR(50) NOT NULL,
    diskTotal INT(4) NOT NULL,
    idLaboratorio INT(2),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idLaboratorio) REFERENCES Laboratorios(idLaboratorio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla EquiposPerifericos
CREATE TABLE EquiposPerifericos (
    idEquipoPeriferico INT(5) PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('Monitor', 'Teclado', 'Mouse', 'TV', 'FlashUSB') NOT NULL,
    descripcion VARCHAR(255),
    conectado BOOLEAN DEFAULT TRUE,
    idEquipo INT(5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idEquipo) REFERENCES Equipos(idEquipo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla Registro (uso de equipos)
CREATE TABLE Registro (
    idRegistro INT(8) PRIMARY KEY AUTO_INCREMENT,
    idEquipo INT(5) NOT NULL,
    idUsuario INT(5) NOT NULL,
    fecha INT(20) NOT NULL,
    estado INT(1) NOT NULL,
    IP VARCHAR(45),
    diskFree INT(4),
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idEquipo) REFERENCES Equipos(idEquipo),
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Inserts para Laboratorios
INSERT INTO Laboratorios (nombre, comentario) VALUES
('Laboratorio 1', 'Laboratorio de informática básica'),
('Laboratorio 2', 'Laboratorio de programación'),
('Laboratorio 3', 'Laboratorio de redes'),
('Laboratorio 4', 'Laboratorio de investigación'),
('Laboratorio 5', 'Laboratorio multimedia');



INSERT INTO Usuarios (ci, nombre, username, email, password, idTipoUsuario, activo, debe_cambiar_password) VALUES
-- ADMINISTRADORES (idTipoUsuario = 1) - Requieren 2FA
(11111111, 'Admin Sistema', 'sysadmin', 'sysadmin@lab.edu.uy', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, TRUE, FALSE),

(22222222, 'Carlos Administrador', 'admin.user', 'admin@lab.edu.uy', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, TRUE, TRUE),

-- DOCENTES (idTipoUsuario = 2) - Login usuario/contraseña
(34567890, 'Carlos López', 'docente1', 'carlos.lopez@lab.edu.uy', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, TRUE, FALSE),

(45678901, 'Ana Rodríguez', 'docente2', 'ana.rodriguez@lab.edu.uy', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, TRUE, FALSE),

(23456789, 'María García', 'docente3', 'maria.garcia@lab.edu.uy', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, TRUE, TRUE),

-- ESTUDIANTES (idTipoUsuario = 3) - Login con GitHub (password NULL)
(12345678, 'Juan Pérez', 'juan.perez', 'juan.perez@estudiante.edu.uy', 
 NULL, 3, TRUE, FALSE),

(56789012, 'Pedro Martínez', 'pedro.martinez', 'pedro.martinez@estudiante.edu.uy', 
 NULL, 3, TRUE, FALSE);

-- Inserts para Equipos
INSERT INTO Equipos (serialNumber, hostname, CPU, RAM, diskType, diskTotal, idLaboratorio) VALUES
(10000001, 'PC-LAB-A-01', 'Intel Core i5-10400', 8, 'SSD', 256, 1),
(10000002, 'PC-LAB-B-01', 'AMD Ryzen 5 3600', 16, 'SSD', 512, 2),
(10000003, 'PC-LAB-C-01', 'Intel Core i7-10700', 32, 'NVMe', 1000, 3),
(10000004, 'PC-LAB-D-01', 'AMD Ryzen 7 3700X', 16, 'HDD', 2000, 4),
(10000005, 'PC-LAB-E-01', 'Intel Core i9-10900', 64, 'NVMe', 2000, 5);

-- Inserts para EquiposPerifericos
INSERT INTO EquiposPerifericos (tipo, descripcion, conectado, idEquipo) VALUES
('Teclado', 'Teclado mecánico RGB', TRUE, 1),
('Mouse', 'Mouse óptico inalámbrico', TRUE, 1),
('Monitor', 'Monitor LED 24"', TRUE, 1),
('Teclado', 'Teclado multimedia', TRUE, 2),
('Mouse', 'Mouse gaming', TRUE, 2),
('Monitor', 'Monitor 27" 4K', TRUE, 3);

-- Inserts para Registro
INSERT INTO Registro (idEquipo, idUsuario, fecha, estado, IP, diskFree, descripcion) VALUES
(1, 7, 1737369000, 1, '192.168.1.101', 120, 'Equipo encendido correctamente'),
(2, 3, 1737371700, 1, '192.168.1.102', 380, 'Inicio de sesión exitoso'),
(3, 5, 1737374400, 0, '192.168.1.103', 750, 'Equipo apagado por mantenimiento'),
(4, 6, 1737379200, 1, '192.168.1.104', 1500, 'Nuevo software instalado'),
(5, 1, 1737389100, 1, '192.168.1.105', 1800, 'Equipo en uso normal');


-- Eliminar usuarios si existen
DROP USER IF EXISTS 'sgapp_login'@'localhost';
DROP USER IF EXISTS 'sgapp_estudiante'@'localhost';
DROP USER IF EXISTS 'sgapp_docente'@'localhost';
DROP USER IF EXISTS 'sgapp_admin'@'localhost';
DROP USER IF EXISTS 'sgapp_backup'@'localhost';

-- 1. Usuario para login y autenticación
CREATE USER 'sgapp_login'@'localhost' IDENTIFIED BY 'LoginUsuario123!';
GRANT SELECT ON bddsglab6.Usuarios TO 'sgapp_login'@'localhost';
GRANT SELECT, INSERT ON bddsglab6.logs_autenticacion TO 'sgapp_login'@'localhost';
GRANT SELECT, INSERT, UPDATE ON bddsglab6.sesiones TO 'sgapp_login'@'localhost';
GRANT SELECT, INSERT ON bddsglab6.historial_passwords TO 'sgapp_login'@'localhost';
GRANT UPDATE (secret_2fa, fechaModificacion, intentos_fallidos, bloqueado_hasta) 
ON bddsglab6.Usuarios TO 'sgapp_login'@'localhost';
GRANT UPDATE ON bddsglab6.Usuarios TO 'sgapp_login'@'localhost';

-- 2. Usuario para estudiantes (solo lectura y registro)
CREATE USER 'sgapp_estudiante'@'localhost' IDENTIFIED BY 'Estudiante1234!';
GRANT SELECT ON bddsglab6.Equipos TO 'sgapp_estudiante'@'localhost';
GRANT SELECT ON bddsglab6.Laboratorios TO 'sgapp_estudiante'@'localhost';
GRANT SELECT, INSERT ON bddsglab6.Registro TO 'sgapp_estudiante'@'localhost';
GRANT SELECT ON bddsglab6.EquiposPerifericos TO 'sgapp_estudiante'@'localhost';
GRANT SELECT ON bddsglab6.Usuarios TO 'sgapp_estudiante'@'localhost';

-- 3. Usuario para docentes y asistentes
CREATE USER 'sgapp_docente'@'localhost' IDENTIFIED BY 'DocenteITI123!';
GRANT SELECT, INSERT, UPDATE ON bddsglab6.* TO 'sgapp_docente'@'localhost';
REVOKE DELETE ON bddsglab6.* FROM 'sgapp_docente'@'localhost';
REVOKE DROP ON bddsglab6.* FROM 'sgapp_docente'@'localhost';
REVOKE ALTER ON bddsglab6.* FROM 'sgapp_docente'@'localhost';
REVOKE CREATE ON bddsglab6.* FROM 'sgapp_docente'@'localhost';

-- 4. Usuario para administradores (control total)
CREATE USER 'sgapp_admin'@'localhost' IDENTIFIED BY 'AdministradorITI!';
GRANT SELECT, INSERT, UPDATE, DELETE ON bddsglab6.* TO 'sgapp_admin'@'localhost';
GRANT EXECUTE ON bddsglab6.* TO 'sgapp_admin'@'localhost';

-- 5. Usuario para backups (solo lectura)
CREATE USER 'sgapp_backup'@'localhost' IDENTIFIED BY 'BackupITIUTU!';
GRANT SELECT ON bddsglab6.* TO 'sgapp_backup'@'localhost';
GRANT LOCK TABLES ON bddsglab6.* TO 'sgapp_backup'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;
