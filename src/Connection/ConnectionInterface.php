<?php declare(strict_types=1);

namespace Acme\Connection;


interface ConnectionInterface
{
    public function write(string $buf);
    public function getSocket();
    public function getResourceId(): int;
    public function getName(): string;
    // BASICALLY THE NAME IS I/O PROTOCOL TYPE
    public function setName(string $name);
    public function isLive(): bool;
    public function hasClient(): bool;
    public function accept(): ConnectionInterface;
    public function close();
    public function read(string &$error = null): ?string;
}
