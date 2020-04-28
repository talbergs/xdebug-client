<?php declare(strict_types=1);

namespace Acme\Connection;


class ConnectionInet implements ConnectionInterface
{
    use ConnectionTrait;

    public static function new(string $name, int $port): ConnectionInterface
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, '0.0.0.0', $port);
        socket_listen($socket);

        return new self($name, $socket, false);
    }
}
