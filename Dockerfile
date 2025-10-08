FROM php:8.3-cli

# Sistem paketleri ve PHP PDO
RUN apt-get update && apt-get install -y unzip git zip \
    && docker-php-ext-install pdo pdo_mysql

# Composer yükle
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

WORKDIR /var/www/html

COPY . .


