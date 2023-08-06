FROM php:8.1-apache

RUN set -eux \
 && apt-get update -y \
 && apt-get upgrade -y \
 && apt-get install -y vim default-mysql-client git autoconf g++ libtool make libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg=/usr \
 && docker-php-ext-configure opcache --enable-opcache \
 && docker-php-ext-install opcache bcmath pdo_mysql gd exif zip \
 && pecl install xdebug  \
 && docker-php-ext-enable xdebug \
 && rm -rf /tmp/*

ARG PUID=1000
ARG PGID=1000

RUN echo "-> $PUID"
RUN echo "-> $PGID"

RUN groupmod -o -g $PGID www-data && \
    usermod -o -u $PUID -g www-data www-data && \
    usermod --shell /bin/bash www-data

# /var/www is www-data's home dir. use in npm and other.
RUN chown www-data:www-data /var/www
