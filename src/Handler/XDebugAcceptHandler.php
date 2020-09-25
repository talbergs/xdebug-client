<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\Device;
use Acme\Device\IDevice;
use Acme\Hub;
use Acme\XDebugApp\XDebugSessionBag;

class XDebugAcceptHandler implements IHandler
{
    protected XDebugSessionBag $session;

    public function __construct(XDebugSessionBag $sess)
    {
        $this->session = $sess;
    }

    /**
     * Accept connections from xDebug server if needed.
     */
    public function handle(IDevice $device, Hub $hub)
    {
        info("Server attempts to connect on {$this->session->connection}.");
        debug("@device {$device}.");

        // TODO this "accepted" must become false when last subsession device is removed.
        // TODO: REFCOUNT
        // $this->session is actually session_bag
        // that has refcount of sessions
        if (!$this->session->connection->isAccepted()) {
            $conn = $this->session->accept();

            $han = new XDebugSessionHandler($this->session);
            $device = new Device($conn, $han);
            $hub->add($device);
            info("Connect accepted on {$this->session->connection}.");
            debug("@device {$device}.");
        } else {
            $conn = $device->getConnection()->accept();

            $han = new XDebugSessionHandler($this->session);
            $device = new Device($conn, $han);
            $hub->add($device);

            info("Reusing connection on {$this->session->connection}.");
            debug("@device {$device}.");
        }
    }
}
