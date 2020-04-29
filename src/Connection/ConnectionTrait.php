<?php declare(strict_types=1);

namespace Acme\Connection;


trait ConnectionTrait
{
    protected $socket;
    protected string $name;
    protected bool $is_live;

    protected function __construct(string $name, $socket, bool $is_live)
    {
        if (!is_resource($socket)) {
            throw new \RuntimeException('Wrong type given.');
        }

        $this->socket = $socket;
        $this->name = $name;
        $this->is_live = $is_live;
    }

    public function isLive(): bool
    {
        return $this->is_live;
    }

    public function getResourceId(): int
    {
        return (int) $this->socket;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasClient(): bool
    {
        $len = socket_recv($this->socket, $buf, 1, MSG_DONTWAIT | MSG_PEEK);

        return $len != 0;
    }

    public function read(string &$error = null): ?string
    {
        if (!$this->isLive()) {
            throw new \RuntimeException('Cannot read of a pending connection.');
        }

        // TODO: exhaust it.
        $len = socket_recv($this->socket, $buf, 2100, MSG_DONTWAIT);

        if ($len === 0) {
            $error = "Client left!";
        } else if ($len === false) {
            $error = "Some error!";
        }

        return $buf;
    }

    public function write(string $str)
    {
        if (!$this->isLive()) {
            throw new \RuntimeException('Cannot write to a pending connection.');
        }

        $bytes_written = socket_write($this->socket, $str);

        if ($bytes_written !== strlen($str)) {
            throw new \RuntimeException('This is a TODO, you are welcome!');
        }
    }

    public function close()
    {
        socket_close($this->socket);
    }

    public function accept(): ConnectionInterface
    {
        $socket = socket_accept($this->socket);

        return new self($this->name, $socket, true);
    }

    public function getSocket()
    {
        return $this->socket;
    }
}
