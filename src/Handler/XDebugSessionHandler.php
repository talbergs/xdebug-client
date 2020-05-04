<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;
use Acme\Log;
use Acme\XDebugApp\XDebugApp;

class XDebugSessionHandler implements IHandler
{
    protected $transactions = [];
    protected $transaction_id = 0;
    public $app;

    public function __construct()
    {
        $this->app = new XDebugApp($this);
    }
    
    public function handle(IDevice $device, Hub $hub)
    {
        $str = $device->getConnection()->read();
        Log::log(__CLASS__.':'.__FUNCTION__);
        Log::log(pretty_xml($str));

        $this->app->setDevice($device);

        $xml = simplexml_load_string($str);
        /* $xml->attributes()->transaction_id; */

        switch ($xml->getName()) {
        case 'init': # https://xdebug.org/docs/dbgp#connection-initialization
            $this->app->onInit($xml);
            $hub->notifyFrontend(json_encode([
                'init' => [
                    'language' => $this->app->language,
                    'idekey' => $this->app->idekey,
                    'appid' => $this->app->appid,
                    'engine_version' => $this->app->engine_version,
                    'protocol_version' => $this->app->protocol_version,
                    'fileuri' => $this->app->fileuri,
                ]
            ]));
            break;
        case 'response': # https://xdebug.org/docs/dbgp#response
            $this->app->onResponse($xml);
            break;
        case 'stream': # https://xdebug.org/docs/dbgp#stream
            throw new \Exception("'{$xml->getName()}' < not implemented.");
        case 'notify': # https://xdebug.org/docs/dbgp#notify
            throw new \Exception("'{$xml->getName()}' < not implemented.");
        default:
            throw new \Exception("'{$xml->getName()}' < unrecognized request name.");
        }
    }
}
