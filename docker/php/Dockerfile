#イメージのベースを指定
FROM php:8.0-fpm

#composerのインストール
COPY --from=composer:2.0.3 /usr/bin/composer /usr/bin/composer

#パッケージ管理ツールapt-getの更新と必要パッケージのインストール
RUN apt-get update \
  && apt-get install -y \
  git \
  zip \
  unzip \
  && docker-php-ext-install pdo_mysql bcmath

WORKDIR /var/www/html/
