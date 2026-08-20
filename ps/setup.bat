echo off
set USUARIO=%USERNAME%
echo Configurando el entorno para el usuario: %USUARIO%
echo Ejecutar los siguiente comandos en una consola de PS administraviva
echo get-ExecutionPolicy
echo Set-ExecutionPolicy Unrestricted -Scope Process -Force
echo Intentado Copiar el archivo registro.ps1 a la carpeta: 
echo C:\Users\USUARIO%\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup
echo
echo Copiando archivo para ejecutar al inicio...
copy ..\ps\registro.ps1 "C:\Users\%USUARIO%\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup\registro.ps1"
echo Archivo copiado.
echo Configuracion completada.

copy "C:\Users\%USUARIO%\SGLAB\ps\registro-estudiante.lnk "C:\Users\%USUARIO%\Desktop\registro-estudiante.lnk"  
echo Acceso directo creado en el escritorio.
echo.   
pause