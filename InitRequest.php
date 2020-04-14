<?php

class InitRequest
{
    public function __construct(
        string $fileuri,
        string $idekey,
        string $engine_version,
        string $protocol_version,
        string $appid,
        string $language
    ) {
        $this->fileuri = $fileuri;
        $this->idekey = $idekey;
        $this->engine_version = $engine_version;
        $this->protocol_version = $protocol_version;
        $this->appid = $appid;
        $this->language = $language;
    }

    public static function fromXMLString($str)
    {
        $xml = simplexml_load_string($str);

        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        return new self(
            $xml->xpath('/a:init/@fileuri')[0],
            $xml->xpath('/a:init/@idekey')[0],
            $xml->xpath('/a:init/a:engine/@version')[0],
            $xml->xpath('/a:init/@protocol_version')[0],
            $xml->xpath('/a:init/@appid')[0],
            $xml->xpath('/a:init/@language')[0]
        );
    }
}
