#!/usr/bin/env php
<?php declare(strict_types=1);
// EXAMPLE:
// https://github.com/Mte90/pugdebug/blob/master/pugdebug/pugdebug.py

require_once dirname(__FILE__) . '/bootstrap.php';

use Acme\Connection\CConnection;
use Acme\Device\Device;
use Acme\Exceptions\EConnectionBroke;
use Acme\Exceptions\EUnknownUIMessage;
use Acme\Exceptions\XDebugClientLeft;
use Acme\Exceptions\XDebugSessionExists;
use Acme\Exceptions\XDebugSessionNotFound;
use Acme\Handler\HttpAcceptHandler;
use Acme\Hub;
use Acme\Protocol\CHttpProtocol;

/* (new \NunoMaduro\Collision\Provider)->register(); */
$hub = new Hub();
// Xdebug Session:
// - STATUS: enabled (listening) | disabled (pending) | active (debugging)
// - CONNECTION: port, host, idekey

$port = 8080;
$http_conn = CConnection::inet($port);
$http_conn->setProtocol(new CHttpProtocol());
$web = new Device($http_conn, new HttpAcceptHandler());
$hub->add($web);
info("Listening for HTTP connection on: {$http_conn}");

while (true) {
    foreach ($hub->selectDeviceActivity(150) as $deviceid) {
        $device = $hub->get($deviceid);
        debug("> Activity on: {$device}");

        try {
            $device->exec($hub);
        } catch (XDebugSessionNotFound $e) {

            debug("{$device} XDebugSessionNotFound {$e->getMessage()}");
            $hub->remove($device->getId());

        } catch (EUnknownUIMessage $e) {
            debug("{$device} EUnknownUIMessage {$e->getMessage()}");
            $hub->notifyFrontend(json_encode(['errors' => [$e->getMessage()]]));
        } catch (XDebugClientLeft $e) {
            debug("{$device} Debugger engine left unexpectedly.");
            $hub->remove($device->getId());
        } catch (XDebugSessionExists $e) {
            debug("{$device} XDebugSessionExists {$e->getMessage()}");
        } catch (EConnectionBroke $e) {
            debug("{$device} EConnectionBroke {$e->getMessage()}");
            $hub->remove($device->getId());
        } catch (Throwable $e) {
            debug("{$device} Throwable {$e->getMessage()}");
            d('==TRACE_START==');
            d($e->getTrace());
            d('==TRACE_END==');
        }
    }
}
