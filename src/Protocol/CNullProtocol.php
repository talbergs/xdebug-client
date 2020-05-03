<?php declare(strict_types=1);

namespace Acme\Protocol;

class CNullProtocol implements IProtocol
{
    public function read($resource): string
    {
        return '';
    }

    public function write($resource, string $str)
    {
    }
}
