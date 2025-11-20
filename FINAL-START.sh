#!/bin/bash

echo "🚀 Configuration FINALE Elliteam - Post-Redémarrage"
echo "==================================================="
echo ""

# Vérifier qu'on est dans le bon répertoire
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ docker-compose.yml non trouvé"
    echo "📂 Allez dans le répertoire du projet : cd /chemin/vers/elliteam"
    exit 1
fi

echo "✅ Répertoire du projet : $(pwd)"
echo ""

# 1. Vérifier Docker
echo "1️⃣ Vérification de Docker..."
if ! systemctl is-active --quiet docker; then
    sudo systemctl start docker
    sleep 5
fi
echo "✅ Docker actif"
echo ""

# 2. Nettoyer (devrait fonctionner après redémarrage)
echo "2️⃣ Nettoyage..."
docker rm -f $(docker ps -aq) 2>/dev/null || echo "Pas de conteneurs à nettoyer"
echo "✅ Nettoyé"
echo ""

# 3. Créer le Dockerfile avec PHP 8.3
echo "3️⃣ Création du Dockerfile (PHP 8.3)..."
cat > Dockerfile << 'DOCKERFILE'
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
DOCKERFILE

echo "✅ Dockerfile créé"
echo ""

# 4. Construire
echo "4️⃣ Construction de l'image..."
docker-compose build --no-cache

if [ $? -ne 0 ]; then
    echo "⚠️  Erreur de construction, utilisation d'une image directe"
    cp docker-compose.yml docker-compose.yml.backup
    sed -i 's/build: \./image: php:8.3-fpm/g' docker-compose.yml
fi
echo ""

# 5. Démarrer
echo "5️⃣ Démarrage des conteneurs..."
docker-compose up -d

if [ $? -ne 0 ]; then
    echo "❌ Erreur au démarrage"
    docker-compose logs --tail=50
    exit 1
fi

echo "✅ Conteneurs démarrés"
echo ""

# 6. Attendre
echo "6️⃣ Attente (30 secondes)..."
sleep 30

# 7. État
echo "7️⃣ État des conteneurs :"
docker-compose ps
echo ""

# 8. Vérifications
if ! docker ps | grep -q php; then
    echo "❌ Conteneur PHP non démarré"
    docker-compose logs php
    exit 1
fi

if ! docker ps | grep -q mysql; then
    echo "❌ Conteneur MySQL non démarré"
    docker-compose logs mysql
    exit 1
fi

echo "✅ Conteneurs principaux actifs"
echo ""

# 9. Version PHP
echo "8️⃣ Vérification PHP..."
PHP_VERSION=$(docker-compose exec -T php php -v | head -n 1)
echo "✅ $PHP_VERSION"
echo ""

# 10. Git
echo "9️⃣ Configuration Git..."
docker-compose exec -T php git config --global --add safe.directory /var/www/html
echo "✅ Git configuré"
echo ""

# 11. Composer
echo "🔟 Installation Composer si nécessaire..."
if ! docker-compose exec -T php composer --version > /dev/null 2>&1; then
    docker-compose exec -T php bash -c "curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"
fi
COMPOSER_VERSION=$(docker-compose exec -T php composer --version 2>&1 | head -n 1)
echo "✅ $COMPOSER_VERSION"
echo ""

# 12. Nettoyer et réinstaller
echo "1️⃣1️⃣ Installation des dépendances..."
docker-compose exec -T php rm -rf vendor composer.lock 2>/dev/null
docker-compose exec -T php composer install --no-interaction --prefer-dist --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Erreur d'installation"
    exit 1
fi

echo "✅ Dépendances installées"
echo ""

# 13. Symfony
echo "1️⃣2️⃣ Vérification Symfony..."
docker-compose exec -T php chmod +x bin/console
SYMFONY_VERSION=$(docker-compose exec -T php php bin/console --version 2>&1 | head -n 1)
echo "✅ $SYMFONY_VERSION"
echo ""

# 14. Configuration DB
MYSQL_NAME=$(docker ps --format '{{.Names}}' | grep mysql | head -n 1)

echo "1️⃣3️⃣ Configuration..."
cat > .env.local << EOF
DATABASE_URL="mysql://elliteam:elliteam@${MYSQL_NAME}:3306/elliteam?serverVersion=8.0"
EOF

echo "✅ .env.local créé"
echo ""

# 15. Attendre MySQL
echo "1️⃣4️⃣ Attente MySQL (20 secondes)..."
sleep 20

# 16. Base de données
echo "1️⃣5️⃣ Configuration base de données..."
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists
docker-compose exec -T php php bin/console make:migration --no-interaction 2>/dev/null || true
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec -T php php bin/console app:init-roles
docker-compose exec -T php php bin/console app:create-user-with-roles admin@elliteam.com \
    --admin --password=Admin123! --firstname=Admin --lastname=Elliteam

echo ""
echo "==================================================="
echo "✨ INSTALLATION TERMINÉE !"
echo "==================================================="
echo ""
echo "📋 Connexion :"
echo "   Email    : admin@elliteam.com"
echo "   Password : Admin123!"
echo ""

NGINX_PORT=$(docker port symfony_nginx 80/tcp 2>/dev/null | cut -d: -f2)
[ -z "$NGINX_PORT" ] && NGINX_PORT="8080"

echo "🌐 URLs :"
echo "   Application : http://localhost:${NGINX_PORT}"
echo "   Login       : http://localhost:${NGINX_PORT}/login"
echo "   Admin       : http://localhost:${NGINX_PORT}/admin"
echo ""
echo "📊 Versions :"
echo "   PHP      : $(docker-compose exec -T php php -v | head -n 1 | awk '{print $2}')"
echo "   Symfony  : $(docker-compose exec -T php php bin/console --version 2>&1 | head -n 1 | awk '{print $3}')"
echo "   Composer : $(docker-compose exec -T php composer --version 2>&1 | awk '{print $3}')"
echo ""
echo "🔧 Commandes :"
echo "   Logs    : docker-compose logs -f"
echo "   Console : docker-compose exec php php bin/console"
echo "   Shell   : docker-compose exec php bash"
echo ""
echo "✅ TOUT EST PRÊT ! Bon développement ! 🚀"