PROJECT_NAME := web
PRODUCTION_PUBLISH_PATH := /home/nanato12/line-bot.jp/public_html/go-pilot.line-bot.jp
STAGING_PUBLISH_PATH := /home/nanato12/line-bot.jp/public_html/go-pilot-stg.line-bot.jp

.PHONY: up down build exec migrate fresh clear server-build deploy deploy-stg

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

clear:
	cd ${PROJECT_NAME} && \
		php artisan cache:clear && \
		php artisan config:clear && \
		php artisan route:clear && \
		php artisan view:clear && \
		php artisan clear-compiled && \
		php artisan optimize && \
		php artisan config:cache

server-build:
	cd ${PROJECT_NAME} && \
		test -f .env || cp .env.example .env && \
		composer install && \
		php artisan storage:link --force && \
		php artisan key:generate --force && \
		php artisan migrate --force
	make clear

deploy: server-build
	cp -rf ${PROJECT_NAME}/public/. ${PRODUCTION_PUBLISH_PATH}
	@echo "\n\n\n🎉🎉🎉🎉 Production deploy completed 🎉🎉🎉🎉\n\n\n"

deploy-stg: server-build
	cp -rf ${PROJECT_NAME}/public/. ${STAGING_PUBLISH_PATH}
	@echo "\n\n\n🎉🎉🎉🎉 Staging deploy completed 🎉🎉🎉🎉\n\n\n"
