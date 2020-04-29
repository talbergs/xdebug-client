<?php declare(strict_types=1);

namespace Acme\Connection;


class ConnectionHub
{
    protected array $connections = [];

    public function drop(ConnectionInterface $conn)
    {
        $connid = $this->getConnectionId($conn);
        $this->connections[$connid]->close();
        unset($this->connections[$connid]);
    }

    public function add(ConnectionInterface $conn)
    {
        $this->connections[$this->getConnectionId($conn)] = $conn;
    }

    public function getConnectionId(ConnectionInterface $conn): string
    {
        return sprintf('%s:%s:%s', (int) $conn->isLive(), $conn->getName(), $conn->getResourceId());
    }

    /**
     * @return ConnectionInterface[]
     */
    public function selectRead(int $timeout): \Generator
    {
        $write = null;
        $except = null;
        $read = [];

        if (!$this->connections) {
            throw new \RuntimeException('No connections.');
        }

        foreach ($this->connections as $connid => $conn) {
            $read[$connid] = $conn->getSocket();
        }

        socket_select($read, $write, $except, $timeout);

        foreach (array_keys($read) as $connid) {
            yield $this->connections[$connid];
        }
    }
}
