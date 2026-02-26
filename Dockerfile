FROM php:8.2-apache

# Instalar extensões necessárias
RUN docker-php-ext-install mysqli

# Habilitar mod_rewrite para URLs amigáveis (opcional)
RUN a2enmod rewrite

# Configurações de segurança e upload no php.ini
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'max_execution_time = 30'; \
    echo 'expose_php = Off'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_strict_mode = 1'; \
} > /usr/local/etc/php/conf.d/escambo.ini

# Definir diretório de trabalho
WORKDIR /var/www/html
