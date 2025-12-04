FROM php:8.3-fpm

# Évite le warning Composer avec root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
        intl \
        gd \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Permet d'éviter des erreurs Git dans le conteneur (Symfony Flex)
RUN git config --global --add safe.directory '*'

# Config PHP de base
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=50M" > /usr/local/etc/php/conf.d/posts.ini \
    && echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini


# Dossier de travail
WORKDIR /var/www/html

# Port par défaut de PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
