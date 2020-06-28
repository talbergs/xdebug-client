<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;


interface IUIMessage
{
    public function actOn(Hub $hub);
}
