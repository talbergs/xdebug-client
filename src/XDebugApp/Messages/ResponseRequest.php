<?php declare(strict_types=1);

namespace Acme\XDebugApp\Messages;


class ResponseRequest implements IMessage
{
    public function __construct(
        string $error_code,
        string $error_message,
        string $command,
        string $encoding,
        string $transaction_id,
        string $contents
    ) {
        $this->error_code = $error_code;
        $this->error_message = $error_message;
        $this->command = $command;
        $this->encoding = $encoding;
        $this->transaction_id = $transaction_id;
        $this->contents = $contents;
    }

    public static function fromXML(\SimpleXMLElement $xml): self
    {
        $err_code = $xml->xpath('/a:response/a:error/@code');
        $err_message = $xml->xpath('/a:response/a:error/a:message');
        $command = $xml->xpath('/a:response/@command');
        $encoding = $xml->xpath('/a:response/@encoding');
        $transaction_id = $xml->xpath('/a:response/@transaction_id');
        $contents = $xml->xpath('/a:response');

        return new self(
            (string) reset($err_code),
            (string) reset($err_message),
            (string) reset($command),
            (string) reset($encoding),
            (string) reset($transaction_id),
            (string) reset($contents),
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
