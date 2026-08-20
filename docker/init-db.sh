#!/bin/bash
# =====================================================
# Script de inicialización de MySQL
# Crea usuarios adicionales con permisos específicos
# =====================================================

set -e

echo "🔧 Inicializando usuarios de MySQL..."

# Esperar a que MySQL esté completamente iniciado
sleep 10

# Crear usuarios adicionales con sus permisos
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    -- Usuario para login/autenticación
    CREATE USER IF NOT EXISTS 'sgapp_login'@'%' IDENTIFIED BY 'LoginUsuario123!';
    GRANT SELECT, UPDATE ON bddsglab6.Usuarios TO 'sgapp_login'@'%';
    GRANT SELECT, INSERT, UPDATE, DELETE ON bddsglab6.sesiones TO 'sgapp_login'@'%';
    GRANT SELECT, INSERT ON bddsglab6.logs_autenticacion TO 'sgapp_login'@'%';
    GRANT SELECT, INSERT, UPDATE ON bddsglab6.historial_passwords TO 'sgapp_login'@'%';

    -- Usuario para estudiantes
    CREATE USER IF NOT EXISTS 'sgapp_estudiante'@'%' IDENTIFIED BY 'Estudiante1234!';
    GRANT SELECT ON bddsglab6.Equipos TO 'sgapp_estudiante'@'%';
    GRANT SELECT ON bddsglab6.Laboratorios TO 'sgapp_estudiante'@'%';
    GRANT INSERT ON bddsglab6.Registro TO 'sgapp_estudiante'@'%';

    -- Usuario para docentes
    CREATE USER IF NOT EXISTS 'sgapp_docente'@'%' IDENTIFIED BY 'DocenteITI123!';
    GRANT SELECT, INSERT, UPDATE ON bddsglab6.* TO 'sgapp_docente'@'%';

    -- Usuario para backup
    CREATE USER IF NOT EXISTS 'sgapp_backup'@'%' IDENTIFIED BY 'BackupITIUTU!';
    GRANT SELECT ON bddsglab6.* TO 'sgapp_backup'@'%';

    FLUSH PRIVILEGES;
EOSQL

echo "✅ Usuarios de MySQL creados correctamente"
