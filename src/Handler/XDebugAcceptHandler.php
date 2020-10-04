<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\Device;
use Acme\Device\IDevice;
use Acme\Hub;
use Acme\XDebugApp\XDebugSessionBag;

class XDebugAcceptHandler implements IHandler
{
    protected XDebugSessionBag $session_bag;

    public function __construct(XDebugSessionBag $session_bag)
    {
        $this->session_bag = $session_bag;
    }

    /**
     * Accept connections from xDebug server if needed.
     */
    public function handle(IDevice $device, Hub $hub)
    {
        info("Accept handler!");
        // TODO this "accepted" must become false when last subsession device is removed.
        // TODO: REFCOUNT
        // that has refcount of sessions
        if (!$this->session_bag->isAccepted()) {
            info("ssssssssssss");
            $conn = $this->session_bag->accept();

            $han = new XDebugSessionHandler($this->session_bag);
            $device = new Device($conn, $han);
            $hub->add($device);
            info("Connect accepted on {$this->session_bag->connection}.");
            debug("@device {$device}.");
        } else {
            info("rrrrrrrrrrrr");
            $conn = $device->getConnection()->accept();

            $han = new XDebugSessionHandler($this->session_bag);
            $device = new Device($conn, $han);
            $hub->add($device);

            info("Reusing connection on {$this->session_bag->connection}.");
            debug("@device {$device}.");
        }
    }
}
