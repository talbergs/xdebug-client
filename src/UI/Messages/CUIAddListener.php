<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Hub;

class CUIAddListener implements IUIMessage
{
    public function __construct()
    {
    }

    // It actually adds new oending session WITH IDEKEY
    public function actOn(Hub $hub)
    {
    }
}
