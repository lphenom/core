.PHONY: install test lint lint-fix analyse all up down

DOCKER_RUN = docker compose run --rm php

up:
	docker compose up -d --build

down:
	docker compose down

install:
	docker compose run --rm composer install

test:
	$(DOCKER_RUN) php vendor/bin/phpunit --testdox

lint:
	$(DOCKER_RUN) php vendor/bin/php-cs-fixer check --diff --ansi --allow-risky=yes

lint-fix:
	$(DOCKER_RUN) php vendor/bin/php-cs-fixer fix --ansi --allow-risky=yes

analyse:
	$(DOCKER_RUN) php vendor/bin/phpstan analyse --ansi

phpstan: analyse

all: install lint analyse test

help:
	@echo "Available targets:"
	@echo "  install   — install composer dependencies (in Docker)"
	@echo "  test      — run PHPUnit tests (in Docker)"
	@echo "  lint      — check code style (php-cs-fixer, in Docker)"
	@echo "  lint-fix  — fix code style (in Docker)"
	@echo "  analyse   — run PHPStan static analysis (in Docker)"
	@echo "  phpstan   — alias for analyse"
	@echo "  all       — install + lint + analyse + test"
	@echo "  up        — build and start Docker environment"
	@echo "  down      — stop Docker environment"
