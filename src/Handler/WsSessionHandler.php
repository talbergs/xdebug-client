<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;
use Acme\Log;

class WsSessionHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $str = $device->getConnection()->read();

        if ($str === '') {
            d(__CLASS__ . ' <> deviceid:' . $device->getId() . ' LEFT ');
            $hub->remove($device->getId());
            return;
        }

        if ($str === 'xdebug:source') {
            $xdebug_app = $hub->getXDebugApp();
            $transaction_id = $xdebug_app->cmdSource();
            $xdebug_app->addCallback($transaction_id, function($xml) {
                d('----', $xml, '----', $this);
            });
            $xdebug_app->commit();
        }

        if ($str === 'xdebug:breakpoint_list') {
            $xdebug_app = $hub->getXDebugApp();
            $xdebug_app->cmdBreakpointList();
            $xdebug_app->commit();
        }

        if ($str === 'xdebug:breakpoint_set') {
            $xdebug_app = $hub->getXDebugApp();
            $transaction_id = $xdebug_app->cmdBreakpointSet(
                'file:///home/ada/xdebug-client/example-page.php',
                5
            );
            $xdebug_app->addCallback($transaction_id, function($xml) {
                d('----', $xml, '----', $this);
            });
            $xdebug_app->commit();
        }

        if ($str === 'xdebug:run') {
            $xdebug_app = $hub->getXDebugApp();
            $xdebug_app->cmdRun();
            $xdebug_app->commit();
        }

        if ($str === 'xdebug:status') {
            $xdebug_app = $hub->getXDebugApp();
            $transaction_id = $xdebug_app->cmdStatus();
            $xdebug_app->addCallback($transaction_id, function($xml) use ($hub) {
                d('----', $xml, '----', $this);
                $hub->getState()->setState('engine.status', 32);
                $hub->notifyFrontend(json_encode($hub->getState()->getFullState()));
            });
            $xdebug_app->commit();
        }

        if ($str === 'xdebug:stack_get') {
            $xdebug_app = $hub->getXDebugApp();
            $xdebug_app->cmdStackGet();
            $xdebug_app->commit();
        }

        if ($str === 'exit:session') {
            $xdebug_app = $hub->getXDebugApp();
            $transaction_id = $xdebug_app->cmdStop();
            $xdebug_app->addCallback($transaction_id, function($xml) use ($hub, $xdebug_app) {
                d('>>>>>>>>', $xml, '<<<<<<', $this);
                $hub->remove($xdebug_app->getDevice()->getId());
                $xdebug_app->appid = null;
            });
            $xdebug_app->commit();
        }

        if ($str === 'app:state') {
            $hub->notifyFrontend(json_encode($hub->getState()->getFullState()));
        }

        if ($str === 'app:files') {

            $files = [];
            $directories = [];
            $path = '/';
            $dir = dir($path);
            while (false !== ($entry = $dir->read())) {
                if (is_dir("{$path}{$entry}")) {
                    $directories[] = ['name' => $entry . '/'];
                } else {
                    $files[] = ['name' => $entry];
                }
            }
            $dir->close();

            usort($directories, fn($a, $b) => $a['name'] <=> $b['name']);
            usort($files, fn($a, $b) => $a['name'] <=> $b['name']);
            $entries = array_merge($directories, $files);
            $hub->notifyFrontend(json_encode(['files' => $entries]));
        }

        Log::log(__CLASS__.':'.__FUNCTION__);
    }
}
