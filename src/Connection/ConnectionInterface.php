<?php declare(strict_types=1);

namespace Acme\Connection;


interface ConnectionInterface
{
    public function write();
    public function getSocket();
    public function getResourceId(): int;
    public function getName(): string;
    public function isLive(): bool;
    public function accept(): ConnectionInterface;
    public function close();
    public function read(string &$error = null): ?string;
}
