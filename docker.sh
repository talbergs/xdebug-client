#!/bin/bash
docker network create xd

docker rm -f xd-xd
docker build -t xd-xd --file $PWD/Dockerfile .

# this volume will be baked in
docker run -it --detach --rm --network xd --name xd-xd \
    -v $PWD:/app \
    -p 8080:8080 \
    xd-xd /app/src/app.php

# zbx,follow xd-xd
