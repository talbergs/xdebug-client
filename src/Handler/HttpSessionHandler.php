<?php declare(strict_types=1);

namespace Acme\Handler;

use Acme\Device\IDevice;
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
            d(__CLASS__ . ' <> deviceid:' . $device->getId() . ' LEFT ');
            $hub->remove($device->getId());
            return;
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
        }
    }
}
