#!/bin/bash

# Script para creacion de usuarios usando adduser
# Fecha: $(date +%Y-%m-%d)

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' 

# Funcion para mostrar mensajes de éxito
success() {
    echo -e "${GREEN}[✓] $1${NC}"
}

# Funcion para mostrar mensajes de error
error() {
    echo -e "${RED}[✗] $1${NC}"
}

# Funcion para mostrar advertencias
warning() {
    echo -e "${YELLOW}[!] $1${NC}"
}

# Funcion para mostrar información
info() {
    echo -e "${BLUE}[i] $1${NC}"
}

# Verificar si el script se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   error "Este script debe ejecutarse como root"
   echo "Ejecuta con: sudo $0"
   exit 1
fi

# Verificar si adduser está disponible
if ! command -v adduser &> /dev/null; then
    error "El comando 'adduser' no está disponible"
    echo "Este comando es común en Debian/Ubuntu y derivados"
    echo "En otras distribuciones puedes instalarlo o usar useradd"
    exit 1
fi

# Función para crear usuario con adduser
crear_usuario_adduser() {
    local nombre_usuario=$1
    
    # Verificar si el usuario ya existe
    if id "$nombre_usuario" &>/dev/null; then
        warning "El usuario '$nombre_usuario' ya existe"
        return 1
    fi
    
    info "Creando usuario: $nombre_usuario"
    info "adduser te hará varias preguntas interactivas:"
    echo "-----------------------------------------"
    echo "1. Contraseña (se pedirá dos veces)"
    echo "2. Información personal (opcional)"
    echo "3. Confirmación de los datos"
    echo "-----------------------------------------"
    
    # Pausa para que el usuario lea
    read -p "Presiona Enter para continuar..."
    
    # Ejecutar adduser (será interactivo)
    if adduser "$nombre_usuario"; then
        success "Usuario '$nombre_usuario' creado exitosamente con adduser"
        return 0
    else
        error "Error al crear el usuario '$nombre_usuario' con adduser"
        return 1
    fi
}

# Función para verificar información del usuario
verificar_usuario() {
    local nombre_usuario=$1
    
    echo ""
    info "INFORMACIÓN DEL USUARIO CREADO:"
    echo "========================================="
    
    # Verificar si el usuario existe
    if id "$nombre_usuario" &>/dev/null; then
        success "✓ Usuario existe en el sistema"
        
        # Mostrar información del usuario
        echo "Nombre: $nombre_usuario"
        echo "UID: $(id -u "$nombre_usuario")"
        echo "GID: $(id -g "$nombre_usuario")"
        echo "Grupos: $(id -Gn "$nombre_usuario")"
        echo "Home: /home/$nombre_usuario"
        echo "Shell: $(getent passwd "$nombre_usuario" | cut -d: -f7)"
        
        # Verificar si el directorio home existe
        if [[ -d "/home/$nombre_usuario" ]]; then
            success "✓ Directorio home creado: /home/$nombre_usuario"
            echo "Permisos: $(ls -ld "/home/$nombre_usuario" | awk '{print $1}')"
            echo "Propietario: $(ls -ld "/home/$nombre_usuario" | awk '{print $3}')"
        else
            warning "✗ Directorio home no existe"
        fi
        
    else
        error "✗ El usuario no fue creado correctamente"
    fi
    echo "========================================="
}

# Función principal para crear un usuario
crear_usuario_individual() {
    echo "========================================="
    echo "CREACIÓN DE USUARIO CON ADDUSER"
    echo "========================================="
    
    # Solicitar nombre de usuario
    read -p "Ingrese el nombre del nuevo usuario: " nombre_usuario
    
    # Validar nombre de usuario
    if [[ -z "$nombre_usuario" ]]; then
        error "El nombre de usuario no puede estar vacío"
        return 1
    fi
    
    if [[ ! "$nombre_usuario" =~ ^[a-z_][a-z0-9_-]*$ ]]; then
        error "Nombre de usuario inválido. Debe comenzar con letra minúscula o _"
        return 1
    fi
    
    # Crear usuario con adduser
    if crear_usuario_adduser "$nombre_usuario"; then
        verificar_usuario "$nombre_usuario"
        
        # Mostrar instrucciones de uso
        echo ""
        info "INSTRUCCIONES PARA USAR EL USUARIO:"
        echo "Para conectarse:"
        echo "  su - $nombre_usuario"
        echo "  o"
        echo "  login $nombre_usuario"
        echo ""
        echo "Para SSH (si está configurado):"
        echo "  ssh $nombre_usuario@localhost"
    fi
}

# Función para crear múltiples usuarios
crear_multiples_usuarios() {
    echo "========================================="
    echo "CREACIÓN DE MÚLTIPLES USUARIOS"
    echo "========================================="
    
    while true; do
        read -p "Ingrese el nombre del usuario (o 'fin' para terminar): " nombre_usuario
        
        if [[ "$nombre_usuario" == "fin" || "$nombre_usuario" == "exit" ]]; then
            success "Proceso de creación de usuarios terminado"
            break
        fi
        
        if [[ -z "$nombre_usuario" ]]; then
            warning "Nombre de usuario no válido"
            continue
        fi
        
        if id "$nombre_usuario" &>/dev/null; then
            warning "El usuario '$nombre_usuario' ya existe"
            continue
        fi
        
        crear_usuario_adduser "$nombre_usuario"
        verificar_usuario "$nombre_usuario"
        echo "-----------------------------------------"
    done
}

# Función para mostrar ayuda
mostrar_ayuda() {
    echo "========================================="
    echo "AYUDA: SCRIPT DE CREACIÓN DE USUARIOS"
    echo "========================================="
    echo "Este script usa 'adduser' que es interactivo:"
    echo ""
    echo "Durante la creación, adduser te pedirá:"
    echo "1. Contraseña (2 veces para confirmar)"
    echo "2. Información personal (opcional, puedes saltar con Enter)"
    echo "3. Confirmación de los datos (Y/n)"
    echo ""
    echo "Ventajas de adduser:"
    echo "- Crea automáticamente el directorio home"
    echo "- Copia archivos de /etc/skel"
    echo "- Configura permisos correctamente"
    echo "- Es interactivo y user-friendly"
    echo ""
    echo "Distribuciones compatibles:"
    echo "- Debian, Ubuntu, Mint y derivados"
    echo "========================================="
}

# Menú principal
echo "========================================="
echo "SISTEMA DE CREACIÓN DE USUARIOS CON ADDUSER"
echo "========================================="
echo "1) Crear un usuario"
echo "2) Crear múltiples usuarios"
echo "3) Mostrar ayuda"
echo "4) Salir"
echo "========================================="

read -p "Seleccione una opción (1-4): " opcion

case $opcion in
    1)
        crear_usuario_individual
        ;;
    2)
        crear_multiples_usuarios
        ;;
    3)
        mostrar_ayuda
        ;;
    4)
        echo "Saliendo..."
        exit 0
        ;;
    *)
        error "Opción inválida"
        exit 1
        ;;
esac
