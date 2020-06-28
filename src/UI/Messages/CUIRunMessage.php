<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIRunMessage implements IUIMessage
{
    public function __construct(array $params)
    {
    }

    public function actOn(Hub $hub)
    {
        $xdebug_app = $hub->getXDebugApp();
        $xdebug_app->cmdRun();
        $xdebug_app->commit();
    }
}
