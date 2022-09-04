<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUISourceMessage implements IUIMessage
{
    protected string $sessionid;
    protected string $filename;

    public function __construct(array $params)
    {
        $this->sessionid = (string) $params['sessionid'];
        $this->filename = (string) $params['filename'];
    }

    public function actOn(Hub $hub)
    {
        $session = $hub->getXDebugSession((int) $this->sessionid);

        $session->cmdSource($this->filename);

        $bag = $hub->xd_map_sessid_to_bag[spl_object_id($session)];

        $bag->commit($session);
    }
}
