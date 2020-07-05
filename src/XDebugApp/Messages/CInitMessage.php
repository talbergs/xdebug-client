<?php declare(strict_types=1);

namespace Acme\XDebugApp\Messages;


class CInitMessage implements IMessage
{
    public $fileuri;
    public $idekey;
    public $engine_version;
    public $protocol_version;
    public $appid;
    public $language;

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
}
