<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Connection\IConnection;
use Acme\Handler\XDebugAcceptHandler;
use Acme\Device\Device;
use Acme\Connection\CConnection;
use Acme\Protocol\XdbProtocol;
use Acme\Hub;
use Acme\XDebugApp\XDebugSessionBag;


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

    protected function findConnection(Hub $hub): ?IConnection
    {
        foreach ($hub->devicesByHandler(XDebugAcceptHandler::class) as $deviceid) {
            $device = $hub->get($deviceid);
            $acc_conn = $device->getConnection();

            $uses_ip = $acc_conn->getAddress() === gethostbyname($this->host);
            $uses_port = $acc_conn->getPort() === $this->port;

            if ($uses_ip && $uses_port) {
                return $acc_conn;
            }
        }

        return null;
    }

    protected function createConnection(Hub $hub): IConnection
    {
        foreach ($hub->devicesByHandler(XDebugAcceptHandler::class) as $deviceid) {
            $device = $hub->get($deviceid);
            $acc_conn = $device->getConnection();

            $uses_ip = $acc_conn->getAddress() === gethostbyname($this->host);
            $uses_port = $acc_conn->getPort() === $this->port;

            if ($uses_ip && $uses_port) {
                return $acc_conn;
            }
        }

        // Create listener.
        if ($this->port != 0) {
            $conn = CConnection::inet($this->port, $this->host);
        } else {
            $conn = CConnection::unix($this->host);
        }

        info("Creating NEW connection {$conn}.");

        // Set appropriate protocol.
        $conn->setProtocol(new XdbProtocol());

        return $conn;
    }

    /**
     * open one connection per xDebug server
     */
    public function actOn(Hub $hub)
    {
        if ($conn = $this->findConnection($hub)) {
            $sess = new XDebugSessionBag($this->idekey, $conn);
            $hub->xdebug_sessions[] = $sess;
        } else {
            $conn = $this->createConnection($hub);

            // Create session.
            $sess = new XDebugSessionBag($this->idekey, $conn);
            $hub->xdebug_sessions[] = $sess;

            $device = new Device($conn, new XDebugAcceptHandler($sess));
            $hub->add($device);
        }

        // Add listener with that session.

        info("Listening for server connection on {$sess->connection}.");
        debug("@device {$device}.");
    }
}
