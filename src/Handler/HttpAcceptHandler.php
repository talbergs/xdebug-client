<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\Device;
use Acme\Device\IDevice;
use Acme\Hub;
use Acme\Protocol\HttpProtocol;

class HttpAcceptHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $new_conn = $device->getConnection()->accept();
        $new_conn->setProtocol(new HttpProtocol());
        $new_device = new Device($new_conn, new HttpSessionHandler());
        $hub->add($new_device);
    }
}
