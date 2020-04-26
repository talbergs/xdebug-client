<?php

class HTTPRequestHeader
{
    public string $name;
    public string $value;

    public static array $supported_headers = [
        'sec-websocket-key',
        'upgrade',
        'accept',
    ];

    static function fromString(string $line): ?HTTPRequestHeader
    {
        $header = new self;

        list($name, $value) = explode(':', $line);

        $header->name = strtolower($name);
        $header->value = trim($value);

        return in_array($header->name, self::$supported_headers)
            ? $header
            : null;
    }
}
