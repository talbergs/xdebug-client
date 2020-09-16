FROM php:7.4.10-fpm-buster

RUN apt-get update

RUN apt-get install autoconf
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
