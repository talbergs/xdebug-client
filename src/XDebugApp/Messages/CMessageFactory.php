<?php declare(strict_types=1);

namespace Acme\XDebugApp\Messages;


class CMessageFactory
{
    public static function fromXMLString(string $str): IMessage
    {
        $xml = simplexml_load_string($str);

        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        switch ($xml->getName()) {
        # https://xdebug.org/docs/dbgp#connection-initialization
        case 'init':
            return new CInitMessage(
                (string) $xml->xpath('/a:init/@fileuri')[0],
                (string) $xml->xpath('/a:init/@idekey')[0],
                (string) $xml->xpath('/a:init/a:engine/@version')[0],
                (string) $xml->xpath('/a:init/@protocol_version')[0],
                (string) $xml->xpath('/a:init/@appid')[0],
                (string) $xml->xpath('/a:init/@language')[0]
            );
        # https://xdebug.org/docs/dbgp#response
        case 'response':
            $err_code = $xml->xpath('/a:response/a:error/@code');
            $err_message = $xml->xpath('/a:response/a:error/a:message');
            $command = $xml->xpath('/a:response/@command');
            $encoding = $xml->xpath('/a:response/@encoding');
            $transaction_id = $xml->xpath('/a:response/@transaction_id');
            $contents = $xml->xpath('/a:response');

            return new CResponseMessage(
                (string) reset($err_code),
                (string) reset($err_message),
                (string) reset($command),
                (string) reset($encoding),
                (string) reset($transaction_id),
                (string) reset($contents),
            );
            break;
        # https://xdebug.org/docs/dbgp#stream
        case 'stream':
            throw new \Exception("'{$xml->getName()}' < not implemented.");
        # https://xdebug.org/docs/dbgp#notify
        case 'notify':
            throw new \Exception("'{$xml->getName()}' < not implemented.");
        }
    }
}
