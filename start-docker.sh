#!/bin/bash
# =====================================================
# Script de inicio rápido para Linux/Mac
# Sistema de Gestión de Laboratorios
# =====================================================

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "======================================"
echo "Sistema de Gestión de Laboratorios"
echo "======================================"
echo ""

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo -e "${RED}[ERROR]${NC} Docker no está instalado."
    echo "Por favor, instala Docker desde: https://www.docker.com/products/docker-desktop"
    exit 1
fi

echo -e "${GREEN}[OK]${NC} Docker encontrado"
echo ""

# Verificar si existe .env
if [ ! -f .env ]; then
    echo -e "${YELLOW}[INFO]${NC} Creando archivo .env..."
    cp .env.docker .env
    echo ""
    echo -e "${YELLOW}[ATENCIÓN]${NC} Archivo .env creado."
    echo "Por favor, edita .env y configura tu GitHub OAuth antes de continuar."
    echo ""
    read -p "Presiona Enter para continuar..."
fi

# Levantar servicios
echo -e "${YELLOW}[INFO]${NC} Levantando servicios Docker..."
echo ""
docker-compose up -d

echo ""
echo "======================================"
echo -e "${GREEN}Servicios iniciados correctamente!${NC}"
echo "======================================"
echo ""
echo -e "Aplicación Web:   ${GREEN}http://localhost:8080${NC}"
echo -e "phpMyAdmin:       ${GREEN}http://localhost:8081${NC}"
echo ""
echo "Usuarios de prueba:"
echo "  Admin:    admin / admin123 (requiere 2FA)"
echo "  Docente:  docente1 / docente123"
echo ""
echo "Para ver logs:  docker-compose logs -f"
echo "Para detener:   docker-compose down"
echo ""
