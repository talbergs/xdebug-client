#!/usr/bin/env php
<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

use Acme\Connection\CConnection;
use Acme\Device\Device;
use Acme\Exceptions\EConnectionBroke;
use Acme\Exceptions\EUnknownUIMessage;
use Acme\Handler\HttpAcceptHandler;
use Acme\Handler\RpcAcceptHandler;
use Acme\Handler\XDebugAcceptHandler;
use Acme\Hub;
use Acme\Protocol\CHttpProtocol;
use Acme\Protocol\RpcProtocol;
use Acme\Protocol\XdbProtocol;
use Acme\State\State;
use Acme\Log;
use Acme\XDebugApp\XDebugApp;

Log::setLogFile('/tmp/app.log');

$hub = new Hub(new XDebugApp());

$http_conn = CConnection::inet(8080);
$http_conn->setProtocol(new CHttpProtocol());
$web = new Device($http_conn, new HttpAcceptHandler());
$hub->add($web);
echo "Listening for HTTP connection on: 0.0.0.0:8080" . PHP_EOL;

$xdb_conn = CConnection::unix('/tmp/xdebug.sock');
$xdb_conn->setProtocol(new XdbProtocol());
$xdb = new Device($xdb_conn, new XDebugAcceptHandler());
$hub->add($xdb);
echo "Listening for XDebug connection on: /tmp/xdebug.sock" . PHP_EOL;

$rpc_conn = CConnection::unix('/tmp/rpc.sock');
$xdb_conn->setProtocol(new RpcProtocol());
$rpc = new Device($rpc_conn, new RpcAcceptHandler());
$hub->add($rpc);

$state = new State();
$state->generateJsState(__DIR__ . '/../public/mjs/state/default.mjs');
$hub->setState($state);

while (true) {
    foreach ($hub->selectDeviceActivity(150) as $deviceid) {
        try {
            $device = $hub->get($deviceid);
            $device->exec($hub);
        } catch (EUnknownUIMessage $e) {
            $hub->notifyFrontend(json_encode(['errors' => [$e->getMessage()]]));
        } catch (EConnectionBroke $e) {
            $hub->remove($device->getId());
        }
    }
}
