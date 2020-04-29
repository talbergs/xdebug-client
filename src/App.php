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
        $req = HTTPRequest::fromString($conn->read());

        $response = new HTTPResponse();

        if ($req->isFileRequest()) {
            $response->setStatusCode(200);
            $response->setBody(file_get_contents($req->getFilePath()));
        } else if ($req->isIndexRequest()) {
            $response->setStatusCode(200);
            $response->setBody(file_get_contents($req->getIndexFilePath()));
        }

        $conn->write((string) $response);

        $this->queue->push(new CloseConnectionEvent($conn));
    }
}
