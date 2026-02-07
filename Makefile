.PHONY: up down build exec migrate fresh

up:
	docker compose up

down:
	docker compose down

build:
	docker compose build --no-cache

exec:
	docker compose exec -it php /bin/bash

migrate:
	docker compose exec php php artisan migrate

fresh:
	docker compose exec php php artisan migrate:fresh --seed
