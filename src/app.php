#!/usr/bin/env php
<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

use Acme\Connection\ConnectionHub;
use Acme\Connection\ConnectionInet;
use Acme\Connection\ConnectionUnix;


$hub = new ConnectionHub();

$web = ConnectionInet::new('web', 8080);
$xdb = ConnectionUnix::new('xdb', '/tmp/xdebug.sock');
$rpc = ConnectionUnix::new('rpc', '/tmp/rpc.sock');

$hub->add($web);
$hub->add($xdb);
$hub->add($rpc);

$events = [];
while (true) {
    d('tick-'.time());
    d($hub);

    /** @var ConnectionInterface $connection */
    foreach ($hub->selectRead(5) as $connection) {
        if ($connection->isLive()) {
            $connection->read();
        } else {
            $hub->add($connection->accept());
        }
    }

    while ($event = array_pop($events)) {
        d("RUN EVENT: $event");
    }
}
