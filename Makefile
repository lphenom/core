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

all: install lint analyse test

up:
	docker compose up -d

down:
	docker compose down


