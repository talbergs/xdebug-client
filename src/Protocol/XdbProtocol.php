<?php declare(strict_types=1);

namespace Acme\Protocol;

use Acme\Log;

class XdbProtocol implements IProtocol
{
    public function read($resource): string
    {
        $msg_len = '';
        $char = '';

        do {
            $msg_len .= $char;
            $len = socket_recv($resource, $char, 1, MSG_DONTWAIT);

            if ($len != 1) {
                throw new \RuntimeException('Xdebug client just left.');
            }

        } while ($char !== "\x00");

        $len = socket_recv($resource, $str, (int) $msg_len, MSG_DONTWAIT);

        // Trailing "\x00" consumed.
        $len = socket_recv($resource, $_, 1, MSG_DONTWAIT);

        Log::log(__CLASS__.':'.__FUNCTION__);
        Log::log($str);

        return $str;
    }

    public function write($resource, string $str)
    {
        Log::log(__CLASS__.':'.__FUNCTION__);
        Log::log($str);

        $str = $str . "\x00";
        $bytes_written = socket_write($resource, $str);

        if ($bytes_written !== strlen($str)) {
            throw new \RuntimeException('This is a TODO, you are welcome!');
        }
    }
}
