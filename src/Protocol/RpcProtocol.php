<?php declare(strict_types=1);

namespace Acme\Protocol;

class RpcProtocol implements IProtocol
{
    public function read($resource): string
    {
        $buf = '';
        $chunk = 1024;
        $chunk_recv = 0;

        do {
            $chunk_recv = socket_recv($resource, $tmp_buf, $chunk, MSG_DONTWAIT);
            $buf .= $tmp_buf;
        } while($chunk_recv);

        return $buf;
    }

    public function write($resource, string $str)
    {
        $bytes_written = socket_write($resource, $str);

        if ($bytes_written !== strlen($str)) {
            throw new \RuntimeException('This is a TODO, you are welcome!');
        }
    }
}
