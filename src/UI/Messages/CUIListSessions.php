<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIListSessions implements IUIMessage
{
    public function actOn(Hub $hub)
    {
        $resp = [];
        foreach ($hub->xdebug_sessions as $session) {
            $resp[] = [
                'state' => $session->state,
                'idekey' => $session->idekey,
                'connection' => (string) $session->conn,
            ];
        }

        $hub->notifyFrontend(json_encode($resp));
        d($hub->xdebug_sessions);
        d($resp);
    }
}
