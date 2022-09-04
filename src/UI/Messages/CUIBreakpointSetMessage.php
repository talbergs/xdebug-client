<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIBreakpointSetMessage implements IUIMessage
{
    public function actOn(Hub $hub)
    {
        /* $xdebug_app = $hub->getXDebugSession(); */
        /* $transaction_id = $xdebug_app->cmdBreakpointSet( */
        /*     'file:///home/ada/xdebug-client/example-page.php', */
        /*     5 */
        /* ); */
        /* $xdebug_app->addCallback($transaction_id, function($xml) { */
        /*     /1* d('----', $xml, '----', $this); *1/ */
        /* }); */
        /* $xdebug_app->commit(); */
    }
}
