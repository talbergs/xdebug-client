<?php declare(strict_types=1);

namespace Acme\Protocol;

interface IProtocol
{
    public function read($resource): string;
    public function write($resource, string $str);
}
