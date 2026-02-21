.PHONY: help build up down restart logs shell composer symfony-console clear-cache test db-create db-migrate

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: ## Construit les images Docker
	docker compose build --no-cache

up: ## Démarre tous les conteneurs
	docker compose up -d

down: ## Arrête tous les conteneurs
	docker compose down

restart: down up ## Redémarre tous les conteneurs

logs: ## Affiche les logs en temps réel
	docker compose logs -f

logs-php: ## Affiche les logs PHP uniquement
	docker compose logs -f php

logs-nginx: ## Affiche les logs Nginx uniquement
	docker compose logs -f nginx

shell: ## Ouvre un shell dans le conteneur PHP
	docker compose exec php bash

shell-root: ## Ouvre un shell root dans le conteneur PHP
	docker compose exec -u root php bash

composer: ## Installe les dépendances Composer
	docker compose exec php composer install

composer-update: ## Met à jour les dépendances Composer
	docker compose exec php composer update

symfony-console: ## Exécute une commande Symfony console (usage: make symfony-console CMD="debug:router")
	docker compose exec php php bin/console $(CMD)

clear-cache: ## Vide le cache Symfony
	docker compose exec php php bin/console cache:clear

test: ## Exécute les tests
	docker compose exec php php bin/phpunit

db-create: ## Crée la base de données
	docker compose exec php php bin/console doctrine:database:create --if-not-exists

db-drop: ## Supprime la base de données
	docker compose exec php php bin/console doctrine:database:drop --force --if-exists

db-migrate: ## Exécute les migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

db-fixtures: ## Charge les fixtures
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction

db-reset: db-drop db-create db-migrate ## Réinitialise complètement la base de données

entity: ## Crée une nouvelle entité (usage: make entity NAME="Product")
	docker compose exec php php bin/console make:entity $(NAME)

controller: ## Crée un nouveau contrôleur (usage: make controller NAME="ProductController")
	docker compose exec php php bin/console make:controller $(NAME)

migration: ## Génère une nouvelle migration
	docker compose exec php php bin/console make:migration

install: build up composer db-create db-migrate ## Installation complète du projet
	@echo "✅ Installation terminée!"
	@echo "🌐 Application: http://localhost:8080"
	@echo "📊 PhpMyAdmin: http://localhost:8081"
	@echo "📧 MailDev: http://localhost:1080"

start: up ## Alias pour up

stop: down ## Alias pour down

ps: ## Liste tous les conteneurs
	docker compose ps

prune: ## Nettoie les volumes et images non utilisés
	docker system prune -af --volumes

compose-require:
	docker compose exec php composer require $(NAME)

stop-mysql:
	docker sudo service mysql stop
