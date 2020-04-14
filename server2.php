<?php
/* ini_set('default_socket_timeout', -1); */

require_once './vendor/autoload.php';

$sock = '/tmp/xdebug.sock';
$path = 'unix://' . $sock;

if (file_exists($sock)) {
    unlink($sock);
}

$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

$result = socket_bind($socket, $sock) or die("Could not bind to socket\n");

$result = socket_listen($socket, 3) or die("Could not set up socket listener\n");
$spawn = socket_accept($socket) or die("Could not accept incoming connection\n");
$input = socket_read($spawn, 1024) or die("Could not read input\n");
d($input);
$output = "run -i 0 \0";
socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");
sleep(1);
socket_close($spawn);
socket_close($socket);
