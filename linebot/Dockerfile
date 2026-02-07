FROM php:8.1.32-apache

# Apache mod_rewrite を有効化
RUN a2enmod rewrite

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 必要な拡張をインストール
RUN docker-php-ext-install opcache

# Composer をインストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www/html

# 生成されたクライアントをコピー (composer.json より先に)
COPY generated/ generated/

# Composer ファイルをコピー
COPY composer.json composer.lock ./

# 依存関係をインストール (本番用)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# アプリケーションコードをコピー
COPY src/ src/
COPY public/ public/

# Apache の DocumentRoot を public に設定
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# .htaccess を有効にするための設定
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/public.conf && \
    a2enconf public

# ポート
EXPOSE 80

# ヘルスチェック
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1
