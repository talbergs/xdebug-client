<?php declare(strict_types=1);

namespace Acme\Device;

use Acme\Connection\IConnection;
use Acme\Handler\IHandler;
use Acme\Hub;


interface IDevice
{
    public function getConnection(): IConnection;
    public function setConnection(IConnection $conn);

    public function setHandler(IHandler $handler);
    public function getHandler(): IHandler;

    public function setId(int $id);
    public function getId(): int;

    public function exec(Hub $hub);
}
