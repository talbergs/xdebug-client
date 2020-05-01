<?php declare(strict_types=1);

namespace Acme\Connection;


class ConnectionWs implements ConnectionInterface
{
    use ConnectionTrait;

    public static function fromInet(ConnectionInet $conn): self
    {
        return new self('ws', $conn->getSocket(), true);
    }

    public function write(string $str)
    {
        if (!$this->isLive()) {
            throw new \RuntimeException('Cannot write to a pending connection.');
        }

        // 0x1 text frame (FIN + opcode)
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($str);

        if($length <= 125)      $header = pack('CC', $b1, $length);     elseif($length > 125 && $length < 65536)        $header = pack('CCS', $b1, 126, $length);   elseif($length >= 65536)
            $header = pack('CCN', $b1, 127, $length);

        $str = $header.$str;
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


        $len = socket_recv($this->socket, $byte1, 1, MSG_DONTWAIT);
        // TODO: mask flag ... final flag .. opcode flag
        $len = socket_recv($this->socket, $byte2, 1, MSG_DONTWAIT);

        $length = ord($byte2) & 127;
        if ($length == 126) {
            $len = socket_recv($this->socket, $length, 2, MSG_DONTWAIT);
            $length = unpack('nu16', $length)['u16'];
        } else if ($length == 127) {
            $len = socket_recv($this->socket, $length, 8, MSG_DONTWAIT);
            $length = unpack('Ju64', $length)['u64'];
        }

        $len = socket_recv($this->socket, $masking_key, 4, MSG_DONTWAIT);
        $len = socket_recv($this->socket, $data, $length, MSG_DONTWAIT);

        $data_unmasked = $data ^ str_pad('', $length, $masking_key, STR_PAD_RIGHT);

        return $data_unmasked;
    }
}
