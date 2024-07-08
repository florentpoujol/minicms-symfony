#https://hub.docker.com/_/php/
FROM php:8.3-fpm

# install Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# install non default PHP extensions
RUN apt-get update && apt-get install -y \
    		libzip-dev \
    && docker-php-ext-install pdo_mysql zip