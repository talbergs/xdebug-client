<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIStackGetMessage implements IUIMessage
{
    public function __construct(array $params)
    {
    }

    public function actOn(Hub $hub)
    {
        $xdebug_app = $hub->getXDebugApp();
        $xdebug_app->cmdStackGet();
        $xdebug_app->commit();
    }
}
