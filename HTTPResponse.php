<?php

class HTTPResponse
{
    public string $status_line;
    public string $body;
    public int $status_code;
    public array $headers = [];

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
        return implode("\r\n", [
            $this->renderStatusLine(),
            $this->renderHeaderFields(),
            $this->renderBody(),
        ]);
    }

    public function renderBody(): string
    {
        return $this->body ? $this->body : '';
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
