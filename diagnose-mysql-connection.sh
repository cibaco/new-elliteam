#!/bin/bash

echo "🔍 Diagnostic connexion MySQL"
echo "=============================="
echo ""

# 1. Conteneurs
echo "1️⃣ Conteneurs actifs :"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""

# 2. Trouver les conteneurs
PHP_CONTAINER=$(docker ps -qf "name=php")
MYSQL_CONTAINER=$(docker ps -qf "name=mysql")

if [ -z "$PHP_CONTAINER" ]; then
    echo "❌ Conteneur PHP non trouvé"
    exit 1
fi

if [ -z "$MYSQL_CONTAINER" ]; then
    echo "❌ Conteneur MySQL non trouvé"
    exit 1
fi

PHP_NAME=$(docker ps --format '{{.Names}}' | grep php)
MYSQL_NAME=$(docker ps --format '{{.Names}}' | grep mysql)

echo "✅ PHP : $PHP_NAME"
echo "✅ MySQL : $MYSQL_NAME"
echo ""

# 3. Vérifier le réseau
echo "2️⃣ Réseaux Docker :"
PHP_NETWORK=$(docker inspect $PHP_CONTAINER | grep -A 1 '"Networks"' | tail -n 1 | awk -F'"' '{print $2}')
MYSQL_NETWORK=$(docker inspect $MYSQL_CONTAINER | grep -A 1 '"Networks"' | tail -n 1 | awk -F'"' '{print $2}')

echo "  PHP Network   : $PHP_NETWORK"
echo "  MySQL Network : $MYSQL_NETWORK"

if [ "$PHP_NETWORK" != "$MYSQL_NETWORK" ]; then
    echo "❌ Les conteneurs ne sont pas sur le même réseau !"
    echo ""
    echo "Solution : Modifier docker-compose.yml pour mettre les deux services sur le même réseau"
    exit 1
fi

echo "✅ Même réseau : $PHP_NETWORK"
echo ""

# 4. IP de MySQL
MYSQL_IP=$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $MYSQL_CONTAINER)
echo "3️⃣ IP de MySQL : $MYSQL_IP"
echo ""

# 5. Test ping depuis PHP vers MySQL
echo "4️⃣ Test de connexion réseau..."
if docker exec $PHP_CONTAINER ping -c 1 $MYSQL_NAME > /dev/null 2>&1; then
    echo "✅ Ping vers $MYSQL_NAME réussi"
    USE_NAME=true
elif docker exec $PHP_CONTAINER ping -c 1 $MYSQL_IP > /dev/null 2>&1; then
    echo "⚠️  Ping par IP réussi mais pas par nom"
    echo "   Utilisation de l'IP à la place"
    USE_NAME=false
else
    echo "❌ Impossible de joindre MySQL"
    exit 1
fi
echo ""

# 6. Configuration .env.local
echo "5️⃣ Configuration de .env.local..."

if [ "$USE_NAME" = true ]; then
    DB_HOST="$MYSQL_NAME"
else
    DB_HOST="$MYSQL_IP"
fi

cat > .env.local << ENVLOCAL
###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://elliteam:elliteam@${DB_HOST}:3306/elliteam?serverVersion=8.0"
###< doctrine/doctrine-bundle ###
ENVLOCAL

echo "✅ Configuration créée :"
cat .env.local
echo ""

# 7. Vider le cache Symfony
echo "6️⃣ Vidage du cache Symfony..."
docker-compose exec -T php php bin/console cache:clear --no-warmup
echo "✅ Cache vidé"
echo ""

# 8. Test connexion MySQL
echo "7️⃣ Test de connexion MySQL..."
docker exec -i $PHP_CONTAINER php << 'PHPCODE'
<?php
$dbHost = getenv('DATABASE_URL');
if (preg_match('/@([^:]+):/', file_get_contents('.env.local'), $matches)) {
    $host = $matches[1];
    try {
        $pdo = new PDO("mysql:host=$host;port=3306", "elliteam", "elliteam");
        echo "✅ Connexion MySQL réussie\n";
        exit(0);
    } catch(Exception $e) {
        echo "❌ Erreur : " . $e->getMessage() . "\n";
        exit(1);
    }
}
PHPCODE

if [ $? -eq 0 ]; then
    echo ""
    echo "8️⃣ Test Doctrine..."
    docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists
    
    if [ $? -eq 0 ]; then
        echo "✅ Base de données créée/vérifiée"
        echo ""
        echo "9️⃣ Création de la migration..."
        docker-compose exec -T php php bin/console make:migration --no-interaction
    fi
else
    echo ""
    echo "❌ Connexion échouée"
    echo ""
    echo "Solutions :"
    echo "1. Vérifiez les credentials dans docker-compose.yml"
    echo "2. Redémarrez les conteneurs : docker-compose restart"
    echo "3. Vérifiez les logs : docker-compose logs mysql"
fi

echo ""
echo "=============================="
echo "Diagnostic terminé"
echo "=============================="
