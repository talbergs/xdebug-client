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
    public $transactions_id = 0;

    public bool $is_accepted;

    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
        $this->is_accepted = false; 
        $this->store = new SplObjectStorage;
    }

    public $transaction_id_to_session = [];

    public function commit(XDebugSession $session)
    {
        foreach ($session->transactions as $line) {
            $this->transactions_id ++;
            $line->setId($this->transactions_id);
            $this->connection->write((string) $line);
            $this->transaction_id_to_session[$this->transactions_id] = $session;
        }
    }
    

    /**
     * undocumented function
     *
     * @return void
     */
    public function findOrCreateSession(string $idekey): XDebugSession
    {
        if (!$this->hasSession($idekey)) {
            $this->sessions[$idekey] = new XDebugSession($idekey);
        }

        return $this->sessions[$idekey];
    }

    public function accept(): IConnection
    {
        $this->connection = $this->connection->accept();
        $this->is_accepted = true;

        return $this->connection;
    }

    public function isAccepted(): bool
    {
        return $this->is_accepted;
    }

    public function getSession(string $idekey): XDebugSession
    {
        $session = $this->sessions[$idekey];

        return $session;
    }

    public function hasSession(string $idekey): bool
    {
        return array_key_exists($idekey, $this->sessions);
    }
}
