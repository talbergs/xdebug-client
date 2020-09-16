FROM php:7.4-cli

RUN docker-php-ext-install sockets

RUN apt-get update
