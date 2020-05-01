<?php declare(strict_types=1);

namespace Acme\HTTP;

class HTTPResponse
{
    public string $status_line;
    public string $body = '';
    public int $status_code = 200;
    public array $headers = [];

    static function handShakeResponse(HTTPRequest $req): HTTPResponse
    {
        $res = new self;

        $res->setStatusCode(101);
        $res->setHeader('upgrade', 'websocket');
        $res->setHeader('connection', 'upgrade');

        $key = $req->getHeader('sec-websocket-key');
        $acc = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        $acc = base64_encode(sha1($acc, true));
        $res->setHeader('sec-websocket-accept', $acc);

        return $res;
    }

    public function setStatusCode(int $code)
    {
        $this->status_code = $code;
    }

    public function setHeader(string $name, string $value)
    {
        $this->headers[] = "$name: $value";
    }

    public function setBody(string $body)
    {
        $this->body = $body;
    }

    public function __toString(): string
    {
        if ($this->body) {
            return implode("\r\n", [
                $this->renderStatusLine(),
                $this->renderHeaderFields(),
                $this->body,
            ]);
        } else {
            return implode("\r\n", [
                $this->renderStatusLine(),
                $this->renderHeaderFields(),
            ]) . "\r\n";
        }
    }

    public function renderStatusLine(): string
    {
        $message = [
            101 => 'Switching Protocols',
            200 => 'OK',
        ][$this->status_code] ?? '';

        return "HTTP/1.1 {$this->status_code} {$message}";
    }

    public function renderHeaderFields(): string
    {
        return implode("\r\n", $this->headers);
    }
}
