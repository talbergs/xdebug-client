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
