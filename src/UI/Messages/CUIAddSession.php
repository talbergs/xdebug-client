<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;
use Acme\XDebugApp\XDebugSessionBag;


class CUIAddSession implements IUIMessage
{
    public int $listener_id;
    public string $idekey = '';

    public function __construct(array $params)
    {
        if (!array_key_exists('listener_id', $params)) {
            throw new \RuntimeException('listener_id parameter is mandatory');
        }

        $this->listener_id = (int) $params['listener_id'];

        if (array_key_exists('idekey', $params)) {
            $this->idekey = $params['idekey'];
        }
    }

    public function actOn(Hub $hub)
    {
        // TODO: check if listener id is correct
        $hub->xdebug_sessions[$this->listener_id . ':' . $this->idekey] = new XDebugSessionBag();

        /* new Device($new_conn, new XDebugSessionHandler($sess)); */

        /* $hub->bindXDebugListener($this->listener_id, $this->idekey); */

        /* $hub->notifyFrontend($info); */
    }
}
