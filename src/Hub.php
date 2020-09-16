<?php declare(strict_types=1);

namespace Acme;

use Acme\Device\Device;
use Acme\Connection\CConnection;
use Acme\Device\IDevice;
use Acme\Handler\XDebugAcceptHandler;
use Acme\Exceptions\XDebugSessionExists;
use Acme\Protocol\XdbProtocol;
use Acme\Exceptions\XDebugSessionNotFound;
use Acme\Handler\WsSessionHandler;
use Acme\XDebugApp\XDebugSession;


class Hub
{
    public \SplObjectStorage $XDSSESSIONS;

    /**
     * @var IDevice[]
     */
    protected array $devices = [];

    /**
     * @var XDebugSession[]
     */
    public array $xdebug_sessions = [];

    public function __construct()
    {
        $this->XDSSESSIONS = new \SplObjectStorage();
    }

    public function remove(int $id)
    {
        $this->devices[$id]->getConnection()->close();
        unset($this->devices[$id]);
    }

    public function add(IDevice $device)
    {
        $device->setId(count($this->devices));
        $this->devices[$device->getId()] = $device;
    }

    public function notifyFrontend(string $message)
    {
        $ws_deviceids = $this->devicesByHandler(WsSessionHandler::class);
        foreach ($ws_deviceids as $deviceid) {
            $this->get($deviceid)->getConnection()->write(json_encode(['type' => 'notify', 'msg' => $message]));
        }
    }

    /**
     * @return int[]
     */
    public function devicesByHandler(string $handler): array
    {
        $deviceids = [];

        foreach ($this->devices as $deviceid => $device) {
            if (get_class($device->getHandler()) === $handler) {
                $deviceids[] = $deviceid;
            }
        }

        return $deviceids;
    }

    /**
     * @return int[]
     */
    public function selectDeviceActivity(int $timeout): array
    {
        $write = null;
        $except = null;
        $read = [];

        foreach ($this->devices as $deviceid => $device) {
            $read[$deviceid] = $device->getConnection()->getResource();
        }

        socket_select($read, $write, $except, $timeout);

        return array_keys($read);
    }

    public function get(int $id): IDevice
    {
        return $this->devices[$id];
    }

    public function getXDebugSession(string $session_id): XDebugSession
    {
        if (!array_key_exists($session_id, $this->xdebug_sessions)) {
            throw new XDebugSessionNotFound($session_id);
        }

        return $this->xdebug_sessions[$session_id];
    }

    public function hasXDebugSession(string $session_id): bool
    {
        return array_key_exists($session_id, $this->xdebug_sessions);
    }

    public function addXDebugSession(string $session_id, XDebugSession $session)
    {
        $this->xdebug_sessions[$session_id] = $session;
    }

    public function hasXDebugConnection(string $connection_id): bool
    {
        foreach ($this->devices as $device) {
            if ((string) $device->getConnection() === $connection_id) {
                return true;
            }
        }

        return false;
    }

    // Absolute mess. RefCount since $sessions MAY SHARE $connection
    public function createXDebugSession(string $host, int $port, string $idekey): XDebugSession
    {
        $conn = null;
        foreach ($this->devices as $device) {
            if ((string) $device->getConnection() === "$host:$port") {
                $conn = $device->getConnection();
                break;
            }
        }

        if (!$conn) {
            if ($port != 0) {
                $conn = CConnection::inet($port, $host);
            } else {
                $conn = CConnection::unix($host);
            }

            $conn->setProtocol(new XdbProtocol());
            $xdb = new Device($conn, new XDebugAcceptHandler());

            $this->add($xdb);
        }

        $session = new XDebugSession($conn, $idekey);

        $session_id = 'SESS:' . $device->getId() . ':' . $idekey;

        if ($this->hasXDebugSession("{$session_id}")) {
            throw new XDebugSessionExists();
        }

        $this->addXDebugSession($session_id, $session);

        return $session;
    }

    public function createXDebugListener(string $host, int $port)
    {
        foreach ($this->devices as $device) {
            if ((string) $device->getConnection() === "$host:$port") {
                throw new \RuntimeException("XDebug Listener EXISTS! ".$device->getConnection());
                /* throw new XDebugSessionExists(); */
            }
        }

        if ($port != 0) {
            $conn = CConnection::inet($port, $host);
        } else {
            $conn = CConnection::unix($host);
        }

        $conn->setProtocol(new XdbProtocol());
        $xdb = new Device($conn, new XDebugAcceptHandler(new XDebugSession('idekey')));

        $this->add($xdb);
    }

    public array $xd_listeners = [];
    public array $xd_sessions = [];

    public function bindXDebugListener(int $listener_id, string $idekey)
    {
        $this->xd_listeners[$listener_id][$idekey] = 1;
    }

    public function bindXDebugSession(int $session_id, int $listener_id)
    {
        $this->xd_sessions[$session_id] = $listener_id;
    }
}
