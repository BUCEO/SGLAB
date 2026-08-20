@echo off
REM =====================================================
REM Script de inicio rápido para Windows
REM Sistema de Gestión de Laboratorios
REM =====================================================

echo ======================================
echo Sistema de Gestion de Laboratorios
echo ======================================
echo.

REM Verificar si Docker está instalado
docker --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker no esta instalado.
    echo Por favor, instala Docker Desktop desde: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo [OK] Docker encontrado
echo.

REM Verificar si existe .env
if not exist .env (
    echo [INFO] Creando archivo .env...
    copy .env.docker .env
    echo.
    echo [ATENCION] Archivo .env creado.
    echo Por favor, edita .env y configura tu GitHub OAuth antes de continuar.
    echo.
    pause
)

REM Levantar servicios
echo [INFO] Levantando servicios Docker...
echo.
docker-compose up -d

echo.
echo ======================================
echo Servicios iniciados correctamente!
echo ======================================
echo.
echo Aplicacion Web:   http://localhost:8080
echo phpMyAdmin:       http://localhost:8081
echo.
echo Usuarios de prueba:
echo   Admin:    admin / admin123 (requiere 2FA)
echo   Docente:  docente1 / docente123
echo.
echo Para ver logs: docker-compose logs -f
echo Para detener:  docker-compose down
echo.
pause
