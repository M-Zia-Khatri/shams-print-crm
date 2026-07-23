SHELL := /bin/bash

DEV_COMPOSE := docker compose --env-file .env -f docker/development/docker-compose.yml
PROD_COMPOSE := docker compose --env-file .env.production -f docker/production/docker-compose.yml

VPS_HOST ?= 162.35.163.254
VPS_USER ?= root
VPS_PATH ?= /srv/shams-print-crm

.PHONY: help install up down restart logs shell migrate fresh cache optimize clear test lint format \
	artisan tinker seed fresh-seed queue-work queue-restart pest test-unit test-feature test-coverage \
	pint-check assets npm-dev key-generate storage-link composer-update npm-update \
	prod-up prod-down prod-restart prod-build prod-logs prod-logs-app prod-logs-queue prod-logs-scheduler \
	prod-shell prod-exec prod-artisan prod-tinker prod-migrate prod-seed prod-cache prod-clear \
	prod-queue-restart prod-composer prod-ps db-shell redis-cli backup-db restore-db \
	volumes-ls tail-log health ssh deploy

.DEFAULT_GOAL := help

help: ## Show this help
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install composer + npm deps, set up .env and app key
	composer install
	npm install
	@if [ ! -f .env ]; then cp .env.example .env; fi
	php artisan key:generate --no-interaction

up: ## Start dev containers
	$(DEV_COMPOSE) up -d

down: ## Stop dev containers
	$(DEV_COMPOSE) down

restart: down up ## Restart dev containers

logs: ## Tail dev container logs
	$(DEV_COMPOSE) logs -f

shell: ## Open a local bash shell
	bash

migrate: ## Run migrations
	php artisan migrate --no-interaction

fresh: ## Drop all tables and re-migrate
	php artisan migrate:fresh --no-interaction

cache: ## Cache config/route/view
	php artisan config:cache --no-interaction
	php artisan route:cache --no-interaction
	php artisan view:cache --no-interaction

optimize: ## Run artisan optimize
	php artisan optimize --no-interaction

clear: ## Clear all caches
	php artisan optimize:clear --no-interaction

test: ## Run test suite
	php artisan test --compact

lint: ## Lint dirty files with Pint
	vendor/bin/pint --dirty --format agent

format: ## Alias for lint
	vendor/bin/pint --dirty --format agent

# --- extended local dev ---

artisan: ## Run an artisan command, e.g. make artisan migrate:status
	php artisan $(filter-out $@,$(MAKECMDGOALS))

tinker: ## Open artisan tinker
	php artisan tinker

seed: ## Run database seeders
	php artisan db:seed --no-interaction

fresh-seed: ## Migrate fresh + seed
	php artisan migrate:fresh --seed --no-interaction

queue-work: ## Run the queue worker
	php artisan queue:work --tries=1

queue-restart: ## Restart queue workers
	php artisan queue:restart

pest: ## Run Pest test suite directly
	vendor/bin/pest

test-unit: ## Run unit tests only
	php artisan test --testsuite=Unit --compact

test-feature: ## Run feature tests only
	php artisan test --testsuite=Feature --compact

test-coverage: ## Run tests with coverage report
	php artisan test --coverage --min=0

pint-check: ## Check formatting without fixing (CI mode)
	vendor/bin/pint --test

assets: ## Build frontend assets
	npm run build

npm-dev: ## Run vite dev server
	npm run dev

key-generate: ## Generate app key
	php artisan key:generate --no-interaction

storage-link: ## Create storage symlink
	php artisan storage:link

composer-update: ## Update composer dependencies
	composer update

npm-update: ## Update npm dependencies
	npm update

# --- production ---

prod-up: ## Build and start prod containers
	$(PROD_COMPOSE) up -d --build

prod-down: ## Stop prod containers
	$(PROD_COMPOSE) down

prod-restart: ## Restart prod containers
	$(PROD_COMPOSE) restart

prod-build: ## Rebuild prod images with no cache
	$(PROD_COMPOSE) build --no-cache

prod-logs: ## Tail all prod container logs
	$(PROD_COMPOSE) logs -f

prod-logs-app: ## Tail prod app container logs
	$(PROD_COMPOSE) logs -f app

prod-logs-queue: ## Tail prod queue container logs
	$(PROD_COMPOSE) logs -f queue

prod-logs-scheduler: ## Tail prod scheduler container logs
	$(PROD_COMPOSE) logs -f scheduler

prod-shell: ## Shell into prod app container
	$(PROD_COMPOSE) exec app sh

prod-exec: ## Exec an arbitrary command in prod app container
	$(PROD_COMPOSE) exec app $(filter-out $@,$(MAKECMDGOALS))

prod-artisan: ## Run an artisan command in prod, e.g. make prod-artisan migrate:status
	$(PROD_COMPOSE) exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

prod-tinker: ## Open artisan tinker in prod
	$(PROD_COMPOSE) exec app php artisan tinker

prod-migrate: ## Run migrations in prod
	$(PROD_COMPOSE) exec app php artisan migrate --force

prod-seed: ## Run seeders in prod
	$(PROD_COMPOSE) exec app php artisan db:seed --force

prod-cache: ## Cache config/route/view in the running prod container (safe: env vars are runtime-injected, never build-time)
	$(PROD_COMPOSE) exec app php artisan config:cache
	$(PROD_COMPOSE) exec app php artisan route:cache
	$(PROD_COMPOSE) exec app php artisan view:cache

prod-clear: ## Clear all caches in prod container
	$(PROD_COMPOSE) exec app php artisan optimize:clear

prod-queue-restart: ## Restart prod queue workers
	$(PROD_COMPOSE) exec app php artisan queue:restart

prod-composer: ## Run composer inside prod app container
	$(PROD_COMPOSE) exec app composer $(filter-out $@,$(MAKECMDGOALS))

prod-ps: ## Show prod container status
	$(PROD_COMPOSE) ps

db-shell: ## Open mysql shell in prod db container
	$(PROD_COMPOSE) exec db mysql -u$$DB_USERNAME -p$$DB_PASSWORD $$DB_DATABASE

redis-cli: ## Open redis-cli in prod redis container
	$(PROD_COMPOSE) exec redis redis-cli

backup-db: ## Dump prod database to backups/db_<timestamp>.sql
	mkdir -p backups
	$(PROD_COMPOSE) exec db sh -c 'mysqldump -u$$DB_USERNAME -p$$DB_PASSWORD $$DB_DATABASE' > backups/db_$(shell date +%Y%m%d_%H%M%S).sql

restore-db: ## Restore prod database from FILE=path.sql
	$(PROD_COMPOSE) exec -T db sh -c 'mysql -u$$DB_USERNAME -p$$DB_PASSWORD $$DB_DATABASE' < $(FILE)

volumes-ls: ## List production_ prefixed docker volumes
	docker volume ls | grep production_

tail-log: ## Tail laravel.log inside prod app container
	$(PROD_COMPOSE) exec app tail -f storage/logs/laravel.log

health: ## Curl the prod health check endpoint
	curl -sSf https://crm.ziakhatri.site/up

# --- remote / vps ---

ssh: ## SSH into the VPS
	ssh $(VPS_USER)@$(VPS_HOST)

deploy: ## SSH into VPS, git pull, and rebuild via make prod-up
	ssh $(VPS_USER)@$(VPS_HOST) "cd $(VPS_PATH) && git pull && make prod-up"

%:
	@: