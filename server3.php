<?php

require_once './vendor/autoload.php';

function newConn($sock) {
    $sock = '/tmp/' . $sock;
    $path = 'unix://' . $sock;

    if (file_exists($sock)) {
        unlink($sock);
    }

    return stream_socket_server($path, $errno, $errstr);
}

$xdebug_conn = newConn('xdebug.sock');
echo "Listening for xdebug connections on: " . stream_socket_get_name($xdebug_conn, false) . PHP_EOL;
$xdebug_session = null;

$web_conn = stream_socket_server("tcp://0.0.0.0:8080", $errno, $errstr);
echo "Listening for web connections on: " . stream_socket_get_name($web_conn, false) . PHP_EOL;
$web_session = null;

$ws_conn = null;

while (true) {
    sleep(0.2);

    $read = $except = $write = array_filter(compact(
        'xdebug_conn',
        'xdebug_session',
        'web_conn',
        'web_session',
        'ws_conn',
    ));
    /* d($write); */
    stream_select($write, $read, $except, 5);

    if (array_key_exists('xdebug_conn', $write)) {
        $xdebug_session = stream_socket_accept($write['xdebug_conn'], 0);
        echo "Xdebug just connected: " . stream_socket_get_name($xdebug_session, false) . PHP_EOL;
        fwrite($ws_conn, WSFrameFragment::encode('hello, Xdebug is connected.'));
    }

    if (array_key_exists('web_conn', $write)) {
        $web_session = stream_socket_accept($write['web_conn']);
        echo "Activity on: " . stream_socket_get_name($write['web_conn'], false) . PHP_EOL;
        $response = new HTTPResponse();
        $request = HTTPRequest::fromStream($web_session);

        if ($request->url === '/js.js') {
            $response->setStatusCode(200);
            // TODO: stream_to_stream_copy_contents
            $response->setBody(file_get_contents('js.js'));
            fwrite($web_session, $response);
            fclose($web_session);
            $web_session = null;
        } else if ($request->url === '/favicon.ico') {
            $response->setStatusCode(404);
            $response->setBody('');
            fwrite($web_session, $response);
            fclose($web_session);
            $web_session = null;
        } else if ($request->isWebSocketHandshake()) {
            $response->setStatusCode(101);
            $response->setHeader('upgrade', 'websocket');
            $response->setBody("\r\n");
            $response->setHeader('connection', 'upgrade');
            $key = $request->getHeader('sec-websocket-key');
            $acc = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
            $acc = base64_encode(sha1($acc, true));
            $response->setHeader('sec-websocket-accept', $acc);
            fwrite($web_session, $response);
            $ws_conn = $web_session;
            $web_session = null;
        } else {
            $response->setStatusCode(200);
            $response->setBody('<script src="js.js"></script>');
            fwrite($web_session, $response);
            fclose($web_session);
            $web_session = null;
        }
    }

    if (array_key_exists('ws_conn', $write)) {
        $frame = WSFrameFragment::fromStream($ws_conn);
        echo "  ws_conn $frame";
        $xdebug_command = "$frame\0";
        fwrite($xdebug_session, $xdebug_command);
    }

    if (array_key_exists('xdebug_session', $write)) {
        $req = XdebugRequest::buffer($xdebug_session);
        d('XDEBUG.SENDS: '.$req);
    }
}
