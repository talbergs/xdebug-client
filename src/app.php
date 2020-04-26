#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

if ($argc == 1) {
    echo 'Usage' . PHP_EOL;
    echo '--http-port 8080' . PHP_EOL;
    echo '--xdebug-socket /tmp/xdebug.sock' . PHP_EOL;
    exit;
} else {
    echo 'Listening for web connections on: 0.0.0.0:8080' . PHP_EOL;
    echo 'Listening for xdebug connections on /tmp/xdebug.sock' . PHP_EOL;
}

pcntl_async_signals(true);
pcntl_signal(SIGINT, function () {
    file_exists('/tmp/xdebug.sock') && unlink('/tmp/xdebug.sock');
    exit;
});

pcntl_signal(SIGTERM, function () {
    file_exists('/tmp/xdebug.sock') && unlink('/tmp/xdebug.sock');
    exit;
});

pcntl_signal(SIGHUP, function () {
    file_exists('/tmp/xdebug.sock') && unlink('/tmp/xdebug.sock');
    exit;
});

pcntl_signal(SIGQUIT, function () {
    file_exists('/tmp/xdebug.sock') && unlink('/tmp/xdebug.sock');
    exit;
});

// Used for testing.
// The name of this signal is derived from "illegal instruction".
// It usually means your program is trying to execute garbage or a privileged instruction.
pcntl_signal(SIGILL, function () {
    trigger_error('Simulated fatal error for testing purposes.', E_USER_ERROR);
});


register_shutdown_function(function () {
    if (error_get_last()) {
        file_exists('/tmp/xdebug.sock') && unlink('/tmp/xdebug.sock');
    }
});


    /* $host = '0.0.0.0'; */
    /* $port = 8080; */
    /* $main_socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Cannot create socket.\n"); */
    /* socket_bind($main_socket, $host, $port) or die("Could not bind to socket $host : $port.\n"); */
    /* socket_listen($main_socket, 5) or die("Could not set up socket listener\n"); */
/* $web_conn = $main_socket; */
$web_conn = stream_socket_server("tcp://0.0.0.0:8080", $errno, $errstr);
$web_session = null;

function newConn($sock) {
    $sock = '/tmp/' . $sock;
    $path = 'unix://' . $sock;

    if (file_exists($sock)) {
        unlink($sock);
    }

    return stream_socket_server($path, $errno, $errstr);
}

$xdebug_conn = newConn('xdebug.sock');
$xdebug_session = null;

// Used for tests - signal is sent when application is ready to accept connections.
posix_kill(posix_getppid(), SIGALRM);

while (true) {

    $read = $except = $write = array_filter(compact(
        'web_conn',
        'web_session',
        'xdebug_conn',
        'xdebug_session',
    ));

    // @suppress PHP Warning:  stream_select(): unable to select [4]:
    // Interrupted system call
    @stream_select($write, $read, $except, 5, 5000000);
    if (!array_keys($write)) {
        continue;
    }
    /* usleep(1000); */

    if (array_key_exists('xdebug_conn', $write)) {
        $xdebug_session = stream_socket_accept($write['xdebug_conn'], 0);
        echo "Xdebug just connected: " . stream_socket_get_name($xdebug_session, false) . PHP_EOL;
        /* $xdebug_conn = null; */
    }

    if (array_key_exists('web_conn', $write)) {
        $web_session = stream_socket_accept($write['web_conn']);
        $response = new HTTPResponse();
        $request = HTTPRequest::fromStream($web_session);

        $response->setStatusCode(200);

        if ($request->url === '/js.js') {
            $response->setBody(file_get_contents('./src/js.js'));
            fwrite($web_session, ''.$response);
            fclose($web_session);
        } else if ($request->isWebSocketHandshake()) {
            $response->setStatusCode(101);
            $response->setHeader('upgrade', 'websocket');
            $response->setHeader('connection', 'upgrade');
            $key = $request->getHeader('sec-websocket-key');
            $acc = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
            $acc = base64_encode(sha1($acc, true));
            $response->setBody("\r\n");
            $response->setHeader('sec-websocket-accept', $acc);
            fwrite($web_session, (string) $response);
            $ws_conn = $web_session;
            $web_session = null;
        } else if ($request->url === '/favicon.ico') {
            $response->setBody('https://github.com/rpsthecoder/square-loading-favicon');
            fwrite($web_session, ''.$response);
            fclose($web_session);
        } else {
            $response->setBody('<script src="js.js"></script>');
            fwrite($web_session, ''.$response);
            fclose($web_session);
        }

        $web_session = null;
    }

    if (array_key_exists('xdebug_session', $write)) {
        $req = XdebugRequest::buffer($xdebug_session);
        d('XDEBUG.SENDS: ', $req);
    }
}
