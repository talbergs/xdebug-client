<?php declare(strict_types=1);

namespace Acme\Events;

use Acme\Connection\ConnectionInterface;
use Acme\Connection\ConnectionHub;

class CloseConnectionEvent implements EventInterface
{
    public function __construct(ConnectionInterface $conn)
    {
        $this->connection = $conn;
    }

    public function execute(ConnectionHub $hub)
    {
        $hub->drop($this->connection);
        d(__CLASS__ . "<Dropped>", $this->connection);
    }
}

