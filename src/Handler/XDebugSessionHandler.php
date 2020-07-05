<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\XDebugClientLeft;
use Acme\Hub;
use Acme\Log;
use Acme\XDebugApp\Messages\CInitMessage;
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

        /** @var CInitMessage $imessage */
        $imessage = CMessageFactory::fromXMLString($str);

        if ($imessage->idekey === 'xdeweb') {
            info("Connection from idekey: '{$imessage->idekey}'");
            $xdebug_app = $hub->getXDebugApp();
            $xdebug_app->setDevice($device);

            // Bootsrap project
            /* $xdebug_app->cmdTypemapGet(); */

            // Set project breakpoints
            /* $xdebug_app->cmdBreakpointSet(); */

            // Set project configuration features
            /* $xdebug_app->cmdFeatureSet('max_depth', '9'); */
            /* $xdebug_app->cmdFeatureSet('max_children', '9'); */
            /* $xdebug_app->cmdFeatureSet('max_data', '9'); */

            // Breakpoint list - reassure breakpoints are set
            /* $xdebug_app->cmdBreakpointList(); */

            // If "NOT BREAK ON FIRST LINE IS configured" and there are breakpoints , then -> RUN !
            /* $xdebug_app->cmdRun(); */
            // ELSE synthetic "STEP INTO"
            /* $xdebug_app->cmdStepInto(); */
        } else {
            info("Connection ignored (dropped) from idekey: '{$imessage->idekey}'");
            $hub->remove($device->getId());
        }
    }
}
