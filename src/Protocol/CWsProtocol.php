<?php declare(strict_types=1);

namespace Acme\Protocol;

use Acme\Log;


class CWsProtocol implements IProtocol
{
    public function read($resource): string
    {
        $len = socket_recv($resource, $byte1, 1, MSG_DONTWAIT);
        if ($byte1 === null) {
            return '';
        }

        /* d((ord($byte1) & 0b1), '<< IF IS FIN'); */

        // TODO: mask flag ... final flag .. opcode flag
        $len = socket_recv($resource, $byte2, 1, MSG_DONTWAIT);
        if ($byte2 === null) {
            return '';
        }

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
        $b = 129; // FIN + text frame
		$len = strlen($text);
		if ($len < 126) {
			return pack('CC', $b, $len) . $text;
		} else if ($len < 65536) {
			return pack('CCn', $b, 126, $len) . $text;
		} else {
			return pack('CCNN', $b, 127, 0, $len) . $text;
		}
    }

    public function write($resource, string $str)
    {
        Log::log(__CLASS__.':'.__FUNCTION__);
        Log::log($str);

        $str = self::encode($str);
        $bytes_written = socket_write($resource, $str);
        if ($bytes_written === false) {
            throw new \RuntimeException('Network error or what..');
        }

        if ($bytes_written !== strlen($str)) {
            throw new \RuntimeException('This is a TODO, you are welcome!');
        }
        d('DONE?');
    }
}
