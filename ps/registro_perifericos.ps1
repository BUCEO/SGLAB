# Metodo alternativo para evitar problemas de ExecutionPolicy
function Get-SystemInfo {
    # 1. Informacion basica del sistema
    $computerInfo = Get-ComputerInfo
    $fecha = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $nombrePC = $env:COMPUTERNAME
    $usuario = $env:USERNAME

    # 2. Informacion del hardware (metodo WMI alternativo)
    $cpuInfo = Get-WmiObject Win32_Processor | Select-Object -First 1
    $osInfo = Get-WmiObject Win32_OperatingSystem
    $ramTotal = [math]::Round($osInfo.TotalVisibleMemorySize / 1MB, 2)
    $ramLibre = [math]::Round($osInfo.FreePhysicalMemory / 1MB, 2)
    $disk = Get-WmiObject Win32_LogicalDisk -Filter "DeviceID='C:'"
    
    # Obtener el tipo de disco (SSD o HDD) para la unidad C:
    $diskType = "No disponible" # Valor por defecto
    try {
        $physicalDisk = Get-PhysicalDisk -DeviceNumber (Get-Partition -DriveLetter C).DiskNumber
        $diskType = $physicalDisk.MediaType
    } catch {
        Write-Warning "No se pudo determinar el tipo de disco físico para C:."
    }
    
    # 3. Informacion de red basica
    $networkInfo = Get-NetIPConfiguration | Where-Object { $_.IPv4DefaultGateway -ne $null } | Select-Object -First 1
    $ipAddress = $networkInfo.IPv4Address.IPAddress
    $gateway = $networkInfo.IPv4DefaultGateway.NextHop
    $dnsServers = $networkInfo.DNSServer | Where-Object { $_.AddressFamily -eq 2 } | Select-Object -ExpandProperty ServerAddresses
    
    # Obtener MAC Address de la interfaz activa
    $adapterName = $networkInfo.InterfaceAlias
    $macAddress = (Get-NetAdapter -Name $adapterName).MacAddress

    # 4. Informacion de perifericos
    $perifericos = @()
    
    # Teclados
    $teclados = Get-WmiObject Win32_Keyboard | ForEach-Object {
        [PSCustomObject]@{
            Tipo = "Teclado"
            Nombre = $_.Name
            Estado = "Conectado"
        }
    }
    $perifericos += $teclados
    
    # Ratones
    $ratones = Get-WmiObject Win32_PointingDevice | ForEach-Object {
        [PSCustomObject]@{
            Tipo = "Raton"
            Nombre = $_.Name
            Estado = "Conectado"
        }
    }
    $perifericos += $ratones
    
    # Monitores
    $monitores = Get-WmiObject Win32_DesktopMonitor | ForEach-Object {
        [PSCustomObject]@{
            Tipo = "Monitor"
            Nombre = $_.Name
            Estado = "Conectado"
        }
    }
    $perifericos += $monitores
    
    # Discos externos/USB
    $discosUSB = Get-WmiObject Win32_DiskDrive | Where-Object { $_.InterfaceType -eq "USB" } | ForEach-Object {
        [PSCustomObject]@{
            Tipo = "Disco USB"
            Nombre = $_.Caption
            Estado = "Conectado"
            TamanoGB = [math]::Round($_.Size / 1GB, 2)
        }
    }
    $perifericos += $discosUSB
    
    # Impresoras
    $impresoras = Get-WmiObject Win32_Printer | Where-Object { $_.Local } | ForEach-Object {
        [PSCustomObject]@{
            Tipo = "Impresora"
            Nombre = $_.Name
            Estado = if ($_.WorkOffline) { "Desconectada" } else { "Conectada" }
        }
    }
    $perifericos += $impresoras

    # 5. Crear objeto con la informacion
    $systemData = [PSCustomObject]@{
        Fecha = $fecha
        NombrePC = $nombrePC
        Usuario = $usuario
        SistemaOperativo = $osInfo.Caption
        VersionOS = $osInfo.Version
        Arquitectura = $osInfo.OSArchitecture
        Fabricante = $computerInfo.CsManufacturer
        Modelo = $computerInfo.CsModel
        NumeroSerie = $computerInfo.BiosSeralNumber
        Procesador = $cpuInfo.Name
        Nucleos = $cpuInfo.NumberOfCores
        Hilos = $cpuInfo.NumberOfLogicalProcessors
        RAMTotalGB = $ramTotal
        RAMLibreGB = $ramLibre
        RAMUsoPorcentaje = [math]::Round(($ramTotal - $ramLibre) / $ramTotal * 100, 2)
        DiscoTotalGB = [math]::Round($disk.Size / 1GB, 2)
        DiscoLibreGB = [math]::Round($disk.FreeSpace / 1GB, 2)
        DiscoUsoPorcentaje = [math]::Round(($disk.Size - $disk.FreeSpace) / $disk.Size * 100, 2)
        TipoDiscoC = $diskType
        IPAddress = $ipAddress
        Gateway = $gateway
        DNSServers = $dnsServers -join ", "
        MACAddress = $macAddress
        ConexionLAN = (Test-Connection $gateway -Count 1 -Quiet).ToString()
        ConexionWAN = (Test-Connection "8.8.8.8" -Count 1 -Quiet).ToString()
        ConexionDNS = (Test-Connection "www.google.com" -Count 1 -Quiet).ToString()
        Perifericos = $perifericos
    }

    return $systemData
}

# Ejecutar la recoleccion de datos
try {
    $systemInfo = Get-SystemInfo
    
    # Crear archivo de reporte en el escritorio
    $reportPath = "$env:USERPROFILE\Desktop\SystemReport_$($systemInfo.NumeroSerie)_$(Get-Date -Format 'yyyyMMdd_HHmmss').toml"
    
    # Formatear la salida
    $reportContent = @"
=== INFORMACION DEL SISTEMA ===
Fecha: $($systemInfo.Fecha)
Nombre del PC: $($systemInfo.NombrePC)
Usuario: $($systemInfo.Usuario)

=== SISTEMA OPERATIVO ===
SO: $($systemInfo.SistemaOperativo)
Version: $($systemInfo.VersionOS)
Arquitectura: $($systemInfo.Arquitectura)

=== HARDWARE ===
Fabricante: $($systemInfo.Fabricante)
Modelo: $($systemInfo.Modelo)
Numero de Serie: $($systemInfo.NumeroSerie)
Procesador: $($systemInfo.Procesador)
Nucleos: $($systemInfo.Nucleos)
Hilos: $($systemInfo.Hilos)
RAM Total: $($systemInfo.RAMTotalGB) GB
RAM Libre: $($systemInfo.RAMLibreGB) GB
Uso de RAM: $($systemInfo.RAMUsoPorcentaje)%
Disco Total (C:): $($systemInfo.DiscoTotalGB) GB
Disco Libre (C:): $($systemInfo.DiscoLibreGB) GB
Uso de Disco: $($systemInfo.DiscoUsoPorcentaje)%
Tipo de Disco (C:): $($systemInfo.TipoDiscoC)

=== RED ===
Direccion IP: $($systemInfo.IPAddress)
Gateway: $($systemInfo.Gateway)
MAC Address: $($systemInfo.MACAddress)
DNS Servers: $($systemInfo.DNSServers)
Conexion LAN: $($systemInfo.ConexionLAN)
Conexion WAN: $($systemInfo.ConexionWAN)
Conexion DNS: $($systemInfo.ConexionDNS)

=== PERIFERICOS CONECTADOS ===
"@

    # Agregar informacion de perifericos
    foreach ($perif in $systemInfo.Perifericos) {
        $reportContent += "`n$($perif.Tipo): $($perif.Nombre) - $($perif.Estado)"
        if ($perif.TamanoGB) {
            $reportContent += " ($($perif.TamanoGB) GB)"
        }
    }

    # Guardar el reporte
    $reportContent | Out-File -FilePath $reportPath -Encoding UTF8
    
    Write-Host "Reporte generado con exito en: $reportPath" -ForegroundColor Green
    Start-Process notepad.exe $reportPath
    exit 0
}
catch {
    Write-Host "Error al generar el reporte: $_" -ForegroundColor Red
    exit 1
}
