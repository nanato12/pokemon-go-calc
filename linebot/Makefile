.PHONY: install lint test serve build up down

install:
	composer install

lint:
	vendor/bin/phpstan analyse

test:
	vendor/bin/phpunit

serve:
	php -S localhost:8081 -t public

build:
	docker build -t pokemon-go-calc-linebot .

up:
	docker compose up -d

down:
	docker compose down
