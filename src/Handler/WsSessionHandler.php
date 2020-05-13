<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;
use Acme\Log;

class WsSessionHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $str = $device->getConnection()->read();

        if ($str === '') {
            d(__CLASS__ . ' <> deviceid:' . $device->getId() . ' LEFT ');
            $hub->remove($device->getId());
            return;
        }

        if ($str === 'app:state') {
            $hub->notifyFrontend(json_encode([
                'lvl1' => [
                    'lvl2' => time(),
                ],
            ]));
        }

        Log::log(__CLASS__.':'.__FUNCTION__);

        d(__CLASS__ . ' <> deviceid:' . $device->getId() . ' sent: ' . $str);
        d('Nothing to do with that yet.');
    }
}
