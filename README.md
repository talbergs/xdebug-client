# Step Debugging client written in php.

Rarely you need a step debugger, but once you do need it - you should be able
to just "spin it up".

```sh
docker run -d -p 8999:80 --network=infrastructure -v app:/app talbergs/xdebug-client
```

Now, web interface can be used configure things further.

Or default configuration can be passed into via ton of environment variables:
```sh
docker run -d -p 8080:8080 --env LISTEN_SOCK=/var/run/app.sock talbergs/xdebug-client
```
