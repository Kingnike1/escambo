FROM php:8.2-apache

# Corrigir conflito de MPM
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# Instalar extensões necessárias
RUN docker-php-ext-install mysqli

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurações PHP
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'max_execution_time = 30'; \
    echo 'expose_php = Off'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_strict_mode = 1'; \
} > /usr/local/etc/php/conf.d/escambo.ini

WORKDIR /var/www/html