# Script PowerShell: Diagnóstico del PC y exportación en formato TOML con clave SSH
param(
    [int]$cedula = 0
)

$usuario = "Tecnicatura"
#$usuario = [Environment]::UserName
#$usuario = $usuario.ToLower()
$servidor = "192.168.2.46"
$laboratorio= "LAB6"
$destino = "/home/" + $usuario + "/SGLAB/" + $laboratorio
$clavePrivada = Join-Path $env:USERPROFILE ".ssh/id_rsa"
# $log variable removed as it was unused


# Obtener fecha y nombre del equipo
$fecha = Get-Date -Format "yyyyMMdd"
$fechaLegible = Get-Date -Format "yyyy/MM/dd (HH:mm)"
$nombrePC = $env:COMPUTERNAME
$serialPC = $((Get-CimInstance Win32_BIOS).SerialNumber)
$serial = $serialPC.Trim()
# Función para generar clave SSH si no existe
function New-SSHKey {
    $sshDir = "$env:USERPROFILE\.ssh"
    $privateKeyPath = "$sshDir\id_rsa"
    $publicKeyPath = "$sshDir\id_rsa.pub"
    
    if (!(Test-Path $sshDir)) {
        try {
            New-Item -ItemType Directory -Path $sshDir -Force | Out-Null
            Write-Output "Directorio .ssh creado"
        }
        catch {
            Write-Output "Error al crear directorio .ssh: $($_.Exception.Message)"
            return "ERROR: No se pudo crear el directorio .ssh"
        }
    }
    
    if (Test-Path $publicKeyPath) {
        try {
            $claveExistente = Get-Content $publicKeyPath -Raw
            return $claveExistente
        }
        catch {
            Write-Output "Error al leer la clave existente: $($_.Exception.Message)"
            return "ERROR: No se pudo leer la clave SSH existente"
        }
    }
    else {
         Write-Output "Generando nueva clave SSH..."
        Write-Output "Directorio .ssh = $sshDir"
        try {
            Get-Command ssh-keygen -ErrorAction Stop | Out-Null
        }
        catch {
            Write-Output "ssh-keygen no está disponible en el sistema"
            return "ERROR: ssh-keygen no disponible. Instale OpenSSH Client."
        }
        $sshKeygenArgs = @(
            "-t", "rsa",
            "-b", "2048", 
            "-f", $privateKeyPath,
            "-N", '""',
            "-C", "$env:USERNAME@$env:COMPUTERNAME"
        )
    
        try {
            & ssh-keygen @sshKeygenArgs
            if (Test-Path $publicKeyPath) {
                Write-Output "Clave SSH generada exitosamente"
                $nuevaClave = Get-Content $publicKeyPath -Raw
                return $nuevaClave
            }
            else {
                Write-Output "Error al generar la clave SSH"
                return "ERROR: No se pudo generar la clave SSH"
            }
        }
        catch {
            Write-Output "Error ejecutando ssh-keygen: $_"
            return "ERROR: ssh-keygen falló - $($_.Exception.Message)"
        }
    }
}

   

# Generar o obtener clave SSH pública
Write-Output "========== GESTION DE CLAVES SSH =========="
$clavePublica = New-SSHKey
Write-Output "=========================================="

# Intentar hacer ping al Gateway
Write-Output "Verificando conectividad..."
try {
    $gateway = (Get-NetRoute -DestinationPrefix "0.0.0.0/0").NextHop
    $ping = Test-Connection -ComputerName $gateway -Count 1 -Quiet
    if ($ping) {
        $estado = "Exito"
    }
    else {
        $estado = "Fallo"
    }
}
catch {
    $estado = "Error"
    $gateway = "No disponible"
}

# Obtener información del sistema
Write-Output "Recopilando información del sistema..."
try {
    $so = Get-CimInstance Win32_OperatingSystem
    $ipInfo = Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.PrefixOrigin -eq "Dhcp" -or $_.PrefixOrigin -eq "Manual"} | Select-Object -First 1
    $cpu = Get-CimInstance Win32_Processor
    $ram = [math]::Round($so.TotalVisibleMemorySize / 1024 / 1024, 2)
    $disco = Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'"
    $discoTotal = [math]::Round($disco.Size / 1GB, 2)
}
catch {
    Write-Output "Error al recopilar información del sistema: $($_.Exception.Message)"
    exit 1
}

# Función para validar cédula uruguaya con dígito verificador
function Test-CedulaUruguaya {
    param([string]$cedula)

    # Verificar formato básico (1-9 dígitos)
    if ($cedula -notmatch '^\d{1,9}$') {
        return $false
    }

    # Algoritmo de validación de cédula uruguaya
    $cedulaStr = $cedula.PadLeft(7, '0')
    $digitos = $cedulaStr.ToCharArray() | ForEach-Object { [int]::Parse($_) }

    # Coeficientes: 2,9,8,7,6,3,4
    $coeficientes = @(2,9,8,7,6,3,4)
    $suma = 0

    for ($i = 0; $i -lt 7; $i++) {
        $suma += $digitos[$i] * $coeficientes[$i]
    }

    $resto = $suma % 10
    $digitoVerificador = if ($resto -eq 0) { 0 } else { 10 - $resto }

    # Si tiene 8 dígitos, validar el último como verificador
    if ($cedula.Length -eq 9) {
        $ultimoDigito = [int]::Parse($cedula.Substring(8, 1))
        return $ultimoDigito -eq $digitoVerificador
    }

    # Si tiene menos de 8 dígitos, es válida (no incluye verificador)
    return $true
}

# Solicitar y validar cédula del usuario
if ($cedula -eq 0) {
    Write-Output "Ejecutando en modo desatendido con cedula = 0"
} else {
    # Si se pasó parámetro diferente de 0, solicitar cédula
    do {
        $inputCedula = Read-Host "Ingrese su cedula (maximo 9 digitos con digito verificador 1234567-8)"
        $cedulaValida = Test-CedulaUruguaya $inputCedula
        if (-not $cedulaValida) {
            Write-Output "Cédula inválida. Debe contener solo números (1-9   dígitos) y tener dígito verificador válido si tiene 8 dígitos."
        } else {
            $cedula = [int]$inputCedula
        }
    } until ($cedulaValida)
}

# Ruta y nombre de archivo de salida (en el Escritorio del usuario)
$escritorio = [Environment]::GetFolderPath("Desktop")
$archivo = Join-Path $escritorio "$fecha-$nombrePC-$estado-$serial.toml"

# Crear contenido TOML
$contenido = @"
[info_general]
serial = $serial
fecha = "$($fechaLegible)"
nombre_pc = "$($nombrePC)"
estado_conexion = "$($estado)"

[sistema_operativo]
nombre = "$($so.Caption)"
version = "$($so.Version)"

[red]
ip = "$($ipInfo.IPAddress)"
mascara = "$($ipInfo.PrefixLength)"
default_gateway = "$($gateway)"


[hardware]
cpu = "$($cpu.Name)"
ram_gb = $ram
disco_c_gb = $discoTotal

[ssh]
clave_publica = "$($clavePublica.Trim())"
usuario = "$($env:USERNAME)"
ruta_clave = "$($clavePrivada + '.pub')"

[usuario_responsable]
cedula = "$cedula"

#[diagnostico_red]
#LAN = "$($pingLAN -replace 'True','Exito' -replace 'False','Fallo')"
#WAN = "$($pingWAN -replace 'True','Exito' -replace 'False','Fallo')"
#DHCP = "$($usaDHCP -replace 'True','Exito' -replace 'False','Fallo')"
#DNS = "$($dnsOK -replace 'True','Exito' -replace 'False','Fallo')"

[recursos_disponibles]
cpu_uso_porcentaje = $cpuUso
ram_uso_porcentaje = $ramUsoPorc
disco_c_uso_porcentaje = $discoUso

"@

# Guardar archivo
try {
    $contenido | Out-File -FilePath $archivo -Encoding utf8
    Write-Output "Archivo TOML guardado: $archivo"
}
catch {
    Write-Output "Error al guardar archivo: $($_.Exception.Message)"
    Write-Output "========== SCRIPT con ERROR=========="
    exit 1
}

# Mostrar información en consola
Write-Output ""
Write-Output "========== DIAGNOSTICO COMPLETADO =========="
Write-Output "Archivo generado: $archivo"
Write-Output "Usuario: $usuario"
Write-Output "Servidor SSH: $servidor"
Write-Output "Directorio destino: $destino"
Write-Output ""
Write-Output "========== CLAVE SSH PUBLICA =========="
Write-Output $clavePublica
Write-Output "======================================"
Start-Sleep 2
# Código SCP con verificación de directorio destino
try {
	$ping = Test-Connection -ComputerName $servidor -Count 1 -Quiet
	    if ($ping) {
            # Verificar si el directorio destino existe en el servidor
            Write-Output "Verificando directorio destino en servidor..."
            $checkDirCmd = "ssh -i `"$clavePrivada`" -o StrictHostKeyChecking=no ${usuario}@${servidor} 'test -d ${destino} && echo EXISTS || echo NOTEXISTS'"
            try {
                $dirExists = Invoke-Expression $checkDirCmd
                if ($dirExists -match "NOTEXISTS") {
                    Write-Output "El directorio ${destino} no existe en el servidor ${servidor}"
                    Write-Output "Intenta crear el directorio con: ssh ${usuario}@${servidor} 'mkdir -p ${destino}'"
                    Write-Output "========== SCRIPT con ERROR=========="
                    exit 1
                }
                Write-Output "Directorio destino verificado: ${destino}"
            }
            catch {
                Write-Output "Error al verificar directorio destino: $($_.Exception.Message)"
                Write-Output "Continuando con SCP de todas formas..."
            }

            # Subir archivo con SCP
            try {
                Write-Output "Subiendo archivo:  scp -i `"$clavePrivada`" `"$archivo`" ${usuario}@${servidor}:${destino}/"
		        scp -i "$clavePrivada" -o StrictHostKeyChecking=no "$archivo" "${usuario}@${servidor}:${destino}/"
                Write-Output "========== SCRIPT COMPLETADO con EXITO=========="
            }
            catch {
                Write-Output "Error al Subir archivo: $($_.Exception.Message)"
                Write-Output "No existe el ${destino}, en el ${servidor}..."
                Write-Output "========== SCRIPT con ERROR=========="
                exit 1
            }
        }
        else {
      	    Write-Output "Fallo: Revisar conexion a servidor SSH: $servidor"
            Write-Output "========== SCRIPT CON ERROR=========="
            Write-Output "========== HAY QUE ESTAR EN LA RED DE UTU (192.168.2.0/24)=========="
            Write-Output "========== Tu dirección ip actual es=($($ipInfo.IPAddress))=========="
    	    exit 1
        }
}
catch {
    Write-Output "Error al ejecutar ping: $($_.Exception.Message)"
    Write-Output "========== SCRIPT con ERROR=========="
    exit 1
}

Write-Output "========== SCRIPT COMPLETADO con EXITO=========="
