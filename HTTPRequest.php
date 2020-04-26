<?php

class HTTPRequest
{
    public string               $method;
    public string               $url;
    public HTTPRequestHeaderBag $headers;
    public ?string              $body;

    static function fromStream($stream): HTTPRequest
    {
        $request = new self;
        list($request->method, $request->url) = explode(' ', fgets($stream));
        $request->headers = HTTPRequestHeaderBag::fromStream($stream);

        /* $request->body = stream_get_contents($stream); */

        return $request;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers->get($name);
    }

    public function isWebSocketHandshake(): bool
    {
        return $this->headers->get('upgrade') === 'websocket';
    }
}
