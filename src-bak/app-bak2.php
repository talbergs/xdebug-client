#!/usr/bin/env php
<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/bootstrap.php';

$sock2 = socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);
d($sock);

@unlink('/tmp/xdebug.sock');
$bind = socket_bind($sock2, '/tmp/xdebug.sock');
d($bind);

$listen = socket_listen($sock2);
d($listen);

$sock3 = socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);
d($sock);

@unlink('/tmp/rpc.sock');
$bind = socket_bind($sock3, '/tmp/rpc.sock');
d($bind);

$listen = socket_listen($sock3);
d($listen);

$sock4 = socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);
d($sock);

/* @unlink('/tmp/ipc2.sock'); */
/* $bind = socket_bind($sock4, '/tmp/ipc2.sock'); */
/* d($bind); */

/* $listen = socket_listen($sock4); */
/* d($listen); */

trait ConnectionTrait
{
    protected $socket;
    protected string $name;
    protected array $clients = [];

    protected function __construct(string $name, $socket)
    {
        $this->socket = $socket;
        $this->name = $name;
    }

    public function getMap(): array
    {
        $map = [];

        foreach ($this->clients as $connection) {
            $sock = $connection->getSocket();
            $map['1:' . $this->name . ':' . (int) $sock] = $sock;
        }

        $map['0:' . $this->name . ':' . (int) $this->socket] = $this->socket;

        return $map;
    }

    public function read(string &$error = null): ?string
    {
        // TODO: exhaust it.
        $len = socket_recv($this->socket, $buf, 2100, MSG_DONTWAIT);

        if ($len === 0) {
            $error = "Client left!";
        } else if ($len === false) {
            $error = "Some error!";
        }

        return $buf;
    }

    public function getResourceId(): int
    {
        return (int) $this->socket;
    }

    public function write()
    {
    }

    public function removeById(int $id)
    {
        socket_close($this->clients[$id]->getSocket());
        unset($this->clients[$id]);
    }

    public function getById(int $id): Connection
    {
        return $this->clients[$id];
    }

    public function accept()
    {
        $socket = socket_accept($this->socket);
        $this->clients[(int) $socket] = new self($this->name, $socket);
    }

    public function getSocket()
    {
        return $this->socket;
    }
}

interface Connection
{
    public function write();
    public function getSocket();
    public function getResourceId(): int;
    public function getName(): string;
    public function getStatus(): int;
}

class ConnectionUnix implements Connection
{
    use ConnectionTrait;

    public static function new(string $name, string $sock_path)
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);

        @unlink($sock_path);
        socket_bind($socket, $sock_path);
        socket_listen($socket);

        return new self($name, $socket);
    }
}

class ConnectionInet implements Connection
{
    use ConnectionTrait;

    public static function new(string $name, int $port): Connection
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, '0.0.0.0', $port);
        socket_listen($socket);

        return new self($name, $socket);
    }
}

class ConnectionHub
{
    protected SplObjectStorage $connections;

    public function __construct()
    {
        $this->connections = new SplObjectStorage();
    }

    public function get($socket): Connection
    {
        $this->connections[] = $conn;
    }

    public function remove(Connection $conn)
    {
        $this->connections[] = $conn;
    }

    public function add(Connection $conn)
    {
        $this->connections[] = $conn;
    }

    public function getConnections(): array
    {
        return [
            
        ];
    }
}

$web = ConnectionInet::new('web', 8080);
$hub = new ConnectionHub();

$hub->add($web);

/* $web = $web2->getSocket(); */

$clients = [];
$events = [];
while (true) {
    d('tick-'.time());
    d($web);
    d('tick');

    $read = [
        /* '0:web' => $sock, */
        /* '0:web' => $web, */
        '0:rpc' => $sock3,
        '0:xdebug' => $sock2,
    ] + $clients + $web->getMap();

    $write = null;
    $except = null;
    socket_select($read, $write, $except, 5);

    foreach ($read as $key => $s) {
        d('=>' . $key);
        list($alive, $domain) = explode(':', $key);
        if ($alive) {
            if ($domain === 'rpc') {
                $len = socket_recv($s, $buf, 2100, MSG_DONTWAIT);
                if ($len === 0) {
                    echo "Client left!";
                    socket_close($s);
                    unset($clients[$key]);
                } else {
                    $bufsub = substr($buf, 0, 10);
                    $events[] = "Alive connection wrote '{$len} {$bufsub}' at: " . $key;
                }
            } else if ($domain === 'web') {
                $conn = $web->getById((int) $s);
                $buf = $conn->read($error);

                if ($error !== null) {
                    /* $web->removeById((int) $s); */
                    $events[] = $error;
                } else {
                    $bufsub = substr($buf, 0, 10);
                    $events[] = "Alive connection wrote '{$bufsub}' at: " . $key;
                }

                /* $len = socket_recv($s, $buf, 2100, MSG_DONTWAIT); */
                /* if ($len === 0) { */
                /*     echo "Client left!"; */
                /*     socket_close($s); */
                /*     unset($clients[$key]); */
                /* } else { */
                /*     $bufsub = substr($buf, 0, 10); */
                /*     $events[] = "Alive connection wrote '{$len} {$bufsub}' at: " . $key; */
                /* } */
                /* socket_write($s, "aaa", 3); */
                /* socket_close($s); */
                /* unset($clients[$key]); */
            } else if ($domain === 'xdebug') {
                $len = socket_recv($s, $buf, 2100, MSG_DONTWAIT);
                if ($len === 0) {
                    echo "Client left!";
                    socket_close($s);
                    unset($clients[$key]);
                } else {
                    $bufsub = substr($buf, 0, 10);
                    $events[] = "Alive connection wrote '{$len} {$bufsub}' at: " . $key;
                }
            }
        } else {
            if ($domain === 'rpc') {
                $events[] = "Got RPC request.";
                $ss = socket_accept($s);
                $clients['1:rpc:' . (int) $ss] = $ss;
            } else if ($domain === 'web') {
                $events[] = "Got web request.";
                $web->accept();
            } else if ($domain === 'xdebug') {
                $events[] = "Got xdebug request.";
                $ss = socket_accept($s);
                $clients['1:xdebug:' . (int) $ss] = $ss;
            }
        }
    }

    while ($event = array_pop($events)) {
        /* d("BEGIN EVENT: $event"); */
        /* sleep(1); */
        /* d("END EVENT: $event"); */
        d("RUN EVENT: $event");
    }
}
