FROM php:8.2-apache

# 🔥 GARANTE que só um MPM vai rodar
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2enmod mpm_prefork

# Instalar extensões
RUN docker-php-ext-install mysqli

# Rewrite
RUN a2enmod rewrite

# PHP config
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'max_execution_time = 30'; \
    echo 'expose_php = Off'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_strict_mode = 1'; \
} > /usr/local/etc/php/conf.d/escambo.ini

WORKDIR /var/www/html