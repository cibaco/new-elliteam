FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip opcache pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html
WORKDIR /var/www/html

# Forcer prod AVANT tout
RUN echo "APP_ENV=prod" > .env.local
ENV APP_ENV=prod

RUN DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" composer install --no-dev --optimize-autoloader --no-scripts
RUN DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console cache:clear --env=prod

RUN chown -R www-data:www-data /var/www/html/var

CMD php -S 0.0.0.0:${PORT:-8080} -t public