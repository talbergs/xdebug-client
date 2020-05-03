<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

final class CConnection implements IConnection
{
    public function __construct($resource, IProtocol $protocol)
    {
        $this->resource = $resource;
        $this->protocol = $protocol;
    }

    public function read(): string
    {
        return $this->protocol->read($this->resource);
    }

    public function write(string $str)
    {
        $this->protocol->write($this->resource, $str);
    }

    public function setProtocol(IProtocol $protocol)
    {
        $this->protocol = $protocol;
    }
}
