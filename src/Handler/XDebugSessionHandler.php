<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\XDebugSessionNotFound;
use Acme\Hub;
use Acme\XDebugApp\Messages\CInitMessage;
use Acme\XDebugApp\Messages\CMessageFactory;
use Acme\XDebugApp\XDebugSession;

class XDebugSessionHandler implements IHandler
{
    protected XDebugSession $session;
    protected $transactions = [];
    protected $transaction_id = 0;
    
    public function __construct(XDebugSession $sess)
    {
        $this->session = $sess;
    }

    public function handle(IDevice $device, Hub $hub)
    {
        $conn = $device->getConnection();
        $str = $conn->read();

        /** @var CInitMessage $imessage */
        $imessage = CMessageFactory::fromXMLString($str);
        d($imessage);
        info("XDebug server connecting to $device with idekey($imessage->idekey)");

        if ($this->session->state !== "starting") {
            d("returning");
            return;
            throw new \RuntimeException("Session cannot accept another connection in state($this->session->state)");
        }

        /* if ($this->session->idekey === '') { */
            // TODO;
            // If idekey is not specified, we accept any init request, as long as
            // none of device sessions are in progress yet.
        /* } */

        if ($imessage->idekey !== $this->session->idekey) {
            throw new XDebugSessionNotFound();
        }

        $this->session->state = "running";

        info("Accepted session, negotiating features now...");

        // Loopback device session will write back to (and read from also..).
        /* $this->session->setDevice($device); */

        // Bootsrap project
        $this->session->cmdTypemapGet();
        $this->session->commit();
        return;

        // Set project breakpoints
        /* $this->session->cmdBreakpointSet(); */

        // TODO: read config from GLOBAL state (allow to update negotiation during session runtime).
        // Set project configuration features
        $this->session->cmdFeatureSet('max_depth', '9');
        $this->session->cmdFeatureSet('max_children', '9');
        $this->session->cmdFeatureSet('max_data', '9');

        // Breakpoint list - reassure breakpoints are set
        $this->session->cmdBreakpointList();

        // If "NOT BREAK ON FIRST LINE IS configured" and there are breakpoints , then -> RUN !
        $this->session->cmdRun();
        // ELSE synthetic "STEP INTO"
        $this->session->cmdStepInto();

        // what do we got??
        d($this->session);

        sleep(4);
        //
        $this->session->commit();
    }
}
