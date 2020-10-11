<?php declare(strict_types=1);

namespace Acme;

use Acme\Connection\IConnection;
use Acme\Device\IDevice;
use Acme\Exceptions\XDebugSessionNotFound;
use Acme\Handler\WsSessionHandler;
use Acme\XDebugApp\XDebugSession;
use Acme\XDebugApp\XDebugSessionBag;
use SplObjectStorage;


class Hub
{
    public \SplObjectStorage $xd_session_bags;

    /**
     * @var IDevice[]
     */
    protected array $devices = [];
    public array $xd_sessions = [];

    public array $xd_map_sessid_to_bag = [];

    public function __construct()
    {
        $this->xd_session_bags = new SplObjectStorage();
    }

    public function findOrCreateSessionBag(IConnection $connection): XDebugSessionBag
    {
        if ($this->xd_session_bags->contains($connection)) {
            return $this->xd_session_bags->offsetGet($connection);
        }

        $this->xd_session_bags->offsetSet($connection, new XDebugSessionBag($connection));

        return $this->xd_session_bags->offsetGet($connection);
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

    public function patchFrontend(string $message, string $path)
    {
        $ws_deviceids = $this->devicesByHandler(WsSessionHandler::class);
        foreach ($ws_deviceids as $deviceid) {
            $this->get($deviceid)->getConnection()->write(json_encode([
                'type' => 'patch',
                'path' => $path,
                'msg' => $message
            ]));
        }
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

    public function getXDebugSession(int $session_id): XDebugSession
    {
        if (!array_key_exists($session_id, $this->xd_sessions)) {
            throw new XDebugSessionNotFound($session_id);
        }

        return $this->xd_sessions[$session_id];
    }
}
