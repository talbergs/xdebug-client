<?php declare(strict_types=1);

namespace Acme\Device;

use Acme\Connection\IConnection;
use Acme\Handler\IHandler;
use Acme\Hub;


class Device implements IDevice
{
    protected IConnection $conn;
    protected IHandler $handler;
    protected int $id;

    public function __construct(IConnection $conn, IHandler $handler)
    {
        $this->conn = $conn;
        $this->handler = $handler;
        $this->id = 0;
    }

    public function getConnection(): IConnection
    {
        return $this->conn;
    }

    public function setConnection(IConnection $conn)
    {
        $this->conn = $conn;
    }

    public function setHandler(IHandler $handler)
    {
        $this->handler = $handler;
    }

    public function getHandler(): IHandler
    {
        return $this->handler;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function exec(Hub $hub)
    {
        $this->handler->handle($this, $hub);
    }

    public function __toString(): string
    {
        $handler = get_class($this->handler);

        return "{$this->conn} [$this->id] ($handler)";
    }
}
