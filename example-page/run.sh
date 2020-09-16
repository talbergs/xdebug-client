#!/bin/bash
docker network create xd

docker build -t xd-php --file $PWD/php.Dockerfile .

docker rm -f xd-php2
docker run --detach --rm --network xd --name xd-php2 \
    -v $PWD:/var/www/html \
    -v $PWD/xd2.ini:/usr/local/etc/php/conf.d/xd.ini \
    xd-php

docker rm -f xd-php
docker run --detach --rm --network xd --name xd-php \
    -v $PWD:/var/www/html \
    -v $PWD/xd.ini:/usr/local/etc/php/conf.d/xd.ini \
    xd-php

docker rm -f xd-caddy
docker run --detach --rm --network xd --name xd-caddy \
    -p 80:80 \
    -v $PWD/Caddyfile:/etc/caddy/Caddyfile \
    -v $PWD:/var/www/html \
    caddy
