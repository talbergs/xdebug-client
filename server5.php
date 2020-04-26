<?php

/* ini_set('default_socket_timeout', -1); */

require_once './vendor/autoload.php';

function newConn($sock) {
    $sock = '/tmp/' . $sock;
    $path = 'unix://' . $sock;

    if (file_exists($sock)) {
        unlink($sock);
    }

    return stream_socket_server($path, $errno, $errstr);
}

interface IO
{
}

interface Protocol
{
}

class Xdebug implements Protocol
{
}

class Obj
{
    public $container = array();
    public $activity = array();

    public function alloc() {
        return $this->container;
    }

    public function __construct() {
        $this->container = array(
            "one" => newConn('xdebug.sock'),
            "one2" => newConn('xdebug2.sock'),
        );
    }

    public function select(int $timeout) {
        $write = $read = $e = $this->alloc();
        $ln = stream_select($write, $read, $e, $timeout);
        $this->activity = $write;
    }

    public function bind(IO $io, Protocol $responder) {
        $sock = '/tmp/' . $sock;
        $path = 'unix://' . $sock;

        if (file_exists($sock)) {
            unlink($sock);
        }

        return stream_socket_server($path, $errno, $errstr);
    }
}

$obj = new Obj();

$obj->bindUnix('xdebug.sock');
$obj->open('xdebug.sock');

while (true) {
    d('*activity:' . count($obj->activity));
    d('*container:' . count($obj->container));

    foreach($obj->select(5) as $event) {
        switch ($event->source) {
        case Event::WS_CONNECT:
            break;
        case Event::WS_MESSAGE:
            break;
        case Event::HTTP_REQUEST:
            break;
        case Event::XDEBUG_CONNECT:
            break;
        case Event::XDEBUG_MESSAGE:
            break;
        }
    }

    $obj->respond();

    d(':activity:' . count($obj->activity));
    d(':container:' . count($obj->container));
    d('');

    sleep(1);
}
