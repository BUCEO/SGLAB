# ============================================
# Dockerfile - Imagen PHP + Apache
# ============================================
# Este archivo define CÓMO construir nuestra imagen

# ============================================
# INSTRUCCIÓN: FROM
# ============================================
# Define la imagen BASE desde la cual partimos
# 
# php:8.3-apache = PHP 8.3 con Apache preinstalado
# Basada en Debian
FROM php:8.3-apache

# ============================================
# INSTRUCCIÓN: LABEL
# ============================================
# Metadatos de la imagen (opcional pero recomendado)
LABEL maintainer="juan@example.com"
LABEL description="Imagen educativa de PHP con Apache para aprender Docker"
LABEL version="1.0"

# ============================================
# INSTRUCCIÓN: ARG
# ============================================
# Variables que se usan SOLO durante el build
# Se pueden pasar desde docker-compose o docker build
ARG DEBIAN_FRONTEND=noninteractive

# ============================================
# INSTRUCCIÓN: RUN
# ============================================
# Ejecuta comandos durante la construcción de la imagen
# Cada RUN crea una nueva capa en la imagen

# ──────────────────────────────────────────
# PASO 1: Actualizar sistema e instalar dependencias
# ──────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    # Herramientas básicas
    curl \
    wget \
    git \
    unzip \
    vim \
    # Librerías necesarias para extensiones PHP
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    # Cliente MySQL (para mysqladmin ping en health check)
    default-mysql-client \
    # Limpieza de cache para reducir tamaño de imagen
    && rm -rf /var/lib/apt/lists/*

# ──────────────────────────────────────────
# PASO 2: Configurar extensiones PHP que requieren configuración
# ──────────────────────────────────────────
# GD (para procesamiento de imágenes)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

# ──────────────────────────────────────────
# PASO 3: Instalar extensiones PHP
# ──────────────────────────────────────────
RUN docker-php-ext-install -j$(nproc) \
    # Para bases de datos
    pdo \
    pdo_mysql \
    mysqli \
    # Para manipular archivos ZIP
    zip \
    # Para imágenes
    gd \
    # Para internacionalización
    intl \
    # Para strings multibyte
    mbstring \
    # Para XML
    xml \
    # Para operaciones matemáticas precisas
    bcmath \
    # Para optimización de código PHP
    opcache \
    # Para metadata de imágenes
    exif

# ──────────────────────────────────────────
# PASO 4: Instalar Composer
# ──────────────────────────────────────────
# COPY --from: Copia archivos desde otra imagen
# Esto es una "multi-stage build"
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ──────────────────────────────────────────
# PASO 5: Configurar Apache
# ──────────────────────────────────────────
# Habilitar mod_rewrite (para URLs amigables)
RUN a2enmod rewrite

# Habilitar mod_headers (para headers HTTP personalizados)
RUN a2enmod headers

# Configurar DocumentRoot apuntando a /var/www/html/public
# El archivo de configuración se copia más adelante
RUN echo "ServerName SGLAB" >> /etc/apache2/apache2.conf

# ──────────────────────────────────────────
# PASO 6: Configurar PHP
# ──────────────────────────────────────────
# Copia archivo de configuración personalizado
COPY ./config/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configurar OPcache (cache de código PHP para mejor performance)
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini

# ============================================
# INSTRUCCIÓN: WORKDIR
# ============================================
# Define el directorio de trabajo dentro del contenedor
# Todos los comandos siguientes se ejecutarán desde aquí
WORKDIR /var/www/html

# ============================================
# INSTRUCCIÓN: COPY
# ============================================
# Copia archivos desde tu PC al contenedor
# (En este caso, solo copiamos el entrypoint)
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ============================================
# INSTRUCCIÓN: RUN (permisos)
# ============================================
# Configurar permisos correctos para Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ============================================
# INSTRUCCIÓN: EXPOSE
# ============================================
# Documenta qué puerto expone el contenedor
# (Nota: Esto NO publica el puerto, solo lo documenta)
EXPOSE 80

# ============================================
# INSTRUCCIÓN: HEALTHCHECK
# ============================================
# Define cómo Docker verifica si el contenedor está saludable
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s \
    CMD curl -f http://localhost/ || exit 1

# ============================================
# INSTRUCCIÓN: ENTRYPOINT
# ============================================
# Define el comando que se ejecuta cuando inicia el contenedor
# Nuestro script personalizado que prepara el ambiente
ENTRYPOINT ["docker-entrypoint.sh"]

# ============================================
# INSTRUCCIÓN: CMD
# ============================================
# Argumentos por defecto para el ENTRYPOINT
# apache2-foreground: Inicia Apache en primer plano
CMD ["apache2-foreground"]

# ============================================
# NOTAS EDUCATIVAS
# ============================================
#
# CAPAS (Layers):
# - Cada instrucción (FROM, RUN, COPY, etc.) crea una capa
# - Las capas se cachean para builds más rápidos
# - Por eso agrupamos múltiples comandos con && en un solo RUN
#
# MULTI-STAGE BUILD:
# - COPY --from=composer:latest
# - Permite copiar archivos de otras imágenes
# - Útil para tener herramientas de build sin incluirlas en la imagen final
#
# ORDEN DE INSTRUCCIONES:
# - Las instrucciones que cambian menos van primero (FROM, RUN apt-get)
# - Las que cambian más van al final (COPY código de tu app)
# - Esto aprovecha el cache de Docker para builds más rápidos
#
# TAMAÑO DE IMAGEN:
# - Cada RUN agrega una capa
# - Limpiar cache (rm -rf /var/lib/apt/lists/*) reduce tamaño
# - Agrupar comandos en un RUN reduce número de capas
#
# DIFERENCIA ENTRYPOINT vs CMD:
# - ENTRYPOINT: Comando principal (siempre se ejecuta)
# - CMD: Argumentos para ENTRYPOINT (se pueden sobrescribir)
# - Ejemplo: ENTRYPOINT ["script.sh"] CMD ["arg1", "arg2"]
#
# ============================================
