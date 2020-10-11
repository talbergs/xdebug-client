<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIStatusMessage implements IUIMessage
{
    protected string $sessionid;

    public function __construct(array $params)
    {
        $this->sessionid = (string) $params['sessionid'];
    }

    public function actOn(Hub $hub)
    {
        $session = $hub->getXDebugSession((int) $this->sessionid);
        $session->cmdStatus();

        $bag = $hub->xd_map_sessid_to_bag[spl_object_id($session)];
        d($bag, $session);
        $bag->commit($session);
    }
}
