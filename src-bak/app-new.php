#!/usr/bin/env php
<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

use Acme\Connection\ConnectionHub;
use Acme\Connection\ConnectionInet;
use Acme\Connection\ConnectionUnix;
use Acme\Connection\ConnectionInterface;
use Acme\Connection\ConnectionXdebug;
use Acme\Events\SwitchConnectionEvent;
use Acme\App;
use Ds\Queue;


$hub = new ConnectionHub();

$web = ConnectionInet::new('web', 8080);
$xdb = ConnectionUnix::new('xdb', '/tmp/xdebug.sock');
$rpc = ConnectionUnix::new('rpc', '/tmp/rpc.sock');

$hub->add($web);
$hub->add($xdb);
$hub->add($rpc);


$event_queue = new Queue();
$app = new App($event_queue);

while (true) {
    echo 'tick-'.time().PHP_EOL;
    /* d($hub); */

    /** @var ConnectionInterface $connection */
    foreach ($hub->selectRead(150) as $connection) {
        if ($connection->isLive()) {
            if ($connection->hasClient()) {
                if ($connection->getName() === 'web') {
                    $app->onHTTPRequest($connection);
                } else if ($connection->getName() === 'xdb-session') {
                    $got = $connection->read();
                    d($got, 'SESSION');
                } else if ($connection->getName() === 'xdb') {
                    $event_queue->push(new SwitchConnectionEvent($connection, ConnectionXdebug::fromUnix($connection)));
                } else if ($connection->getName() === 'rpc') {
                    $got = $connection->read();
                    list($a, $m) = explode(':', $got);
                    $m = trim($m);
                    d("RPC: [$got] <<<<<<");
                    switch ($a) {
                    case 'dump hub': d($hub);break;
                    case 'x':
                        foreach ($hub->connectionsByName('xdb-session') as $conn) {
                            d($conn);
                            $conn->write($m);
                        }
                        break;
                    case 'ws':
                        foreach ($hub->connectionsByName('ws') as $conn) {
                            $conn->write($m);
                        }
                        break;
                    default: d("!! UNKNOWN ${got} RPC COMMAND !!");
                    }
                    $hub->drop($connection);
                } else if ($connection->getName() === 'ws') {
                    $got = $connection->read();
                    d($got);
                } else {
                    throw new RuntimeException('What?!');
                }
                /* $res = $connection->read(); */
                /* $event_queue->push($res); */
            } else {
                $hub->drop($connection);
                /* $event_queue->push("Client left."); */
            }
        } else {
            $hub->add($connection->accept());
        }
    }

    while ($event_queue->count() > 0) {
        $event_queue->pop()->execute($hub);
    }
}
