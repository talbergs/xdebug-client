<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;
use Acme\UI\Messages\CUIMessageFactory;

class WsSessionHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $str = $device->getConnection()->read();

        $ws_message = CUIMessageFactory::fromString($str);
        $ws_message->actOn($hub);
    }
}
