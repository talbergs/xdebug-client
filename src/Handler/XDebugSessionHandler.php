<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\XDebugSessionNotFound;
use Acme\Hub;
use Acme\UI\Messages\CUIAppStateMessage;
use Acme\UI\Messages\CUIBreakpointListMessage;
use Acme\UI\Messages\CUIExitSessionMessage;
use Acme\UI\Messages\CUISourceMessage;
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
        $session->cmdBreakpointSet('file:///var/www/html/index.php', 5);
        $session->cmdBreakpointSet('file:///var/www/html/index2.php', 8);
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

                d('..', $status, $reason, '..');

                (new CUISourceMessage([
                    'sessionid' => spl_object_id($session),
                    'filename' => $filename,
                ]))->actOn($hub);
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
            case 'stop':
                // TODO;
                // remove session from session bag
                // remove connection if the last session, debugger engine would leave any way, do not make this unexpected.
                $status = (string) $imessage->xml->attributes()['status'];
                $reason = (string) $imessage->xml->attributes()['reason'];
                break;
            case 'stack_get':
                $session->setStack($imessage->readStackGet());
                break;
            case 'source':
                $session->source = explode(PHP_EOL, base64_decode((string) $imessage->xml));
                break;
            case 'step_over':
            case 'run':
                $run_status = (string) $imessage->xml->attributes()['status'];
                $reason = (string) $imessage->xml->attributes()['reason'];
                $filename = (string) $imessage->xml->xpath('//xdebug:message')[0]['filename'];
                $lineno = (string) $imessage->xml->xpath('//xdebug:message')[0]['lineno'];
                if ($run_status === 'break') {
                    (new CUIBreakpointListMessage([
                        'sessionid' => spl_object_id($session),
                    ]))->actOn($hub);

                    (new CUISourceMessage([
                        'sessionid' => spl_object_id($session),
                        'filename' => $filename,
                    ]))->actOn($hub);

                    $session->code_lineno = $lineno;
                } else if ($run_status === 'stopping') {
                    // if feature_get supports supports_postmortem we may interact with engine at this state
                    // for now we just always send stop request
                    (new CUIExitSessionMessage([
                        'sessionid' => spl_object_id($session),
                    ]))->actOn($hub);
                } else {
                    d($imessage);
                    info("^^ unknown RUN STATUS {$run_status}");
                }
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
    }
}
