<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\Device;
use Acme\Device\IDevice;
use Acme\Hub;
use Acme\Protocol\RpcProtocol;

class RpcAcceptHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $new_conn = $device->getConnection()->accept();
        $new_conn->setProtocol(new RpcProtocol());
        $new_device = new Device($new_conn, new RpcSessionHandler());
        $hub->add($new_device);
    }
}
