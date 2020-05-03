<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Hub;

interface IHandler
{
    public function handle(IDevice $device, Hub $hub);
}

