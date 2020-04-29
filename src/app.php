#!/usr/bin/env php
<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

use Acme\Connection\ConnectionHub;
use Acme\Connection\ConnectionInet;
use Acme\Connection\ConnectionUnix;
use Acme\Connection\ConnectionInterface;
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

$events = [];
while (true) {
    d('tick-'.time());
    d($hub);

    /** @var ConnectionInterface $connection */
    foreach ($hub->selectRead(5) as $connection) {
        if ($connection->isLive()) {
            if ($connection->hasClient()) {
                if ($connection->getName() === 'web') {
                    $app->onHTTPRequest($connection);
                } else if ($connection->getName() === 'xdb') {
                } else if ($connection->getName() === 'rpc') {
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
