#https://hub.docker.com/_/php/
FROM php:8.3-fpm

# install compose
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# install non default PHP extensions
RUN docker-php-ext-install pdo_mysql