<?php declare(strict_types=1);

namespace Acme\XDebugApp\Messages;


class CResponseMessage implements IMessage
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
}
