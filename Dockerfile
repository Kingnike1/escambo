FROM php:8.2-apache

# 🔥 REMOVE QUALQUER MPM carregado
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load

# 🔥 ATIVA SOMENTE O PREFORK (compatível com PHP)
RUN a2enmod mpm_prefork

# Instalar extensões
RUN docker-php-ext-install mysqli

# Ativar rewrite
RUN a2enmod rewrite

# Configuração PHP
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'max_execution_time = 30'; \
    echo 'expose_php = Off'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_strict_mode = 1'; \
} > /usr/local/etc/php/conf.d/escambo.ini

WORKDIR /var/www/html