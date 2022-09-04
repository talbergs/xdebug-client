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
                    'breakpoints' => $session->breakpoints,
                    'stack' => $session->stack,
                    'xd_server' => [
                        'typemap' => $session->typemap,
                    ],
                    'source' => $session->source,
                    'code_lineno' => $session->code_lineno,
                ];
            }

        }

        $hub->patchFrontend(json_encode($resp), "sessions");
    }
}
