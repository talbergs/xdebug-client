<?php declare(strict_types=1);

namespace Acme\XDebugApp;

use Acme\Device\IDevice;

class XDebugApp
{
    protected $transactions = [];
    protected $transaction_id = 0;
    private $fileuri;
    private $ideke;
    private $engine_version;
    private $protocol_version;
    private $appid;
    private $language;
    private $handler;
    private Idevice $device;

    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function onInit(\SimpleXMLElement $xml)
    {
        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        $this->fileuri = (string) $xml->xpath('/a:init/@fileuri')[0];
        $this->ideke = (string) $xml->xpath('/a:init/@idekey')[0];
        $this->engine_version = (string) $xml->xpath('/a:init/a:engine/@version')[0];
        $this->protocol_version = (string) $xml->xpath('/a:init/@protocol_version')[0];
        $this->appid = (string) $xml->xpath('/a:init/@appid')[0];
        $this->language = (string) $xml->xpath('/a:init/@language')[0];
    }

    public function onResponse(\SimpleXMLElement $xml)
    {
        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        d($this->transactions[$xml->attributes()->transaction_id . ''], "<< response on", $this, $xml);
        unset($this->transactions[$xml->attributes()->transaction_id . '']);
    }

    # https://xdebug.org/docs/dbgp#status
    public function cmdFeatureSet(string $feature_name, string $value)
    {
        $possible_names = [
            'encoding',
            'multiple_sessions',
            'max_children',
            'max_data',
            'max_depth',
            'extended_properties',
            // The following features strings MAY be available, if they are not, the IDE should assume that the feature is not available:
            'show_hidden',
            'notify_ok',
        ];

        $this->transactions[$this->transaction_id] = $this->cmd('feature_set', ['-n', $feature_name, '-v', $value]);
    }

    # https://xdebug.org/docs/dbgp#status
    public function cmdFeatureGet(string $feature_name)
    {
        /*
            <?xml version="1.0" encoding="iso-8859-1"?>
            <response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="https://xdebug.org/dbgp/xdebug" command="feature_get" transaction_id="0" feature_name="max_data" supported="1"><![CDATA[1024]]></response>

            <?xml version="1.0" encoding="iso-8859-1"?>
            <response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="https://xdebug.org/dbgp/xdebug" command="feature_get" transaction_id="1" feature_name="max_data2" supported="0"><![CDATA[0]]></response>
         */

        $possible_names = [
            'language_supports_threads',
            'language_name',
            'language_version',
            'encoding',
            'protocol_version',
            'supports_async',
            'data_encoding',
            'breakpoint_languages',
            'breakpoint_types',
            'resolved_breakpoints',
            'multiple_sessions',
            'max_children',
            'max_data',
            'max_depth',
            'extended_properties',
            // The following features strings MAY be available, if they are not, the IDE should assume that the feature is not available:
            'supported_encodings',
            'supports_postmortem',
            'show_hidden',
            'notify_ok',
        ];

        $transaction = $this->cmd('feature_get', ['-n', $feature_name]);
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===    continuation commands  ========*/
    /*======================================*/
    /* starts or resumes the script until a new breakpoint is reached, or the end of the script is reached. */
    public function cmdRun()
    {
        $transaction = $this->cmd('run');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /* steps to the next statement, if there is a function call involved it will break on the first statement in that function */
    public function cmdStepInto()
    {
        $transaction = $this->cmd('step_into');
        $this->transactions[$transaction->getId()] = $transaction;
    }
    
    /* steps to the next statement, if there is a function call on the line from which the step_over is issued then the debugger engine will stop at the statement after the function call in the same scope as from where the command was issued */
    public function cmdStepOver()
    {
        $transaction = $this->cmd('step_over');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /* steps out of the current scope and breaks on the statement after returning from the current function. (Also called 'finish' in GDB) */
    public function cmdStepOut()
    {
        $transaction = $this->cmd('step_out');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /* ends execution of the script immediately, the debugger engine may not respond, though if possible should be designed to do so. The script will be terminated right away and be followed by a disconnection of the network connection from the IDE (and debugger engine if required in multi request apache processes). */
    public function cmdStop()
    {
        $transaction = $this->cmd('stop');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /* (optional): stops interaction with the debugger engine. Once this command is executed, the IDE will no longer be able to communicate with the debugger engine. This does not end execution of the script as does the stop command above, but rather detaches from debugging. Support of this continuation command is optional, and the IDE should verify support for it via the feature_get command. If the IDE has created stdin/stdout/stderr pipes for execution of the script (eg. an interactive shell or other console to catch script output), it should keep those open and usable by the process until the process has terminated normally. */
    public function cmdDetach()
    {
        $transaction = $this->cmd('detatch');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===   BREAKPOINTS       ========*/
    /*======================================*/

    public function cmdBreakpointSet()
    {
        $transaction = $this->cmd('breakpoint_set');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdBreakpointGet()
    {
        $transaction = $this->cmd('breakpoint_get');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdBreakpointUpdate()
    {
        $transaction = $this->cmd('breakpoint_update');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdBreakpointRemove()
    {
        $transaction = $this->cmd('breakpoint_remove');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdBreakpointList()
    {
        $transaction = $this->cmd('breakpoint_list');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===         stack           ========*/
    /*======================================*/
    public function cmdStackDepth()
    {
        $transaction = $this->cmd('stack_depth');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdStackGet()
    {
        $transaction = $this->cmd('stack_get');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===         context           ========*/
    /*======================================*/
    public function cmdContextNames()
    {
        $transaction = $this->cmd('context_names');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdContextGet()
    {
        $transaction = $this->cmd('context_get');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===         types           ========*/
    /*======================================*/
    public function cmdTypemapGet()
    {
        $transaction = $this->cmd('typemap_get');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===         property           ========*/
    /*======================================*/
    public function cmdPropertyGet()
    {
        $transaction = $this->cmd('property_get');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdPropertySet()
    {
        $transaction = $this->cmd('property_set');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    public function cmdPropertyValue()
    {
        $transaction = $this->cmd('property_value');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===         source           ========*/
    /*======================================*/
    public function cmdSource()
    {
        $transaction = $this->cmd('source');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===         break           ========*/
    /*======================================*/
    public function cmdBreak()
    {
        $transaction = $this->cmd('break');
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /*======================================*/
    /*===         spawnpoints           ========*/
    /*======================================*/
    /*======================================*/
    /*===         notifications           ========*/
    /*======================================*/
    /*======================================*/
    /*===         REPL interact..           ========*/
    /*======================================*/
    

    /*======================================*/
    /*===                           ========*/
    /*======================================*/

    public function setDevice($device)
    {
        $this->device = $device;
    }

    public function commit()
    {
        foreach ($this->transactions as $line) {
            $this->device->getConnection()->write((string) $line);
        }
    }

    protected function cmd(string $cmd, array $args = []): XDebugTransaction
    {
        $transaction = new XDebugTransaction();
        $transaction->setId($this->transaction_id);

        $transaction->setCommad($cmd);
        $transaction->setArgs($args);

        $this->transaction_id ++;

        return $transaction;
    }
}
