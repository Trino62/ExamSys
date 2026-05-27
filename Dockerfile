FROM php:8.2-apache

# Extensión PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite y respetar .htaccess en el DocumentRoot
RUN a2enmod rewrite \
    && echo '<Directory /var/www/html/>\n\tAllowOverride All\n\tOptions -Indexes\n</Directory>' \
       > /etc/apache2/conf-available/examsys.conf \
    && a2enconf examsys

# Copiar código al directorio público
COPY . /var/www/html/

# Permisos de escritura para que install.php pueda crear .env
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
