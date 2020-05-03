<?php declare(strict_types=1);

namespace Acme\Connection;


class ConnectionXdebug implements ConnectionInterface
{
    use ConnectionTrait;

    public static function fromUnix(ConnectionInterface $conn): self
    {
        return new self('xdb-session', $conn->getSocket(), true);
    }

    public function write(string $str)
    {
        if (!$this->isLive()) {
            throw new \RuntimeException('Cannot write to a pending connection.');
        }

        $str = $str . "\x00";
        $bytes_written = socket_write($this->socket, $str);

        if ($bytes_written !== strlen($str)) {
            throw new \RuntimeException('This is a TODO, you are welcome!');
        }
    }

    public function read(string &$error = null): ?string
    {
        if (!$this->isLive()) {
            throw new \RuntimeException('Cannot read of a pending connection.');
        }

        $msg_len = '';
        $char = '';

        do {
            $msg_len .= $char;
            $len = socket_recv($this->socket, $char, 1, MSG_DONTWAIT);

            if ($len != 1) {
                throw new \RuntimeException('Xdebug client just left.');
            }

        } while ($char !== "\x00");

        $len = socket_recv($this->socket, $str, (int) $msg_len, MSG_DONTWAIT);

        // Trailing "\x00" consumed.
        $len = socket_recv($this->socket, $_, 1, MSG_DONTWAIT);

        return $str;
    }
}
