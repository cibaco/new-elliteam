
FROM php:8.2-apache

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip opcache

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier le code
COPY . /var/www/html

# Configurer Apache pour pointer vers /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Installer les dépendances
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data /var/www/html/var

# Script de démarrage qui configure le port au runtime
RUN echo '#!/bin/bash\nsed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf\nsed -i "s/:80/:${PORT:-8080}/" /etc/apache2/sites-available/000-default.conf\napache2-foreground' > /start.sh \
    && chmod +x /start.sh

CMD ["/start.sh"]
