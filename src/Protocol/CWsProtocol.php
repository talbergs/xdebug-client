<?php declare(strict_types=1);

namespace Acme\Protocol;

use Acme\Exceptions\EConnectionBroke;
use Acme\Log;


/**
 * Base Framing Protocol

          0                   1                   2                   3
      0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1
     +-+-+-+-+-------+-+-------------+-------------------------------+
     |F|R|R|R| opcode|M| Payload len |    Extended payload length    |
     |I|S|S|S|  (4)  |A|     (7)     |             (16/64)           |
     |N|V|V|V|       |S|             |   (if payload len==126/127)   |
     | |1|2|3|       |K|             |                               |
     +-+-+-+-+-------+-+-------------+ - - - - - - - - - - - - - - - +
     |     Extended payload length continued, if payload len == 127  |
     + - - - - - - - - - - - - - - - +-------------------------------+
     |                               |Masking-key, if MASK set to 1  |
     +-------------------------------+-------------------------------+
     | Masking-key (continued)       |          Payload Data         |
     +-------------------------------- - - - - - - - - - - - - - - - +
     :                     Payload Data continued ...                :
     + - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +
     |                     Payload Data continued ...                |
     +---------------------------------------------------------------+

 */
class CWsProtocol implements IProtocol
{
    const OPCODE_CONTINUATION_FRAME = 0;
    const OPCODE_TEXT_FRAME = 1;
    const OPCODE_BINARY_FRAME = 2;
    const OPCODE_CONNECTION_CLOSE_FRAME = 8;
    const OPCODE_PING_FRAME = 9;
    const OPCODE_PONG_FRAME = 10;

    public function read($resource): string
    {
        $len = socket_recv($resource, $byte1, 1, MSG_DONTWAIT);
        if ($byte1 === null) {
            throw new EConnectionBroke();
        }

        $opcode = ord($byte1) & 0b00001111;
        if ($opcode === self::OPCODE_CONNECTION_CLOSE_FRAME) {
            throw new EConnectionBroke();
        }

        if ($opcode !== self::OPCODE_TEXT_FRAME) {
            throw new \RuntimeException("Not implemented opcode: ${opcode}");
        }

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

        $data = (string) $data;

        $data_unmasked = $data ^ str_pad('', $length, $masking_key, STR_PAD_RIGHT);
        $data_unmasked = (string) $data_unmasked;

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
    }
}
