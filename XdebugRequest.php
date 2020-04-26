<?php

class XdebugRequest
{
    static function buffer($stream): string
    {
        $len = stream_get_line($stream, 1024, "\x00");
        $res = fread($stream, (int) $len);
        fgetc($stream); // consume trailing \x00

        return $res;
    }

    static function fromStream($stream): XdebugRequest
    {
        $request = new self;

        self::buffer($stream);

        return $request;
    }

    public function processBuffer(string $req)
    {
        $xml = simplexml_load_string($req);

        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        switch ($xml->getName()) {
        case 'init': return InitRequest::fromXML($xml);
        default: throw new \Exception("{$xml->getName()} < unrecognized request name.");
        }
    }
}
