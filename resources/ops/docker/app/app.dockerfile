FROM php:8.1.7-fpm-alpine AS base

RUN apk add --update zlib-dev libpng-dev libzip-dev $PHPIZE_DEPS
RUN apk add git

RUN docker-php-ext-install exif
RUN docker-php-ext-install gd
RUN docker-php-ext-install zip
RUN docker-php-ext-install sockets
#RUN docker-php-ext-install pdo_mysql
RUN pecl install apcu
RUN docker-php-ext-enable apcu
RUN docker-php-ext-install pcntl

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev
RUN docker-php-ext-install pdo pdo_pgsql

FROM base AS dev

COPY /composer.json composer.json
COPY /composer.lock composer.lock
COPY /app app
COPY /bootstrap bootstrap
COPY /config config
COPY /artisan artisan

FROM base AS build-fpm

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY /artisan artisan

COPY /composer.json composer.json

COPY /bootstrap bootstrap
COPY /app app
COPY /config config
COPY /routes routes

COPY . /var/www/html

RUN composer install

RUN composer dump-autoload -o

FROM node:15.5-alpine AS assets-build

WORKDIR /var/www/html

COPY . /var/www/html/

RUN npm ci
RUN npm run build

FROM build-fpm AS fpm

COPY --from=build-fpm /var/www/html /var/www/html
RUN true
COPY --from=assets-build /var/www/html/public/build /var/www/html/public/build