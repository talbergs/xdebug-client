<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

class CWsProtocol implements IProtocol
{
    public function read($resource): string
    {
    }

    public function write($resource, string $str)
    {
    }
}
