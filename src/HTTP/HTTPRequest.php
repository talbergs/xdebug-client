<?php declare(strict_types=1);

namespace Acme\HTTP;

class HTTPRequest
{
    public string $method;
    public string $url;
    public array $headers;
    public string $body;

    static function fromString(string $raw): HTTPRequest
    {
        $request = new self;
        $lines = explode("\r\n", $raw);

        list(
            $request->method,
            $request->url
        ) = explode(' ', array_shift($lines));

        while ($header = array_shift($lines)) {
            if ($header === "\r\n") {
                break;
            }

            list($name, $value) = explode(':', $header);

            $request->headers[strtolower($name)] = ltrim($value);
        }

        $request->body = implode(PHP_EOL, $lines);

        return $request;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getIndexFilePath(): string
    {
        return 'public/index.html';
    }

    public function getFilePath(): string
    {
        return 'public' . $this->url;
    }

    public function isIndexRequest(): bool
    {
        return $this->url === '/';
    }

    public function isFileRequest(): bool
    {
        return is_file($this->getFilePath());
    }

    public function isWebSocketHandshake(): bool
    {
        return $this->getHeader('upgrade') === 'websocket';
    }
}
