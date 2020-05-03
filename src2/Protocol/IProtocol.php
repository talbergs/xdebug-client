<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

interface Protocol
{
    public function read($resource): string;
    public function write($resource, string $str);
}
