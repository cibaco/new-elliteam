FROM php:8.3-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    git curl libzip-dev libicu-dev libpng-dev libonig-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN git config --global --add safe.directory '*'

RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory.ini

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
