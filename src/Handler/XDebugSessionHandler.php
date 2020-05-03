<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;

class XDebugSessionHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        d(__CLASS__ . ' <> deviceid:' . $device->getId() . ' sent: ' . $device->getConnection()->read());
        d('Nothing to do with that yet.');
    }
}
