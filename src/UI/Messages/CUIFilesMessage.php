<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


class CUIFilesMessage implements IUIMessage
{
    public function __construct(array $params)
    {
    }

    public function actOn(Hub $hub)
    {
        d($hub->xd_sessions);
        d($hub);
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
}
