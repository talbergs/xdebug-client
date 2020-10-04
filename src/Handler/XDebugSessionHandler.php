<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\XDebugSessionNotFound;
use Acme\Hub;
use Acme\UI\Messages\CUIAppStateMessage;
use Acme\XDebugApp\Messages\CInitMessage;
use Acme\XDebugApp\Messages\CMessageFactory;
use Acme\XDebugApp\Messages\CResponseMessage;
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
        if (!$this->session_bag->hasSession($message->idekey)) {
            // TODO: remove from session_bag (because of ref-counter)
            throw new XDebugSessionNotFound();
        }

        $session = $this->session_bag->getSession($message->idekey);

        $session->state = "initalizing";
        $session->onInit($message);

        $session->cmdTypemapGet();
        $session->cmdBreakpointList();


        // Set project breakpoints
        /* $this->session->cmdBreakpointSet(); */

        // TODO: read config from GLOBAL state (allow to update negotiation during session runtime).
        // Set project configuration features
        $session->cmdFeatureSet('max_depth', '9');
        $session->cmdFeatureSet('max_children', '9');
        $session->cmdFeatureSet('max_data', '9');

        // Breakpoint list - reassure breakpoints are set
        $session->cmdBreakpointList();

        // If "NOT BREAK ON FIRST LINE IS configured" and there are breakpoints , then -> RUN !
        /* $session->cmdRun(); */
        // ELSE synthetic "STEP INTO"
        $session->cmdStepInto();
        $session->cmdBreakpointSet('file:///var/www/html/index.php', 6);
        $session->cmdBreakpointSet('file:///var/www/html/index.php', 8);
        $session->cmdBreakpointList();

        $this->session_bag->commit($session);
    }

    public function handle(IDevice $device, Hub $hub)
    {
        $conn = $device->getConnection();
        $str = $conn->read();

        $imessage = CMessageFactory::fromXMLString($str);
        switch (get_class($imessage)) {
        case CInitMessage::class:
            info("Server attempts to initalize session using idekey: '{$imessage->idekey}'");
            $this->handleInit($imessage);
            info("Success on '{$imessage->idekey}'");
            break;
        case CResponseMessage::class:
            /** @var XDebugSession $session */
            $session = $this->session_bag->transaction_id_to_session[$imessage->transaction_id];
            /** @var CResponseMessage $imessage */

            info("Server responds to transaction");
            switch ($imessage->command) {
            case 'step_into':
                $status = (string) $imessage->xml->attributes()['status'];
                $reason = (string) $imessage->xml->attributes()['reason'];
                $filename = (string) $imessage->xml->xpath('/a:response/*')[0]->attributes()['filename'];
                $lineno = (string) $imessage->xml->xpath('/a:response/*')[0]->attributes()['lineno'];
                break;
            case 'breakpoint_set':
                $breakpoint_id = $imessage->xml->attributes()['id'];
                // TODO: ^ use that ID
                break;
            case 'feature_set':
                [$feature, $success] = $imessage->readFeatureSetResponse();
                d($feature, $success);
                break;
            case 'breakpoint_list':
                $session->setBreakpoints($imessage->readBreakpoints());
                break;
            case 'typemap_get':
                $session->setTypemap($imessage->readTypemap());
                break;
            default:
                d($imessage);
                info("$imessage->command <- no command handler defined.");
            }

            break;
        default:
            d("returning");
            return;
        }

        // quick refresh
        (new CUIAppStateMessage)->actOn($hub);

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

        // what do we got??
        d($this->session_bag);

        sleep(4);
        //
        $this->session_bag->commit();
    }
}
