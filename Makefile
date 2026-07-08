SHELL := /bin/bash

DEV_COMPOSE := docker compose --env-file .env -f docker/development/docker-compose.yml
PROD_COMPOSE := docker compose --env-file .env.production -f docker/production/docker-compose.yml

.PHONY: install up down restart logs shell migrate fresh cache optimize clear test lint format prod-up prod-down prod-logs prod-shell

install:
	composer install
	npm install
	@if [ ! -f .env ]; then cp .env.example .env; fi
	php artisan key:generate --no-interaction

up:
	$(DEV_COMPOSE) up -d

down:
	$(DEV_COMPOSE) down

restart: down up

logs:
	$(DEV_COMPOSE) logs -f

shell:
	bash

migrate:
	php artisan migrate --no-interaction

fresh:
	php artisan migrate:fresh --no-interaction

cache:
	php artisan config:cache --no-interaction
	php artisan route:cache --no-interaction
	php artisan view:cache --no-interaction

optimize:
	php artisan optimize --no-interaction

clear:
	php artisan optimize:clear --no-interaction

test:
	php artisan test --compact

lint:
	vendor/bin/pint --dirty --format agent

format:
	vendor/bin/pint --dirty --format agent

prod-up:
	$(PROD_COMPOSE) up -d --build

prod-down:
	$(PROD_COMPOSE) down

prod-logs:
	$(PROD_COMPOSE) logs -f

prod-shell:
	$(PROD_COMPOSE) exec app sh
