FROM php:8.2-apache

# Instalar extensões necessárias
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurações do PHP
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'memory_limit = 256M'; \
    echo 'display_errors = Off'; \
    echo 'log_errors = On'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . .

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p uploads \
    && chown www-data:www-data uploads \
    && chmod 777 uploads

# Expor a porta 80
EXPOSE 80
