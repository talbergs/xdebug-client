<?php declare(strict_types=1);

namespace Acme\XDebugApp;

interface IXDebugState
{
    public function initSession(): IXDebugState;

    public function getFile(): IXDebugState;

    public function addBreakPoint(): IXDebugState;
}
