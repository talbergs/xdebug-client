<?php declare(strict_types=1);

namespace Acme;

use Acme\Connection\ConnectionInterface;
use Acme\Events\CloseConnectionEvent;
use Acme\HTTP\HTTPRequest;
use Acme\HTTP\HTTPResponse;
use Ds\Queue;

class App
{
    protected Queue $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    public function onHTTPRequest(ConnectionInterface $conn)
    {
        $tmp = $conn->read();
        $req = HTTPRequest::fromString($tmp);

        $response = new HTTPResponse();

        if ($req->isWebSocketHandshake()) {
            $response = HTTPResponse::handShakeResponse($req);
            $response->setBody("\r\n");
            $conn->write((string) $response);
        } else if ($req->isFileRequest()) {
            $response->setStatusCode(200);
            $response->setBody(file_get_contents($req->getFilePath()));

            $conn->write((string) $response);
            $this->queue->push(new CloseConnectionEvent($conn));
        } else if ($req->isIndexRequest()) {
            $response->setStatusCode(200);
            $response->setBody(file_get_contents($req->getIndexFilePath()));

            $conn->write((string) $response);
            $this->queue->push(new CloseConnectionEvent($conn));
        }
    }
}
