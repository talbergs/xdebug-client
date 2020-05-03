<?php declare(strict_types=1);

namespace Acme\Protocol;


class CWsProtocol implements IProtocol
{
    public function read($resource): string
    {
        $len = socket_recv($resource, $byte1, 1, MSG_DONTWAIT);
        // TODO: mask flag ... final flag .. opcode flag
        $len = socket_recv($resource, $byte2, 1, MSG_DONTWAIT);

        $length = ord($byte2) & 127;
        if ($length == 126) {
            $len = socket_recv($resource, $length, 2, MSG_DONTWAIT);
            $length = unpack('nu16', $length)['u16'];
        } else if ($length == 127) {
            $len = socket_recv($resource, $length, 8, MSG_DONTWAIT);
            $length = unpack('Ju64', $length)['u64'];
        }

        $len = socket_recv($resource, $masking_key, 4, MSG_DONTWAIT);
        $len = socket_recv($resource, $data, $length, MSG_DONTWAIT);

        $data_unmasked = $data ^ str_pad('', $length, $masking_key, STR_PAD_RIGHT);

        return (string) $data_unmasked;
    }

    public function write($resource, string $str)
    {
    }
}
