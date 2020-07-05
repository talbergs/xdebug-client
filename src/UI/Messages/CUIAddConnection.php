<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Handler\XDebugAcceptHandler;
use Acme\Device\Device;
use Acme\Connection\CConnection;
use Acme\Protocol\XdbProtocol;
use Acme\Hub;


class CUIAddConnection implements IUIMessage
{
    public function __construct(array $params)
    {
        d($params);
    }

    public function actOn(Hub $hub)
    {
        $xdb_conn = CConnection::inet(9000);
        $xdb_conn->setProtocol(new XdbProtocol());
        $xdb = new Device($xdb_conn, new XDebugAcceptHandler());
        $hub->add($xdb);
        info('Listening for XDebug connection on: /tmp/xdebug.sock');

        /* $hub->notifyFrontend(json_encode(['connections' => [111]])); */
        $hub->notifyFrontend('notify');
    }
}
