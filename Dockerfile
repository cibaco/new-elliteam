FROM php:8.2-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip opcache pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
RUN APP_ENV=prod DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" composer install --no-dev --optimize-autoloader --no-scripts
RUN APP_ENV=prod DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console cache:clear

RUN chown -R www-data:www-data /var/www/html/var

RUN echo '#!/bin/bash\nsed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf\nsed -i "s/:80/:${PORT:-8080}/" /etc/apache2/sites-available/000-default.conf\napache2-foreground' > /start.sh \
    && chmod +x /start.sh

CMD ["/start.sh"]