<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Handler\XDebugAcceptHandler;
use Acme\Device\Device;
use Acme\Connection\CConnection;
use Acme\Protocol\XdbProtocol;
use Acme\Hub;


class CUIAddConnection implements IUIMessage
{
    public string $host;
    public int $port;

    public function __construct(array $params)
    {
        $this->host = $params['host'];
        $this->port = (int) $params['port'];
    }

    public function actOn(Hub $hub)
    {
        if ($this->port != 0) {
            $xdb_conn = CConnection::inet($this->port, $this->host);
        } else {
            $xdb_conn = CConnection::unix($this->host);
        }

        $xdb_conn->setProtocol(new XdbProtocol());
        $xdb = new Device($xdb_conn, new XDebugAcceptHandler());
        $hub->add($xdb);

        info("Listening for XDebug connection on: {$xdb_conn}");

        /* $hub->notifyFrontend(json_encode(['connections' => [111]])); */
        $hub->notifyFrontend('notify');
    }
}
