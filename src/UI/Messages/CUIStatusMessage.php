<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIStatusMessage implements IUIMessage
{
    public function __construct(array $params)
    {
    }

    public function actOn(Hub $hub)
    {
        $xdebug_app = $hub->getXDebugSession();
        $transaction_id = $xdebug_app->cmdStatus();
        $xdebug_app->addCallback($transaction_id, function($xml) use ($hub) {
            /* d('----', $xml, '----', $this); */
            /* $hub->getState()->setState('engine.status', 32); */
            /* $hub->notifyFrontend(json_encode($hub->getState()->getFullState())); */
        });
        $xdebug_app->commit();
    }
}
