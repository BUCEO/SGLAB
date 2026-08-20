#!/bin/bash

opcion=10

while [ $opcion != "0" ]; do
    clear
    echo "========== MENU DE INSTALACIÓN =========="
    echo "1) Actualizar el sistema"
    echo "2) Instalar Apache2"
    echo "3) Instalar PHP 8.3 + extensiones"
    echo "4) Instalar MySQL Server"
    echo "5) Instalar Git"
    echo "6) Instalar OpenSSH Server"
    echo "7) Clonar repositorio del proyecto"
    echo "8) Instalar TODO"
    echo "0) Salir"
    echo "=========================================="
    read -p "Seleccione una opción: " opcion

    case $opcion in
        1)
            echo "🔄 Actualizando el sistema..."
            sudo apt update && sudo apt upgrade -y
            ;;
        2)
            echo "🌐 Instalando Apache2..."
            sudo apt install -y apache2
            sudo systemctl enable apache2
            sudo systemctl start apache2
            sudo systemctl status apache2
            ;;
        3)
            echo "🐘 Instalando PHP 8.3 y extensiones..."
            if ! php -v | grep -q "8.3"; then
                sudo add-apt-repository ppa:ondrej/php -y
                sudo apt update
            fi
            sudo apt install -y php8.3 php8.3-cli php8.3-common php8.3-mysql php8.3-pdo libapache2-mod-php8.3
            ;;
        4)
            echo "💾 Instalando MySQL Server..."
            sudo apt install -y mysql-server
            sudo mysql_secure_installation
            ;;
        5)
            echo "🔧 Instalando Git..."
            sudo apt install -y git
            git --version
            ;;
        6)
            echo "🔐 Instalando OpenSSH Server..."
            sudo apt install -y openssh-server
            sudo systemctl enable ssh
            sudo systemctl start ssh
            sudo systemctl status ssh
            ;;
        7)
            echo "📦 Clonando el repositorio del proyecto..."
            git clone https://github.com/UTU-ITI/Los-Cosmicos.git
            ;;
        8)
            echo "🚀 Instalando todo el sistema..."

            echo "🔄 Actualizando el sistema..."
            sudo apt update && sudo apt upgrade -y

            echo "🌐 Instalando Apache2..."
            sudo apt install -y apache2
            sudo systemctl enable apache2
            sudo systemctl start apache2
            sudo systemctl status apache2

            echo "🐘 Instalando PHP 8.3 y extensiones..."
            if ! php -v | grep -q "8.3"; then
                sudo add-apt-repository ppa:ondrej/php -y
                sudo apt update
            fi
            sudo apt install -y php8.3 php8.3-cli php8.3-common php8.3-mysql php8.3-pdo libapache2-mod-php8.3

            echo "💾 Instalando MySQL Server..."
            sudo apt install -y mysql-server
            sudo mysql_secure_installation

            echo "🔧 Instalando Git..."
            sudo apt install -y git
            git --version

            echo "🔐 Instalando OpenSSH Server..."
            sudo apt install -y openssh-server
            sudo systemctl enable ssh
            sudo systemctl start ssh
            sudo systemctl status ssh
            

            echo "✅ Instalación completa."
            ;;
        0)
            echo "👋 Saliendo..."
            break
            ;;
        *)
            echo "❌ Opción inválida. Intente de nuevo."
            ;;
    esac

    echo ""
    read -p "Presione Enter para continuar..."
done
