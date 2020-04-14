<?php
/* ini_set('default_socket_timeout', -1); */

require_once './vendor/autoload.php';

$sock = '/tmp/xdebug.sock';
$path = 'unix://' . $sock;

if (file_exists($sock)) {
    unlink($sock);
}

$sock = stream_socket_server($path, $errno, $errstr);

$sock2 = '/tmp/xdebug2.sock';
$path2 = 'unix://' . $sock2;
if (file_exists($sock2)) {
    unlink($sock2);
}
$sock2 = stream_socket_server($path2, $errno, $errstr);
$conn2 = stream_socket_accept($sock2, -1);

$readable = [$conn2, $sock2];
$writable = [$conn2, $sock2];
$except = null;
$res = stream_select($readable, $writable, $except, 10);

dd($res, $readable, $writable);
/* dd(socket_select($r, $r, $a, null)); */
while ($conn2 = stream_socket_accept($sock2, -1)) {
    d('a');
    d(stream_get_line($conn2, 1000));
}

die;

class Req
{
    function __construct()
    {
        $this->size = 0;
        $this->buf = '';
    }

    function read($str)
    {
        if (!$this->size) {
            /* $this-> */
        } else {
        }
    }
}

class Recv
{
    function __construct($conn)
    {
        $this->conn = $conn;
    }

    function read()
    {
        $len = '';
        $b = '';
        while (true) {
            $b = fgetc($this->conn);
            if ($b === "\x00") {
                break;
            }
            $len .= $b;
        }
        if ($len === '') {
            echo 'Incorrect request. Expecting header.';die;
        } else if ($len == 0) {
            echo 'Empty request.';die;
        }

        $res = stream_get_line($this->conn, (int) $len, "\x00");
        if ($res === false) {
            echo 'Php broke.';die;
        } else if ($res === '') {
            echo 'Xdebug broke.';die;
        }

        return $res;
    }
}
/* dd(ord("\x00")); // 0 */
/* dd("\x00" === chr(0)); // true */

if (!$sock) {
  echo "$errstr ($errno)<br />\n";
} else {
    $rr = 0;
    while ($conn = stream_socket_accept($sock, -1)) {
        d($conn, "<CONN>");
        $recv = new Recv($conn);
        $res = $recv->read();
        pretty_xml($res);
        if ($rr) {
            fclose($conn);
        }
        $req = InitRequest::fromXMLString($res);
        d($req);

        $r = "status -i 0\x00";
        d(stream_socket_sendto($conn, $r));
        /* fwrite($conn, $r); */

        /* $s2 = stream_socket_client($path); */
        /* $sa = stream_socket_sendto($s2, $r); */

        /* $path = 'unix:///tmp/xdebug.sock'; */
        /* $sock = stream_socket_client($path, $errno, $errstr); */

    }
        d($conn, "<CONN>");

    fclose($sock);
}
