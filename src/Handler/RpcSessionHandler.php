<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;

class RpcSessionHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $got = $device->getConnection()->read();

        $pts = explode(':', $got);
        $action = array_shift($pts);
        $message = trim(implode(':', $pts));

        /* d("RPC: [$got] <<<<<<"); */

        switch ($action) {
            case 'hub': // d($hub);break;
            case 'xdd':
                $xd_deviceids = $hub->devicesByHandler(XDebugSessionHandler::class);
                /* d(compact('xd_deviceids', 'action', 'message')); */
                foreach ($xd_deviceids as $deviceid) {
                    /* d($deviceid); */
                    $app = $hub->get($deviceid)->getHandler()->app;
                    eval($message);
                }
                break;
            case 'xd':
                $xd_deviceids = $hub->devicesByHandler(XDebugSessionHandler::class);
                /* d(compact('xd_deviceids', 'action', 'message')); */
                foreach ($xd_deviceids as $deviceid) {
                    /* d($deviceid); */
                    $hub->get($deviceid)->getConnection()->write($message);
                }
                break;
            case 'ws':
                $ws_deviceids = $hub->devicesByHandler(WsSessionHandler::class);
                /* d(compact('ws_deviceids', 'action', 'message')); */
                foreach ($ws_deviceids as $deviceid) {
                    $hub->get($deviceid)->getConnection()->write($message);
                }
                break;
            default: // d("!! UNKNOWN ${got} RPC COMMAND !!");
        }

        $hub->remove($device->getId());
    }
}
