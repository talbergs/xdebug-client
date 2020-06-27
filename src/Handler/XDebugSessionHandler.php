<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\XDebugClientLeft;
use Acme\Hub;
use Acme\Log;

class XDebugSessionHandler implements IHandler
{
    protected $transactions = [];
    protected $transaction_id = 0;
    
    public function handle(IDevice $device, Hub $hub)
    {
        try {
            $str = $device->getConnection()->read();
        } catch (XDebugClientLeft $e) {
            echo 'XDebugClientLeft';

            $hub->remove($device->getId());
            $hub->notifyFrontend('Debugger engine left unexpectedly.');

            Log::log($e->getMessage());
            return;
        }

        Log::log(__CLASS__.':'.__FUNCTION__);
        Log::log(pretty_xml($str));

        $xdebug_app = $hub->getXDebugApp();
        $xdebug_app->setDevice($device);

        $xml = simplexml_load_string($str);
        /* $xml->attributes()->transaction_id; */

        switch ($xml->getName()) {
        case 'init': # https://xdebug.org/docs/dbgp#connection-initialization
            $xdebug_app->onInit($xml);
            $state = $hub->getState();
            $state->setState('xdebug.init', [
                'language' => $xdebug_app->language,
                'idekey' => $xdebug_app->idekey,
                'appid' => $xdebug_app->appid,
                'engine_version' => $xdebug_app->engine_version,
                'protocol_version' => $xdebug_app->protocol_version,
                'fileuri' => $xdebug_app->fileuri,
            ]);

            $hub->notifyFrontend(json_encode($state->getState('xdebug.init')));
            break;
        case 'response': # https://xdebug.org/docs/dbgp#response
            $xdebug_app->onResponse($xml);
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
