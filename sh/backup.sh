#!/bin/bash
set -e
# Datos de la Base de Datos
DB_USER="sgapp_backup"
DB_PASS="BackupITIUTU!"
DB_NAME="bddsglab6"
# Carpeta donde se guardara 
BACKUP_DIR="/home/usuario/respaldos_db/"
RETENTION_DAYS=7

# 1. Crear la carpeta de respaldos si no existe
mkdir -p "$BACKUP_DIR"

# 2. Definir el nombre del archivo de respaldo
TIMESTAMP=$(date +"%F_%H-%M-%S")
BACKUP_FILE_SQL="$BACKUP_DIR$DB_NAME-$TIMESTAMP.sql"

# 3. Crear el respaldo de la base de datos
mysqldump --user="$DB_USER" --password="$DB_PASS" --no-tablespaces "$DB_NAME" > "$BACKUP_FILE_SQL"

# 4. Comprimir el archivo .sql
gzip "$BACKUP_FILE_SQL"

# 5. Limpiar los respaldos antiguos
find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete

#crontab -e
# Ejecutar el respaldo todos los días a las 3:00 AM y guardar solo los errores en un log.
#0 3 * * * /ruta/completa/a/tu/backup_silencioso.sh >/dev/null 2>> /var/log/backup_errors.log