<?php
// Xdebug server.
// Xdebug will connect to this server.
$listen = 9000;

$path = 'unix:///tmp/xdebug.sock';
$sock = stream_socket_client($path, $errno, $errstr);
var_dump($errstr);

/* fwrite($sock, 'SOME COMMAND'."\r\n"); */

echo fread($sock, 4096)."\n";

fclose($sock);
