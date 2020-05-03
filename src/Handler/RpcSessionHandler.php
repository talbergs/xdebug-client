<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;

class RpcSessionHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $got = $device->getConnection()->read();

        ob_start();
        d($hub);
        $device->getConnection()->write(ob_get_clean());

        /* list($a, $m) = explode(':', $got); */
        /* $m = trim($m); */
        /* d("RPC: [$got] <<<<<<"); */
        /* switch ($a) { */
        /* case 'dump hub': d($hub);break; */
        /* case 'x': */
        /*     foreach ($hub->connectionsByName('xdb-session') as $conn) { */
        /*         d($conn); */
        /*         $conn->write($m); */
        /*     } */
        /*     break; */
        /* case 'ws': */
        /*     foreach ($hub->connectionsByName('ws') as $conn) { */
        /*         $conn->write($m); */
        /*     } */
        /*     break; */
        /* default: d("!! UNKNOWN ${got} RPC COMMAND !!"); */
        /* } */

        $hub->remove($device->getId());
    }
}
