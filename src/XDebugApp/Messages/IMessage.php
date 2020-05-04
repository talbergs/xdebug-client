<?php

namespace Acme\XDebugApp\Messages;

use SimpleXMLElement;

interface IMessage
{
    public static function fromXML(SimpleXMLElement $xml): IMessage;
    public static function fromXMLString($str): IMessage;
}
