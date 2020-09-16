<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIAppStateMessage implements IUIMessage
{
    public function actOn(Hub $hub)
    {
        /* $hub->notifyFrontend(json_encode($hub->getState()->getFullState())); */
    }
}
