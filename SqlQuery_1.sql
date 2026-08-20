# Variables
$serverName = "NombreDelServidor"     # Nombre del servidor SQL
$databaseName = "MiBaseDeDatos"       # Nombre de la base de datos
$backupPath = "C:\Ruta\Para\Guardar\El\Respaldo.bak"
$restorePath = "C:\Ruta\Donde\Restaurar\El\Respaldo.bak"
$backupFile = "MiBaseDeDatos.bak"

# Realizar un respaldo de la base de datos
Backup-SqlDatabase -ServerInstance $serverName -Database $databaseName -BackupFile $backupPath

# Comprobar si el respaldo se creó con éxito
if (Test-Path $backupPath) {
    Write-Host "Respaldo de $databaseName completado con éxito."
} else {
    Write-Host "Error: no se pudo crear el respaldo de $databaseName."
    exit
}

# Restaurar la base de datos desde el respaldo
Restore-SqlDatabase -ServerInstance $serverName -Database $databaseName -BackupFile $restorePath -RelocateFile ($databaseName + "_Data") $restorePath

# Comprobar si la restauración se realizó con éxito
if (Test-Path (Join-Path $restorePath $backupFile)) {
    Write-Host "Restauración de $databaseName completada con éxito."
} else {
    Write-Host "Error: no se pudo restaurar la base de datos $databaseName."
}
