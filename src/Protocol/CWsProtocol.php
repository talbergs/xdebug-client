<?php declare(strict_types=1);

namespace Acme\Protocol;

use Acme\Log;


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
        $data_unmasked = (string) $data_unmasked;

        Log::log(__CLASS__.':'.__FUNCTION__);
        Log::log($data_unmasked);

        return (string) $data_unmasked;
    }

    public static function encode($text): string
    {
        // 0x1 text frame (FIN + opcode)
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if($length <= 125)      $header = pack('CC', $b1, $length);     elseif($length > 125 && $length < 65536)        $header = pack('CCS', $b1, 126, $length);   elseif($length >= 65536)
            $header = pack('CCN', $b1, 127, $length);

        return $header.$text;
    }

    public function write($resource, string $str)
    {
        Log::log(__CLASS__.':'.__FUNCTION__);
        Log::log($str);

        $bytes_written = socket_write($resource, self::encode($str));

        if ($bytes_written !== strlen($str)) {
            throw new \RuntimeException('This is a TODO, you are welcome!');
        }
    }
}
