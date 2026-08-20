#!/bin/bash

# Termina el script si un comando falla
set -e

# --- Colores para la salida en la consola ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# --- Funciones de Logging ---
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" >&2
    exit 1
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARN: $1${NC}"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

# --- Funciones de Utilidad ---

# Verifica si el script se está ejecutando con privilegios de superusuario
check_sudo() {
    if [ "$EUID" -ne 0 ]; then
        error "Este script requiere privilegios de superusuario. Por favor, ejecútalo con sudo."
    fi
}

# Detecta la distribución de Linux
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        OS_VERSION=$VERSION_ID
    else
        error "No se pudo detectar la distribución de Linux."
    fi
}

# --- Funciones de Configuración Comunes ---

# Actualiza los paquetes del sistema
update_system() {
    log "Actualizando el sistema..."
    case "$OS" in
        ubuntu|debian)
            sudo apt-get update -qq
            sudo apt-get upgrade -y -qq
            ;;
        centos|rhel|fedora)
            sudo yum update -y -q
            ;;
        *)
            error "Sistema operativo no soportado: $OS"
            ;;
    esac
}

# Instala paquetes básicos
install_basic_tools() {
    log "Instalando herramientas básicas..."
    case "$OS" in
        ubuntu|debian)
            sudo apt-get install -y -qq git curl wget rsync openssh-client openssh-server \
                software-properties-common apt-transport-https ca-certificates \
                gnupg-agent unzip make nano htop vim
            ;;
        centos|rhel|fedora)
            sudo yum install -y -q git curl wget rsync openssh-clients openssh-server \
                unzip nano htop vim
            ;;
    esac
}

# --- Funciones para el Entorno LAMP Clásico ---

install_apache() {
    log "Instalando el servidor web Apache2..."
    sudo apt-get install -y -qq apache2
    log "Apache2 instalado correctamente."
}

install_mysql() {
    log "Instalando MySQL Server..."
    sudo apt-get install -y -qq mysql-server
    log "MySQL Server instalado correctamente."
}

install_php() {
    log "Agregando el repositorio PPA de Ondřej Surý para PHP 8.3..."
    sudo add-apt-repository ppa:ondrej/php -y > /dev/null
    sudo apt-get update -qq

    log "Instalando PHP 8.3 y extensiones (incluyendo mysqli)..."
    sudo apt-get install -y -qq php8.3 libapache2-mod-php8.3 php8.3-mysql
    log "PHP 8.3 instalado y configurado para Apache."
}

finalize_lamp_setup() {
    log "Reiniciando Apache2 para aplicar la configuración de PHP..."
    sudo systemctl restart apache2

    log "Creando una página de prueba de PHP para verificar la instalación..."
    echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php > /dev/null
}


# --- Funciones para el Entorno Docker + Laravel ---

install_docker() {
    log "Instalando Docker..."
    if command -v docker &> /dev/null; then
        warn "Docker ya está instalado"
        return
    fi
    # ... (código de instalación de Docker original)
}

install_docker_compose() {
    log "Instalando Docker Compose..."
    if command -v docker-compose &> /dev/null; then
        warn "Docker Compose ya está instalado"
        return
    fi
    # ... (código de instalación de Docker Compose original)
}

setup_ssh() {
    log "Configurando SSH..."
    # ... (código de configuración de SSH original)
}

setup_github_ssh() {
    log "Configurando acceso SSH a GitHub..."
    # ... (código de configuración de GitHub SSH original)
}

clone_repository() {
    local repo_url=$1
    local target_dir=${2:-"/opt/laravel-app"}
    # ... (código para clonar repositorio original)
}

setup_project() {
    log "Configurando el proyecto Laravel..."
    # ... (código de configuración del proyecto Laravel original)
}


# --- Flujos de Aprovisionamiento ---

provision_lamp_stack() {
    if [ "$OS" != "ubuntu" ] && [ "$OS" != "debian" ]; then
        error "El aprovisionamiento LAMP está optimizado para Ubuntu/Debian."
    fi
    log "Iniciando aprovisionamiento de un servidor LAMP clásico..."
    install_apache
    install_mysql
    install_php
    finalize_lamp_setup

    SERVER_IP=$(hostname -I | awk '{print $1}')
    log "¡Aprovisionamiento LAMP completado exitosamente!"
    info "Accede a la página de prueba en tu navegador: http://$SERVER_IP/info.php"
    warn "Por seguridad, recuerda asegurar tu instalación de MySQL ejecutando: sudo mysql_secure_installation"
    warn "Una vez verificado todo, elimina el archivo de prueba con: sudo rm /var/www/html/info.php"
}

provision_docker_laravel() {
    log "Iniciando aprovisionamiento para entorno de desarrollo Laravel con Docker..."
    install_docker
    install_docker_compose
    setup_ssh
    setup_github_ssh
    
    read -p "Introduce la URL SSH de tu repositorio GitHub (ej: git@github.com:usuario/repo.git): " repo_url
    if [ -z "$repo_url" ]; then
        error "Debes proporcionar una URL de repositorio válida"
    fi
    
    clone_repository "$repo_url"
    setup_project
    
    log "Provisionamiento completado exitosamente!"
    info "Acceso a la aplicación: http://localhost"
    info "Acceso a PHPMyAdmin: http://localhost:8080"
    warn "Recuerda que has tenido que agregar manualmente la clave SSH a tu cuenta de GitHub."
}


# --- Función Principal ---
main() {
    log "Iniciando proceso de aprovisionamiento"
    
    check_sudo
    detect_os
    info "Sistema operativo detectado: $OS $OS_VERSION"
    
    update_system
    install_basic_tools
    
    info "Por favor, selecciona el tipo de entorno que deseas aprovisionar:"
    echo -e "${YELLOW}1) Entorno de desarrollo Laravel con Docker (Git, Docker, Docker Compose)${NC}"
    echo -e "${YELLOW}2) Servidor LAMP clásico (Apache, MySQL, PHP en el host)${NC}"
    read -p "Introduce tu elección [1-2]: " choice

    case $choice in
        1)
            provision_docker_laravel
            ;;
        2)
            provision_lamp_stack
            ;;
        *)
            error "Opción no válida. Saliendo."
            ;;
    esac
}

# Ejecutar la función principal
main "$@"
