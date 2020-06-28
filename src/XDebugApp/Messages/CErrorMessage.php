<?php declare(strict_types=1);

namespace Acme\XDebugApp\Messages;


class CErrorMessage implements IMessage
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

    public static function fromXML(\SimpleXMLElement $xml): self
    {
        return new self(
            (string) $xml->xpath('/a:init/@fileuri')[0],
            (string) $xml->xpath('/a:init/@idekey')[0],
            (string) $xml->xpath('/a:init/a:engine/@version')[0],
            (string) $xml->xpath('/a:init/@protocol_version')[0],
            (string) $xml->xpath('/a:init/@appid')[0],
            (string) $xml->xpath('/a:init/@language')[0]
        );
    }

    public static function fromXMLString($str): self
    {
        $xml = simplexml_load_string($str);

        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        return self::fromXML($xml);
    }
}
