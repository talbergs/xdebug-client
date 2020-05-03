<?php declare(strict_types=1);

namespace Acme\Events;

use Acme\Connection\ConnectionInterface;
use Acme\Connection\ConnectionHub;

class SwitchConnectionEvent implements EventInterface
{
    public function __construct(ConnectionInterface $from, ConnectionInterface $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function execute(ConnectionHub $hub)
    {
        $hub->switch($this->from, $this->to);
        d(__CLASS__ . "<Switched>", $this->from, $this->to);
    }
}

