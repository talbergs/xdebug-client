<?php

class HTTPRequestHeaderBag
{
    public array $headers;

    static function fromStream($stream): HTTPRequestHeaderBag
    {
        $bag = new self;

        while ($line = fgets($stream)) {
            if ($line === "\r\n") break;

            if ($header = HTTPRequestHeader::fromString($line)) {
                $bag->headers[$header->name] = $header->value;
            }
        }

        return $bag;
    }

    public function get(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
}
