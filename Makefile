SHELL := /bin/bash
.PHONY: hi up down logs runls

hi:
	@echo "hello world"

up:
	docker-compose up -d

down:
	docker-compose stop
	docker-compose down -v

logs:
	docker-compose logs -f

enter_php:
	docker exec -it php-coreapi bash

install:
	docker exec  php-coreapi composer install
	docker exec  php-coreapi php bin/console doctrine:database:create
	docker exec  php-coreapi php bin/console doctrine:migrations:migrate

run-test:
	docker exec  php-coreapi php bin/phpunit tests/

make-fixtures:
	docker exec php-coreapi php bin/console doctrine:fixtures:load --no-interaction


