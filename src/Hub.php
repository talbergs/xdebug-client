<?php declare(strict_types=1);

namespace Acme;

use Acme\Device\IDevice;
use Acme\Handler\WsSessionHandler;
use Acme\State\State;
use Acme\XDebugApp\XDebugApp;


class Hub
{
    /**
     * @var IDevice[]
     */
    protected array $devices = [];
    protected XDebugApp $xdebug_app;

    public function __construct(XDebugApp $xdebug_app)
    {
        $this->xdebug_app = $xdebug_app;
    }

    /**
     * WRONG!!!
     */
    protected State $state;
    public function getState(): State
    {
        return $this->state;
    }
    public function setState(State $state)
    {
        $this->state = $state;
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
            $this->get($deviceid)->getConnection()->write($message);
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
        return $this->devices[$id];;
    }

    public function getXDebugApp(): XDebugApp
    {
        return $this->xdebug_app;
    }
}
