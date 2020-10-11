<?php declare(strict_types=1);

namespace Acme\XDebugApp;

use Acme\Connection\IConnection;
use Acme\XDebugApp\Messages\CInitMessage;

class XDebugSession
{
    public $transactions = [];
    public $test_responses = [];
    protected $callbacks = [];
    /* protected $transaction_id = 0; */
    public $fileuri;
    public $engine_version;
    public $protocol_version;
    public $appid;
    public $language;
    public string $idekey;

    public array $typemap = [];
    public array $breakpoints = [];

    public string $state = 'starting';

    public function __construct(string $idekey)
    {
        $this->idekey = $idekey;
        $this->fileuri = '';
        $this->engine_version = '';
        $this->protocol_version = '';
        $this->appid = '';
        $this->language = '';
    }

    public function setBreakpoints(array $breakpoints)
    {
        $this->breakpoints = $breakpoints;
    }

    public function setTypemap(array $typemap)
    {
        $this->typemap = $typemap;
    }

    public function commit(IConnection $connection)
    {
        foreach ($this->transactions as $line) {
            $connection->write((string) $line);
        }
    }

    public function onInit(CInitMessage $initmessage)
    {
        $this->fileuri = $initmessage->fileuri;
        $this->engine_version = $initmessage->engine_version;
        $this->protocol_version = $initmessage->protocol_version;
        $this->appid = $initmessage->appid;
        $this->language = $initmessage->language;
    }

    public function onResponse(\SimpleXMLElement $xml)
    {
        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        switch ($xml->attributes()->command) {
        case 'source':
            $source = base64_decode((string) $xml);
            /* d($source); */
        break;
        case 'stack_get':
        break;
        case 'stop':
        break;
        /* default: throw new \RuntimeException("Not implemented:"); */
        }

        /* $transaction_id = $xml->attributes()->transaction_id . ''; */
        /* unset($this->transactions[$transaction_id]); */

        /* if (array_key_exists($transaction_id, $this->callbacks)) { */
        /*     $this->callbacks[$transaction_id]($xml); */
        /*     unset($this->callbacks[$transaction_id]); */
        /* } */
    }

    /** @deprecated */
    public function addCallback(string $transaction_id, callable $callback)
    {
        $this->callbacks[$transaction_id] = $callback;
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

        $this->transactions[] = $this->cmd('feature_set', ['-n', $feature_name, '-v', $value]);
    }

    /**
     * The status command is a simple way for the IDE to find out from the debugger engine whether execution may be
     * continued or not. No body is required on request. If async support has been negotiated using feature_get/set
     * the status command may be sent while the debugger engine is in a 'run state'.
     *
     * https://xdebug.org/docs/dbgp#status
     */
    public function cmdStatus()
    {
        $this->transactions[] = $this->cmd('status');
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
            // The following features strings MAY be available, if they are not,
            // the IDE should assume that the feature is not available:
            'supported_encodings',
            'supports_postmortem',
            'show_hidden',
            'notify_ok',
        ];

        $transaction = $this->cmd('feature_get', ['-n', $feature_name]);
        $this->transactions[] = $transaction;
    }

    /*======================================*/
    /*===    continuation commands  ========*/
    /*======================================*/

    /** 
     * Starts or resumes the script until a new breakpoint is reached,
     * or the end of the script is reached.
     */
    public function cmdRun()
    {
        $transaction = $this->cmd('run');
        $this->transactions[] = $transaction;
    }

    /* steps to the next statement, if there is a function call involved it will break on the first statement in that function */
    public function cmdStepInto()
    {
        $transaction = $this->cmd('step_into');
        $this->transactions[] = $transaction;
    }

    /* steps to the next statement, if there is a function call on the line from which the step_over is issued then the debugger engine will stop at the statement after the function call in the same scope as from where the command was issued */
    public function cmdStepOver()
    {
        $transaction = $this->cmd('step_over');
        $this->transactions[] = $transaction;
    }

    /* steps out of the current scope and breaks on the statement after returning from the current function. (Also called 'finish' in GDB) */
    public function cmdStepOut()
    {
        $transaction = $this->cmd('step_out');
        $this->transactions[] = $transaction;
    }

    /**
     * Ends execution of the script immediately,
     * the debugger engine may not respond,
     * though if possible should be designed to do so.
     *
     * The script will be terminated right away and be followed by a
     * disconnection of the network connection from the IDE
     * (and debugger engine if required in multi request apache processes).
     */
    public function cmdStop(): string
    {
        $transaction = $this->cmd('stop');
        $this->transactions[] = $transaction;

        return $transaction->getId();
    }

    /**
     * (optional): stops interaction with the debugger engine.
     * Once this command is executed, the IDE will no longer be
     * able to communicate with the debugger engine.
     *
     * This does not end execution of the script as does the stop command above,
     * but rather detaches from debugging.
     * Support of this continuation command is optional,
     * and the IDE should verify support for it via the feature_get command.
     *
     * If the IDE has created stdin/stdout/stderr pipes for execution of the script
     * (eg. an interactive shell or other console to catch script output),
     * it should keep those open and usable by the process until
     * the process has terminated normally.
     */
    public function cmdDetach()
    {
        $transaction = $this->cmd('detatch');
        $this->transactions[] = $transaction;
    }

    /*======================================*/
    /*===   BREAKPOINTS             ========*/
    /*======================================*/

    public function cmdBreakpointSet(string $file, int $lineno)
    {
        $transaction = $this->cmd('breakpoint_set', [
            '-t line',
            '-f ' . $file,
            '-n ' . $lineno,
        ]);

        $this->transactions[] = $transaction;
    }

    public function cmdBreakpointGet()
    {
        $transaction = $this->cmd('breakpoint_get');
        $this->transactions[] = $transaction;
    }

    public function cmdBreakpointUpdate()
    {
        $transaction = $this->cmd('breakpoint_update');
        $this->transactions[] = $transaction;
    }

    public function cmdBreakpointRemove()
    {
        $transaction = $this->cmd('breakpoint_remove');
        $this->transactions[] = $transaction;
    }

    public function cmdBreakpointList()
    {
        $transaction = $this->cmd('breakpoint_list');
        $this->transactions[] = $transaction;
    }

    /*======================================*/
    /*===         stack           ========*/
    /*======================================*/
    public function cmdStackDepth()
    {
        $transaction = $this->cmd('stack_depth');
        $this->transactions[] = $transaction;
    }

    public function cmdStackGet()
    {
        $transaction = $this->cmd('stack_get');
        $this->transactions[] = $transaction;
    }

    /*======================================*/
    /*===         context           ========*/
    /*======================================*/
    public function cmdContextNames()
    {
        $transaction = $this->cmd('context_names');
        $this->transactions[] = $transaction;
    }

    public function cmdContextGet()
    {
        $transaction = $this->cmd('context_get');
        $this->transactions[] = $transaction;
    }

    /*======================================*/
    /*===         types           ========*/
    /*======================================*/

    /**
     * The IDE calls this command to get information on how to map language
     * specific type names (as received in the property element returned by the
     * context_get, and property_* commands). The debugger engine returns all
     * data types that it supports. There may be multiple map elements with the
     * same type attribute value, but the name value must be unique. This
     * allows a language to map multiple language specific types into one of
     * the common data types (eg. float and double can both be mapped to
     * float).
     */
    public function cmdTypemapGet()
    {
        $transaction = $this->cmd('typemap_get');
        $this->transactions[] = $transaction;
    }

    /*======================================*/
    /*===         property           ========*/
    /*======================================*/
    public function cmdPropertyGet()
    {
        $transaction = $this->cmd('property_get');
        $this->transactions[] = $transaction;
    }

    public function cmdPropertySet()
    {
        $transaction = $this->cmd('property_set');
        $this->transactions[] = $transaction;
    }

    public function cmdPropertyValue()
    {
        $transaction = $this->cmd('property_value');
        $this->transactions[] = $transaction;
    }

    /*======================================*/
    /*===         source           ========*/
    /*======================================*/
    public function cmdSource(): string
    {
        $transaction = $this->cmd('source', [
            '-f file:///home/ada/xdebug-client/example-page.php'
        ]);

        $this->transactions[] = $transaction;

        return $transaction->getId();
    }

    /*======================================*/
    /*===         break           ========*/
    /*======================================*/
    public function cmdBreak()
    {
        $transaction = $this->cmd('break');
        $this->transactions[] = $transaction;
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

    public function cmd(string $cmd, array $args = [], string $data = ''): XDebugTransaction
    {
        $transaction = new XDebugTransaction();
        /* $transaction->setId($this->transaction_id); */

        $transaction->setCommad($cmd);
        $transaction->setArgs($args);
        $transaction->setData($data);

        /* $this->transaction_id ++; */

        return $transaction;
    }
}

