.PHONY: install test lint lint-fix analyse all up down

PHP  = php
COMP = composer

install:
	$(COMP) install

test:
	$(PHP) vendor/bin/phpunit --testdox

lint:
	$(PHP) vendor/bin/php-cs-fixer check --diff --ansi --allow-risky=yes

lint-fix:
	$(PHP) vendor/bin/php-cs-fixer fix --ansi --allow-risky=yes

analyse:
	$(PHP) vendor/bin/phpstan analyse --ansi

phpstan: analyse

all: install lint analyse test

help:
	@echo "Available targets:"
	@echo "  install   — install composer dependencies"
	@echo "  test      — run PHPUnit tests"
	@echo "  lint      — check code style (php-cs-fixer)"
	@echo "  lint-fix  — fix code style"
	@echo "  analyse   — run PHPStan static analysis"
	@echo "  phpstan   — alias for analyse"
	@echo "  all       — install + lint + analyse + test"
	@echo "  up        — start Docker environment"
	@echo "  down      — stop Docker environment"

up:
	docker compose up -d

down:
	docker compose down


