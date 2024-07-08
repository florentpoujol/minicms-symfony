#https://hub.docker.com/_/php/
FROM php:8.3-fpm

# install Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# add the "install-php-extensions" tool
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# install non default PHP extensions
RUN install-php-extensions pdo_mysql zip mbstring
