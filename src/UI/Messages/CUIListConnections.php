<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Handler\XDebugSessionHandler;
use Acme\Handler\XDebugAcceptHandler;
use Acme\Hub;


class CUIListConnections implements IUIMessage
{
    public function actOn(Hub $hub)
    {
        d($hub->devicesByHandler(XDebugAcceptHandler::class), __CLASS__);
        d($hub->devicesByHandler(XDebugSessionHandler::class), __CLASS__);
    }
}
