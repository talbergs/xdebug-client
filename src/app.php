#!/usr/bin/env php
<?php declare(strict_types=1);
// EXAMPLE:
// https://github.com/Mte90/pugdebug/blob/master/pugdebug/pugdebug.py

require_once dirname(__FILE__) . '/bootstrap.php';

use Acme\Connection\CConnection;
use Acme\Device\Device;
use Acme\Exceptions\EConnectionBroke;
use Acme\Exceptions\EUnknownUIMessage;
use Acme\Handler\HttpAcceptHandler;
use Acme\Hub;
use Acme\Protocol\CHttpProtocol;
use Acme\State\State;
use Acme\XDebugApp\XDebugApp;

(new \NunoMaduro\Collision\Provider)->register();
$hub = new Hub(new XDebugApp());

$port = 8080;
$http_conn = CConnection::inet($port);
$http_conn->setProtocol(new CHttpProtocol());
$web = new Device($http_conn, new HttpAcceptHandler());
$hub->add($web);
info("Listening for HTTP connection on: {$http_conn}");

$state = new State();
$state->generateJsState(public_path('mjs/state/default.mjs'));
$hub->setState($state);

while (true) {
    foreach ($hub->selectDeviceActivity(150) as $deviceid) {
        try {
            $device = $hub->get($deviceid);
            $device->exec($hub);
        } catch (EUnknownUIMessage $e) {
            $hub->notifyFrontend(json_encode(['errors' => [$e->getMessage()]]));
            debug($e->getMessage());
        } catch (EConnectionBroke $e) {
            $hub->remove($device->getId());
            debug($e->getMessage());
        } catch (Throwable $e) {
            debug($e->getMessage());
        }
    }
}
