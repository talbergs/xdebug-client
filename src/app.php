#!/usr/bin/env php
<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

use Acme\Connection\CConnection;
use Acme\Device\Device;
use Acme\Handler\HttpAcceptHandler;
use Acme\Handler\RpcAcceptHandler;
use Acme\Handler\XDebugAcceptHandler;
use Acme\Hub;
use Acme\Protocol\HttpProtocol;
use Acme\Protocol\RpcProtocol;
use Acme\Protocol\XdbProtocol;
use Acme\Log;

Log::setLogFile('/tmp/app.log');

$hub = new Hub();

$http_conn = CConnection::inet(8080);
$http_conn->setProtocol(new HttpProtocol());
$web = new Device($http_conn, new HttpAcceptHandler());
$hub->add($web);

$xdb_conn = CConnection::unix('/tmp/xdebug.sock');
$xdb_conn->setProtocol(new XdbProtocol());
$xdb = new Device($xdb_conn, new XDebugAcceptHandler());
$hub->add($xdb);

$rpc_conn = CConnection::unix('/tmp/rpc.sock');
$xdb_conn->setProtocol(new RpcProtocol());
$rpc = new Device($rpc_conn, new RpcAcceptHandler());
$hub->add($rpc);


while (true) {
    echo 'tick-'.time().PHP_EOL;
    d($hub);

    foreach ($hub->selectDeviceActivity(150) as $deviceid) {
        $device = $hub->get($deviceid);
        $device->exec($hub);
    }
}
