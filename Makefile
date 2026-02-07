.PHONY: up down build exec migrate fresh deploy deploy-stg

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

deploy:
	cd server && composer install --no-dev --optimize-autoloader
	cd server && php artisan config:cache
	cd server && php artisan route:cache
	cd server && php artisan view:cache

deploy-stg:
	cd server && composer install
	cd server && php artisan config:clear
	cd server && php artisan route:clear
	cd server && php artisan view:clear
