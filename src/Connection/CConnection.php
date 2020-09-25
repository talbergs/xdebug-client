<?php declare(strict_types=1);

namespace Acme\Connection;

use Acme\Protocol\IProtocol;
use Acme\Protocol\CNullProtocol;


final class CConnection implements IConnection
{
    protected IProtocol $protocol;
    protected $resource;
    protected string $address = '';
    protected int $port = 0;
    protected bool $accepted = false;

    protected function __construct($resource, IProtocol $protocol)
    {
        $this->resource = $resource;
        $this->protocol = $protocol;
    }

    public function close()
    {
        socket_close($this->resource);
    }

    public function read(): string
    {
        return $this->protocol->read($this->resource);
    }

    public function write(string $str)
    {
        $this->protocol->write($this->resource, $str);
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function accept(): IConnection
    {
        $resource = socket_accept($this->resource);

        $conn = new self($resource, $this->protocol);
        $conn->accepted = true;

        return $conn;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setProtocol(IProtocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function getPort(): int
    {
        /* socket_getsockname($this->resource, $_addr, $port); */
        /* return $port; */

        return $this->port;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function toApi(): array
    {
        socket_getsockname($this->resource, $host, $port);

        return $port === null ? compact('host') : compact('host', 'port');
    }

    public function getId(): int
    {
        return (int) $this->resource;
    }

    public function __toString(): string
    {
        socket_getsockname($this->resource, $host, $port);

        return $port === null ? $host : "$host:$port";
    }

    public static function inet(int $port, string $host = '0.0.0.0'): IConnection
    {
        $addrs = gethostbynamel($host);
        if ($addrs === false) {
            throw new \RuntimeException("DNS error - Cannot look up '${host}'");
        }

        /* $host = reset($addrs); */
        /* if ($host === '127.0.0.1') { */
        /*     $host = '0.0.0.0'; */
        /* } */

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        $res = socket_bind($socket, $host, $port);
        if ($res === false) {
            throw new \RuntimeException("Could not bind to ${$host} ${port}", socket_last_error());
        }

        socket_listen($socket);

        $conn = new self($socket, new CNullProtocol());

        $conn->address = gethostbyname($host);
        $conn->port = $port;

        return $conn;
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
}
