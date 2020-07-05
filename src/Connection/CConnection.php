<?php declare(strict_types=1);

namespace Acme\Connection;

use Acme\Protocol\IProtocol;
use Acme\Protocol\CNullProtocol;


final class CConnection implements IConnection
{
    protected IProtocol $protocol;
    protected $resource;

    protected function __construct($resource, IProtocol $protocol)
    {
        $this->resource = $resource;
        $this->protocol = $protocol;
    }

    public function read(): string
    {
        return $this->protocol->read($this->resource);
    }

    public function write(string $str)
    {
        $this->protocol->write($this->resource, $str);
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setProtocol(IProtocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function accept(): IConnection
    {
        $resource = socket_accept($this->resource);

        return new self($resource, $this->protocol);
    }

    public static function inet(int $port): IConnection
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        try {
            socket_bind($socket, '0.0.0.0', $port);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Could not bind to ${port}", socket_last_error(), $e);
        }
        socket_listen($socket);

        return new self($socket, new CNullProtocol());
    }

    public static function unix(string $sock_path): IConnection
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);

        @unlink($sock_path);
        $success = socket_bind($socket, $sock_path);
        if (!$success) {
            throw new \RuntimeException("Could not bind to ${sock_path}");
        }
        socket_listen($socket);

        return new self($socket, new CNullProtocol());
    }

    public function close()
    {
        socket_close($this->resource);
    }
}
