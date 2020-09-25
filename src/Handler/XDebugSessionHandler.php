<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\XDebugSessionNotFound;
use Acme\Hub;
use Acme\XDebugApp\Messages\CInitMessage;
use Acme\XDebugApp\Messages\CMessageFactory;
use Acme\XDebugApp\XDebugSessionBag;

class XDebugSessionHandler implements IHandler
{
    protected XDebugSessionBag $session_bag;
    protected $transactions = [];
    protected $transaction_id = 0;
    
    public function __construct(XDebugSessionBag $sess)
    {
        $this->session_bag = $sess;
    }

    public function handleInit(CInitMessage $message)
    {
        if ($message->idekey !== $this->session_bag->idekey) {
            // TODO: remove from session_bag (because of ref-counter)
            throw new XDebugSessionNotFound();
        }

        $this->session_bag->cmdTypemapGet();
        $this->session_bag->commit();
    }

    public function handle(IDevice $device, Hub $hub)
    {
        $conn = $device->getConnection();
        $str = $conn->read();

        $imessage = CMessageFactory::fromXMLString($str);
        d($imessage);
        switch (get_class($imessage)) {
        case CInitMessage::class:
            info("Server attempts to initalize session using idekey: '{$imessage->idekey}'");
            $this->handleInit($imessage);
            info("Success on '{$imessage->idekey}'");
            break;
        case CResponseMessage::class:
            info("Server responds to transaction");
            d($imessage, $str);
            break;
        default:
            d("returning");
            return;
        }

        return;

        if ($this->session_bag->state !== "starting") {
            d("returning");
            return;
            throw new \RuntimeException("Session cannot accept another connection in state($this->session_bag->state)");
        }

        /* if ($this->session->idekey === '') { */
            // TODO;
            // If idekey is not specified, we accept any init request, as long as
            // none of device sessions are in progress yet.
        /* } */

        if ($imessage->idekey !== $this->session_bag->idekey) {
            throw new XDebugSessionNotFound();
        }

        $this->session_bag->state = "running";

        info("Accepted session, negotiating features now...");

        // Loopback device session will write back to (and read from also..).
        /* $this->session->setDevice($device); */

        // Bootsrap project
        $this->session_bag->cmdTypemapGet();
        $this->session_bag->commit();
        return;

        // Set project breakpoints
        /* $this->session->cmdBreakpointSet(); */

        // TODO: read config from GLOBAL state (allow to update negotiation during session runtime).
        // Set project configuration features
        $this->session_bag->cmdFeatureSet('max_depth', '9');
        $this->session_bag->cmdFeatureSet('max_children', '9');
        $this->session_bag->cmdFeatureSet('max_data', '9');

        // Breakpoint list - reassure breakpoints are set
        $this->session_bag->cmdBreakpointList();

        // If "NOT BREAK ON FIRST LINE IS configured" and there are breakpoints , then -> RUN !
        $this->session_bag->cmdRun();
        // ELSE synthetic "STEP INTO"
        $this->session_bag->cmdStepInto();

        // what do we got??
        d($this->session_bag);

        sleep(4);
        //
        $this->session_bag->commit();
    }
}
