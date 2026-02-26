FROM php:8.2-apache

# 🔥 Limpa QUALQUER MPM existente
RUN find /etc/apache2 -name "mpm_*.load" -delete
RUN find /etc/apache2 -name "mpm_*.conf" -delete

# 🔥 Recria só o prefork
RUN echo "LoadModule mpm_prefork_module /usr/lib/apache2/modules/mod_mpm_prefork.so" > /etc/apache2/mods-available/mpm_prefork.load
RUN a2enmod mpm_prefork

# PHP
RUN docker-php-ext-install mysqli

# Rewrite
RUN a2enmod rewrite

# Config PHP
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'max_execution_time = 30'; \
} > /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html