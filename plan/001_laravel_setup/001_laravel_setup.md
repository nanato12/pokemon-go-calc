# Laravel 初期構築プラン

## 概要

`server/` ディレクトリに Laravel アプリケーションを構築する。Docker設定はルートに配置。

## 前提条件

- PHP 8.3
- Docker で開発環境構築（php artisan serve 使用）
- 本番: Xserver（共有サーバー）
- SEO重視 → Laravel SSR（Blade）が主軸

## 構築後のディレクトリ構成

```text
pokemon-go-calc/
├── api/                    # 既存（Python API）
├── linebot/                # 既存（PHP LINE Bot）
├── docker/                 # Docker設定
│   ├── php/
│   │   └── Dockerfile
│   └── mysql/
│       └── data/           # MySQLデータ（gitignore）
├── docker-compose.yml
├── Makefile
├── server/                 # Laravel アプリケーション
│   ├── app/
│   ├── resources/
│   ├── public/
│   ├── routes/
│   ├── database/
│   └── ...
└── ...
```

## 実装ファイル

### docker/php/Dockerfile

```dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    bcmath \
    gd \
    mbstring \
    xml \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
```

### docker-compose.yml

```yaml
services:
  php:
    build: ./docker/php
    ports:
      - "38000:8000"
    volumes:
      - ./server:/var/www/html
    working_dir: /var/www/html
    depends_on:
      - mysql
    command: >
      sh -c '
        test -f .env || cp .env.example .env &&
        composer install &&
        php artisan key:generate --force &&
        php artisan migrate --force &&
        php artisan serve --host 0.0.0.0
      '

  mysql:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: pokemon_go_calc
      MYSQL_USER: laravel
      MYSQL_PASSWORD: secret
      TZ: Asia/Tokyo
    ports:
      - "33060:3306"
    volumes:
      - ./docker/mysql/data:/var/lib/mysql
```

### Makefile

```makefile
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
```

## インストール手順

```bash
# 1. ビルド
docker compose build

# 2. PHPコンテナに入ってLaravelインストール
docker compose run --rm php bash
composer create-project laravel/laravel .
exit

# 3. 起動（自動で .env コピー → composer install → migrate → serve）
make up

# 4. アクセス確認
open http://localhost:38000
```

## 初期設定

### server/.env

```env
APP_NAME="Pokemon GO Calc"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:38000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=pokemon_go_calc
DB_USERNAME=laravel
DB_PASSWORD=secret
```

## ポート

| サービス | ホスト | コンテナ内 |
|----------|--------|------------|
| PHP      | 38000  | 8000       |
| MySQL    | 33060  | 3306       |

## 検証項目

- [ ] `make up` で起動
- [ ] `http://localhost:38000` でLaravel表示
- [ ] `make exec` → `php artisan migrate` でDB接続確認

## 備考

- Nginx不使用（開発は artisan serve、本番は Xserver Apache）
- `docker compose up` で自動セットアップ＆サーバー起動
- linebot/ のDomain層は将来的に server/app/Domain/ に移植可能
