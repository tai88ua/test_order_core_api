SHELL := /bin/bash
.PHONY: hi up down logs runls


# обычно выполняеться первая команда
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
