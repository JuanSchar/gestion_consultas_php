# Dockerfile para el servicio PHP
FROM php:8.2-apache

# Instala extensiones necesarias para PHP y MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita mod_rewrite de Apache
RUN a2enmod rewrite

# Copia el código fuente al contenedor
COPY . /var/www/html/

# Da permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto 80
EXPOSE 80
