#!/bin/bash

docker rm -f xdebug-web

docker build -t xdebug-web /home/ada/.zbx-box/build/xdebug-web

# this volume will be baked in
docker run -it --rm --name xdebug-web -v $PWD:/app \
    --network zbx-box \
    -p 8080:80 \
    -d \
    --volume $HOME/zabbix-dev:/www \
    xdebug-web

echo ---------------------------
echo ---------------------------
docker logs --follow xdebug-web
