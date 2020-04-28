<?php declare(strict_types=1);

namespace Acme\Connection;


class ConnectionUnix implements ConnectionInterface
{
    use ConnectionTrait;

    public static function new(string $name, string $sock_path): self
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);

        @unlink($sock_path);
        socket_bind($socket, $sock_path);
        socket_listen($socket);

        return new self($name, $socket, false);
    }
}
