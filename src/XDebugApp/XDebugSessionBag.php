<?php declare(strict_types=1);

namespace Acme\XDebugApp;

use Acme\Connection\IConnection;
use SplObjectStorage;

class XDebugSessionBag
{
    public IConnection $connection;
    public SplObjectStorage $store;

    // ide key => session
    public array $sessions = [];

    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
        $this->store = new SplObjectStorage;
    }
    /**
     * undocumented function
     *
     * @return void
     */
    public function findOrCreateSession(string $idekey)
    {
        return $this->sessions[$idekey] ?? new XDebugSession($this->connection);
    }
    
}
