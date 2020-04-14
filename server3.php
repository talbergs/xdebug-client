<?php

require_once './vendor/autoload.php';

function selectClientRequest($sock) {
    $conn = stream_socket_accept($sock, 0);

    $len = '';
    $b = '';
    while (true) {
        $b = fgetc($conn);
        if ($b === "\x00") {
            break;
        }
        $len .= $b;
    }
    if ($len === '') {
        echo 'Incorrect request. Expecting header.';die;
    } else if ($len == 0) {
        echo 'Empty request.';die;
    }

    $res = stream_get_line($conn, (int) $len, "\x00");
    if ($res === false) {
        echo 'Php broke.';die;
    } else if ($res === '') {
        echo 'Xdebug broke.';die;
    }

    d($res, __FUNCTION__);
}

function selectServerRequest($sock) {
    $conn = stream_socket_accept($sock, 0);

    $len = '';
    $b = '';
    while (true) {
        $b = fgetc($conn);
        if ($b === "\x00") {
            break;
        }
        $len .= $b;
    }
    if ($len === '') {
        echo 'Incorrect request. Expecting header.';die;
    } else if ($len == 0) {
        echo 'Empty request.';die;
    }

    $res = stream_get_line($conn, (int) $len, "\x00");
    if ($res === false) {
        echo 'Php broke.';die;
    } else if ($res === '') {
        echo 'Xdebug broke.';die;
    }

    d($res, __FUNCTION__);
    return $conn;
}

function newConn($sock) {
    $sock = '/tmp/' . $sock;
    $path = 'unix://' . $sock;

    if (file_exists($sock)) {
        unlink($sock);
    }

    return stream_socket_server($path, $errno, $errstr);
}

$sock = newConn('xdebug.sock');
$sock2 = newConn('xdebug2.sock');

$xdebug_session = null;
while (true) {
    $read = $except = $write = array_filter([
        'xdebug' => $sock, 
        'web' => $sock2,
        'xdebug_session' => $xdebug_session
    ]);

    stream_select($write, $read, $except, 5);

    if (array_key_exists('xdebug', $write)) {
        $xdebug_session = selectServerRequest($write['xdebug']);
    }

    if (array_key_exists('xdebug_session', $write)) {
        $xdebug_session = selectServerRequest($write['xdebug_session']);
    }

    if (array_key_exists('web', $write)) {
        selectClientRequest($write['web']);
    }
}
sleep(3);
fclose($conn);
