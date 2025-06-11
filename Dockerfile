# Dockerfile para el servicio PHP
FROM php:8.2-apache

# Instala dependencias del sistema
RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev \
        libzip-dev \
        libxml2-dev \
        libgd-dev \
        libwebp-dev \
        libjpeg-dev \
        libpng-dev \
        libxpm-dev \
        unzip \
        && \
    rm -rf /var/lib/apt/lists/*

# Instala extensiones necesarias
RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    xml \
    gd

# Habilita mod_rewrite de Apache
RUN a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia solo los archivos necesarios para composer primero
COPY composer.json composer.lock /var/www/html/

# Instala dependencias ignorando temporalmente los requisitos de plataforma
RUN cd /var/www/html && \
    composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Crea el directorio storage si no existe
RUN mkdir -p /var/www/html/storage

# Copia el resto del código fuente
COPY . /var/www/html/

# Da permisos adecuados (solo si el directorio existe)
RUN chown -R www-data:www-data /var/www/html && \
    [ -d "/var/www/html/storage" ] && chmod -R 755 /var/www/html/storage || true

# Expone el puerto 80
EXPOSE 80
