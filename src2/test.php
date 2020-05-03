<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

new CConnection("socket", new CWsProtocol());
