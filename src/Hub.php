<?php declare(strict_types=1);

namespace Acme;

use Acme\Device\IDevice;


class Hub
{
    /**
     * @var IDevice[]
     */
    protected array $devices = [];
    protected array $live_ids = [];

    public function remove(int $id)
    {
        $this->devices[$id]->getConnection()->close();
        unset($this->devices[$id]);
    }

    public function switch(IDevice $from, IDevice $to)
    {
        unset($this->devices[$this->getConnectionId($from)]);
        $this->devices[$this->getConnectionId($to)] = $to;
    }

    public function connectionsByName(string $name)
    {
        foreach ($this->devices as $device) {
            if ($device->getName() == $name) {
                yield $device;
            }
        }
    }

    public function isLive(IDevice $device): bool
    {
        return $this->live_ids[(int) $device->getResource()] ?? false;
    }

    public function add(IDevice $device)
    {
        $device->setId(count($this->devices));
        $this->devices[$device->getId()] = $device;
    }

    /**
     * @return int[]
     */
    public function selectDeviceActivity(int $timeout): array
    {
        $write = null;
        $except = null;
        $read = [];

        if (!$this->devices) {
            throw new \RuntimeException('No connections.');
        }

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
}
