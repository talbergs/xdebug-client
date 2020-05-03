<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

interface IConnection
{
    public function read(): string;
    public function write(string $str);
    public function setProtocol(IProtocol $protocol);
}
