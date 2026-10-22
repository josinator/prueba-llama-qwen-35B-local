# ============================================
# Symfony API - Book Management Project
# Stack: PHP 8.3 + MariaDB 10 + Nginx
# ============================================

.PHONY: help up down restart build clean logs composer vendor test db-migrate db-reset db-dump dev shell symfony php assets init-project

.DEFAULT_GOAL := help

PHP_SERVICE = app

# --- Help ---
help: ## Muestra esta ayuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-22s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "━━━ Controles Docker ━━━━━━━━━━━━━━━━━━━━━━━"
	@echo "  make up              - Levanta todos los contenedores"
	@echo "  make down            - Detiene todos los contenedores"
	@echo "  make restart         - Reinicia todo"
	@echo "  make build           - Reconstruye imagenes Docker"
	@echo "  make clean           - Elimina contenedores y volumes"
	@echo "  make logs            - Logs de todos los servicios"
	@echo ""
	@echo "━━━ Desarrollo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
	@echo "  make dev             - Inicializa (build + up)"
	@echo "  make shell           - Shell dentro del contenedor PHP"
	@echo "  make symfony [cmd]   - Comandos Symfony (ej: make symfony list:routes)"
	@echo "  make composer [cmd]  - Composer (ej: make composer install)"
	@echo "  make vendor          - Restaura dependencias (composer install)"
	@echo "  make php             - Ejecuta PHP (ej: make php --version)"
	@echo ""
	@echo "━━━ Base de datos ━━━━━━━━━━━━━━━━━━━━━━━━━━"
	@echo "  make db-migrate      - Aplica migraciones Doctrine"
	@echo "  make db-reset        - Borrar y recrear esquema DB"
	@echo ""
	@echo "━━━ API Endpoints ━━━━━━━━━━━━━━━━━━━━━━━━━━"
	@echo "  GET    http://localhost:8080/api/books      - Listar libros"
	@echo "  GET    http://localhost:8080/api/books/{id} - Mostrar libro"
	@echo "  POST   http://localhost:8080/api/books      - Crear libro"
	@echo "  PUT    http://localhost:8080/api/books/{id} - Actualizar libro"

# --- Docker Compose ---
up: ## Levanta todos los contenedores en background
	docker compose up -d --wait

down: ## Detiene todos los contenedores
	docker compose down

restart: down up ## Reinicia todos los contenedores

build: build-app ## Reconstruye imagenes Docker
	docker compose build --no-cache

build-app:
	docker compose build --no-cache app

clean: down ## Elimina contenedores, volumes y redes
	docker compose down -v --remove-orphans

logs: ## Muestra los logs de todos los servicios
	docker compose logs -f

# --- Development ---
dev: build up ## Inicializa y levanta el proyecto completo

shell: ## Entra en el shell del contenedor PHP
	docker compose exec $(PHP_SERVICE) sh

php: ## Ejecuta PHP dentro del contenedor
	@if [ "$(word 2, $(MAKECMDGOALS))" ]; then \
		docker compose exec $(PHP_SERVICE) php $(word 2, $(MAKECMDGOALS)) $(subst ,,$(wordlist 3,99,$(MAKECMDGOALS))); \
	else \
		docker compose exec $(PHP_SERVICE) php; \
	fi

symfony: ## Ejecuta comandos de Symfony Console
	@if [ "$(word 2, $(MAKECMDGOALS))" ]; then \
		docker compose exec $(PHP_SERVICE) bin/console $(word 2, $(MAKECMDGOALS)) $(subst ,,$(wordlist 3,99,$(MAKECMDGOALS))); \
	else \
		docker compose exec $(PHP_SERVICE) bin/console list; \
	fi

composer: ## Ejecuta Composer
	@if [ -n "$(word 2, $(MAKECMDGOALS))" ]; then \
		docker compose exec $(PHP_SERVICE) composer $(wordlist 2,99,$(MAKECMDGOALS)); \
	else \
		docker compose exec $(PHP_SERVICE) composer; \
	fi

vendor: docker-compose-exec-composer-install ## Actualiza las dependencias de Composer

docker-compose-exec-composer-install:
	docker compose exec $(PHP_SERVICE) sh -c "composer install && composer dump-autoload --optimize"

# --- Database ---
db-migrate: ## Ejecuta las migraciones de Doctrine
	@echo "Aplicando migraciones..."
	docker compose exec $(PHP_SERVICE) bin/console doctrine:migrations:migrate --no-interaction

db-reset: ## Reinicia la base de datos (DROP + MIGRATE)
	@read -p "⚠️ Borrar TODOS los datos? (s/S): " ans && [ "$$ans" = "s" ] || exit 1
	@echo "Reiniciando base de datos..."
	docker compose restart database
	@sleep 5
	docker compose exec $(PHP_SERVICE) bin/console doctrine:schema:drop --force --no-interaction
	docker compose exec $(PHP_SERVICE) bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

db-dump: ## Exporta la base de datos a archivo SQL
	@mkdir -p ./dumps
	docker compose exec database mariadb-dump -u symfony -psymfony books > dumps/backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "Backup guardado en ./dumps/"

# --- Test ---
test: ## Ejecuta las pruebas unitarias
	docker compose exec $(PHP_SERVICE) php bin/phpunit --configuration phpunit.xml.dist

# --- Code Quality ---
phpstan: ## Ejecutar PHPStan (análisis estático)
	docker compose exec $(PHP_SERVICE) sh -c "php ./vendor/bin/phpstan analyse --ansi"

cs-fixer: ## Ejecutar PHP-CS-Fixer (corrige estilo)
	docker compose exec $(PHP_SERVICE) sh -c "php ./vendor/bin/php-cs-fixer fix --ansi"

cs-fixer-dry: ## Ejecutar PHP-CS-Fixer (solo verifica, sin corregir)
	docker compose exec $(PHP_SERVICE) sh -c "php ./vendor/bin/php-cs-fixer fix --dry-run --diff --ansi"

quality: ## Ejecutar PHPStan + PHP-CS-Fixer (dry-run)
	docker compose exec $(PHP_SERVICE) sh -c "php ./vendor/bin/php-cs-fixer fix --dry-run --diff --ansi && php ./vendor/bin/phpstan analyse --ansi"
