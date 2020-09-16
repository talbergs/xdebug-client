<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\Device;
use Acme\Device\IDevice;
use Acme\Hub;
use Acme\XDebugApp\XDebugSession;

class XDebugAcceptHandler implements IHandler
{
    protected XDebugSession $session;

    public function __construct(XDebugSession $sess)
    {
        $this->session = $sess;
    }

    public function handle(IDevice $device, Hub $hub)
    {
        $conn = $this->session->accept();

        $han = new XDebugSessionHandler($this->session);

        $device = new Device($conn, $han);

        $hub->add($device);

        /* info("=== accept session ==="); */
        /* d($hub); */
        /* info("=== accept session ==="); */

        // Allways accept ANY connection, pass down session.
        /* $hub->add( */
        /*     new Device( */
        /*         $device->getConnection()->accept(), */
        /*         new XDebugSessionHandler($this->session) */
        /*     ) */
        /* ); */

        /* $hub->xd_listeners */
        /* $hub->addXDebugSession('my-id', $sess); */
        /* $hub->bindXDebugSession($new_device->getId(), $device->getId()); */
        
        /* $session_id = 'SESS:' . $new_device->getId() . ':'; */
        /* $hub->addXDebugSession($session_id, new XDebugSession($new_conn, 'uu2')); */

        /* info("1"); */
        /* sleep(5); */
        /* $hub->add($new_device); */
        /* info("2"); */
        /* sleep(5); */
        /* info("3"); */

        /* info("> Listening for xdebug connection on {$new_device}"); */
    }
}
