<?php declare(strict_types=1);

namespace Acme\Connection;

use Acme\Protocol\IProtocol;


interface IConnection
{
    public function read(): string;
    public function write(string $str);
    public function setProtocol(IProtocol $protocol);
    public function accept(): IConnection;
    public function getResource();
    public function close();
    public function getId(): int;
    public function getPort(): ?int;
    public function getAddress(): string;
    public function isAccepted(): bool;
}
