<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Handler\XDebugAcceptHandler;
use Acme\Device\Device;
use Acme\Connection\CConnection;
use Acme\Protocol\XdbProtocol;
use Acme\Hub;
use Acme\XDebugApp\XDebugSession;


class CUIStartXDSession implements IUIMessage
{
    public string $idekey = '';
    public string $host;
    public int $port = 0;

    public function __construct(array $params)
    {
        if (!array_key_exists('host', $params)) {
            throw new \RuntimeException('Host parameter is mandatory');
        }

        $this->host = $params['host'];

        if (array_key_exists('idekey', $params)) {
            $this->idekey = (string) $params['idekey'];
        }

        if (array_key_exists('port', $params)) {
            $this->port = (int) $params['port'];
        }
    }

    public function actOn(Hub $hub)
    {
        // Create listener.
        if ($this->port != 0) {
            $conn = CConnection::inet($this->port, $this->host);
        } else {
            $conn = CConnection::unix($this->host);
        }

        // Set appropriate protocol.
        $conn->setProtocol(new XdbProtocol());

        // Create session.
        $sess = new XDebugSession($this->idekey, $conn);
        $hub->xdebug_sessions[] = $sess;

        // Add listener with that session.
        $xdb = new Device($conn, new XDebugAcceptHandler($sess));
        $hub->add($xdb);

        info("=== start session ===");
        d($hub);
        info("=== start session ===");

    }
}
