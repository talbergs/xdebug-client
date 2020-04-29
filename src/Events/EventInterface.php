<?php declare(strict_types=1);

namespace Acme\Events;

use Acme\Connection\ConnectionHub;

interface EventInterface
{
    public function execute(ConnectionHub $hub);
}

