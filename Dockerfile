FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip opcache pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html
WORKDIR /var/www/html

RUN APP_ENV=prod DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" composer install --no-dev --optimize-autoloader --no-scripts
RUN APP_ENV=prod DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console cache:clear

RUN chown -R www-data:www-data /var/www/html/var

# Forcer le mode prod
ENV APP_ENV=prod
RUN echo "APP_ENV=prod" > /var/www/html/.env.local

CMD php -S 0.0.0.0:${PORT:-8080} -t public