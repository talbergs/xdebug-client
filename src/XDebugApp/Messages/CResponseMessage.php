<?php declare(strict_types=1);

namespace Acme\XDebugApp\Messages;

use SimpleXMLElement;


class CResponseMessage implements IMessage
{
    public string $error_code;
    public string $error_message;
    public string $command;
    public string $encoding;
    public string $transaction_id;
    public string $contents;
    public SimpleXMLElement $xml;

    public function __construct(
        string $error_code,
        string $error_message,
        string $command,
        string $encoding,
        string $transaction_id,
        string $contents,
        SimpleXMLElement $xml
    ) {
        $this->error_code = $error_code;
        $this->error_message = $error_message;
        $this->command = $command;
        $this->encoding = $encoding;
        $this->transaction_id = $transaction_id;
        $this->contents = $contents;
        $this->xml = $xml;
    }

    public function readFeatureSetResponse(): array
    {
        return [
            (string) $this->xml->attributes()['feature'],
            (string) $this->xml->attributes()['success'] === '1',
        ];
    }

    public function readBreakpoints(): array
    {
        $breakpoints = [];

        foreach ($this->xml->xpath('/a:response/*') as $elem) {
            $breakpoints[] = [
                'id' => (string) $elem->attributes()['id'],
                'type' => (string) $elem->attributes()['type'],
                'filename' => (string) $elem->attributes()['filename'],
                'lineno' => (string) $elem->attributes()['lineno'],
                'state' => (string) $elem->attributes()['state'],
                'hit_count' => (string) $elem->attributes()['hit_count'],
                'hit_value' => (string) $elem->attributes()['hit_value'],
            ];
        }

        return $breakpoints;
    }

    public function readTypemap(): array
    {
        $types = [];

        foreach ($this->xml->xpath('/a:response/*') as $elem) {
            $types[] = [
                'name' => (string) $elem->attributes()['name'],
                'type' => (string) $elem->attributes()['type'],
            ];
        }

        return $types;
    }
}
