<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
use Acme\Exceptions\EConnectionBroke;
use Acme\HTTP\HTTPResponse;
use Acme\Hub;
use Acme\HTTP\HTTPRequest;
use Acme\Protocol\CWsProtocol;

class HttpSessionHandler implements IHandler
{
    public function handle(IDevice $device, Hub $hub)
    {
        $str = $device->getConnection()->read();
        if ($str === '') {
            throw new EConnectionBroke();
        }

        $req = HTTPRequest::fromString($str);

        $response = new HTTPResponse();

        $conn = $device->getConnection();

        if ($req->isWebSocketHandshake()) {
            $response = HTTPResponse::handShakeResponse($req);
            $response->setBody('');
            $conn->write((string) $response."\r\n");
            $conn->setProtocol(new CWsProtocol());
            $device->setHandler(new WsSessionHandler());
        } else if ($req->isFileRequest()) {
            $response->setStatusCode(200);
            // For ES modules to work, this is mandatory header.
            if (preg_match('/.*\.mjs$/', $req->getFilePath())) {
                $response->setHeader('content-type', 'text/javascript');
            } else if (preg_match('/.*\.css$/', $req->getFilePath())) {
                $response->setHeader('content-type', 'text/css');
            }
            $contents = file_get_contents($req->getFilePath());
            $response->setBody("\r\n" . $contents);

            $conn->write((string) $response);
            $hub->remove($device->getId());
        } else if ($req->isIndexRequest()) {
            $response->setStatusCode(200);
            $response->setBody(file_get_contents($req->getIndexFilePath()));

            $conn->write((string) $response);
            $hub->remove($device->getId());
        } else if ($req->url === '/api/connection/list') {
            $response->setStatusCode(200);

            $devices = [];
            foreach ($hub->devicesByHandler(XDebugSessionHandler::class) as $deviceid) {
                $device = $hub->get($deviceid);
                $devices[] = [
                    'id' => $device->getId(),
                    'live' => true,
                    'name' => (string) $device->getConnection(),
                ];
            }

            foreach ($hub->devicesByHandler(XDebugAcceptHandler::class) as $deviceid) {
                $device = $hub->get($deviceid);
                $devices[] = [
                    'id' => $device->getId(),
                    'live' => false,
                    'name' => (string) $device->getConnection(),
                ];
            }
            
            $response->setBody(json_encode($devices));

            /* $xdb_conn = CConnection::inet($this->port, $this->host); */
            /* $xdb_conn->setProtocol(new XdbProtocol()); */
            /* $xdb = new Device($xdb_conn, new XDebugAcceptHandler()); */
            /* $hub->add($xdb); */
            /* info("Listening for XDebug connection on: {$xdb_conn}"); */

            $conn->write((string) $response);
            $hub->remove($device->getId());
        }
    }
}
