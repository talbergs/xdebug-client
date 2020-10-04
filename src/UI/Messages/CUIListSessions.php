<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIListSessions implements IUIMessage
{
    public function actOn(Hub $hub)
    {
        $resp = [];

        foreach ($hub->xd_session_bags as $session_bag_connection) {
            $connection = (string) $session_bag_connection;

            foreach ($hub->xd_session_bags->offsetGet($session_bag_connection)->sessions as $session) {
                $resp[] = [
                    'id' => spl_object_id($session),
                    'state' => $session->state,
                    'idekey' => $session->idekey,
                    'connection' => $connection,
                    'info' => [
                        'idekey' => $session->idekey,
                        'fileuri' => $session->fileuri,
                        'engine_version' => $session->engine_version,
                        'protocol_version' => $session->protocol_version,
                        'appid' => $session->appid,
                        'language' => $session->language,
                    ],
                    'breakpoints' => [
                        'breakpoints' => $session->breakpoints,
                    ],
                    'xd_server' => [
                        'typemap' => $session->typemap,
                    ],
                ];
            }

        }

        $hub->patchFrontend(json_encode($resp), "sessions");
    }
}
