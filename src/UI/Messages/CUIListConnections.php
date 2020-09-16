<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Handler\XDebugSessionHandler;
use Acme\Handler\XDebugAcceptHandler;
use Acme\Hub;


class CUIListConnections implements IUIMessage
{
    public function actOn(Hub $hub)
    {
        $waiting_deviceids = $hub->devicesByHandler(XDebugAcceptHandler::class);
        $active_deviceids = $hub->devicesByHandler(XDebugSessionHandler::class);

        d(compact('active_deviceids', 'waiting_deviceids'));

        d('waiting_deviceids');
        foreach ($waiting_deviceids as $device_id) {
            d($hub->get($device_id));
        }

        d('active_deviceids');
        foreach ($active_deviceids as $device_id) {
            d($hub->get($device_id));
        }
    }
}
