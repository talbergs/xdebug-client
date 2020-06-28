<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\XDebugClientLeft;
use Acme\Hub;
use Acme\Log;
use Acme\XDebugApp\Messages\CMessageFactory;

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
        $imessage = CMessageFactory::fromXMLString($str);
        d($imessage);

        $xdebug_app = $hub->getXDebugApp();
        $xdebug_app->setDevice($device);
    }
}
