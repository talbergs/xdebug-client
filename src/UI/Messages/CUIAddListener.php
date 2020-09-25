<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Handler\XDebugAcceptHandler;
use Acme\Device\Device;
use Acme\Connection\CConnection;
use Acme\Protocol\XdbProtocol;
use Acme\Hub;
use Acme\XDebugApp\XDebugSessionBag;


class CUIAddListener implements IUIMessage
{
    public string $host;
    public int $port = 0;

    public function __construct(array $params)
    {
        if (!array_key_exists('host', $params)) {
            throw new \RuntimeException('Host parameter is mandatory');
        }

        $this->host = $params['host'];

        if (array_key_exists('port', $params)) {
            $this->port = (int) $params['port'];
        }
    }

    // It actually adds new oending session WITH IDEKEY
    public function actOn(Hub $hub)
    {

        /* foreach ($hub->devices as $device) { */
        /*     if ((string) $device->getConnection() === "$host:$port") { */
        /*         throw new \RuntimeException("XDebug Listener EXISTS! ".$device->getConnection()); */
        /*         /1* throw new XDebugSessionExists(); *1/ */
        /*     } */
        /* } */

        if ($this->port != 0) {
            $conn = CConnection::inet($this->port, $this->host);
        } else {
            $conn = CConnection::unix($this->host);
        }

        $IDEKEY = "some";

        $sess = new XDebugSessionBag($IDEKEY);
        $conn->setProtocol(new XdbProtocol());
        $xdb = new Device($conn, new XDebugAcceptHandler($sess));
        $hub->add($xdb);

        /* $hub->createXDebugListener($this->host, $this->port); */
        /* $hub->createXDebugListener($this->host, $this->port); */
    }
}
