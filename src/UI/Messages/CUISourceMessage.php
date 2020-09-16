<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUISourceMessage implements IUIMessage
{
    public function __construct(array $params)
    {
    }

    public function actOn(Hub $hub)
    {
        $xdebug_app = $hub->getXDebugSession();
        $transaction_id = $xdebug_app->cmdSource();
        $xdebug_app->addCallback($transaction_id, function($xml) {
            /* d('----', $xml, '----', $this); */
        });
        $xdebug_app->commit();
    }
}
