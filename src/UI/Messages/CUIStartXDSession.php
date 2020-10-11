<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Connection\IConnection;
use Acme\Handler\XDebugAcceptHandler;
use Acme\Device\Device;
use Acme\Connection\CConnection;
use Acme\Protocol\XdbProtocol;
use Acme\Hub;


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

        // Set appropriate protocol.
        $conn->setProtocol(new XdbProtocol());

        return $conn;
    }

    /**
     * open one connection per xDebug server
     */
    public function actOn(Hub $hub)
    {
        $connection = $this->findConnection($hub);
        $is_new_conn = false;

        if (!$connection) {
            $is_new_conn = true;
            $connection = $this->createConnection($hub);
            info("Creating NEW connection {$connection}.");
        } else {
            info("Using connection {$connection}.");
        }

        $session_bag = $hub->findOrCreateSessionBag($connection);

        if ($session_bag->hasSession($this->idekey)) {
            info("Connot create {$this->idekey} is already used.");
            return;
        }

        $session = $session_bag->findOrCreateSession($this->idekey);

        $hub->xd_map_sessid_to_bag[spl_object_id($session)] = $session_bag;
        $hub->xd_sessions[spl_object_id($session)] = $session;

        if ($is_new_conn) {
            $device = new Device($connection, new XDebugAcceptHandler($session_bag));
            $hub->add($device);
        }

        (new CUIListSessions)->actOn($hub);

        // TODO: we may respond that session on this Device with this IDEKEY already exists
        /* $session_bag->findOrCreateSession($this->idekey); */

        /* info("Listening for server connection on {$connection->connection}."); */
        /* debug("@device {$device}."); */
    }
}
