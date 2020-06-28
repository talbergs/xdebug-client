<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIExitSessionMessage implements IUIMessage
{
    public function __construct(array $params)
    {
    }

    public function actOn(Hub $hub)
    {
        $xdebug_app = $hub->getXDebugApp();
        $transaction_id = $xdebug_app->cmdStop();
        $xdebug_app->addCallback($transaction_id, function($xml) use ($hub, $xdebug_app) {
            d('>>>>>>>>', $xml, '<<<<<<', $this);
            $hub->remove($xdebug_app->getDevice()->getId());
            $xdebug_app->appid = null;
        });
        $xdebug_app->commit();
    }
}
