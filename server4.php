<?php

/* ini_set('default_socket_timeout', -1); */

require_once './vendor/autoload.php';

$sock = stream_socket_server("tcp://0.0.0.0:8080", $errno, $errstr);

$body = "<html>
  <head>
    <title>An Example Page</title>
  </head>
  <body>
    <p>Hello World, this is a very simple HTML document.</p>
  </body>
</html>
";
$len = strlen($body);
$res = "HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
Content-Length: $len
Connection: close

$body";

$conn = null;
while (true) {
    $write = $read = $except = array_filter([$sock, $conn]);
    stream_select($write, $read, $except, 1);
    /* d($write, $read, $except); */

    foreach ($write as $s) {
        $conn = ftell($s) == 0 ? stream_socket_accept($s) : $s;
        if (ftell($s)) {
            $frame = WSFrameFragment::fromStream($conn);
            d($frame . ' }}');
            continue;
        }
        $request = HTTPRequest::fromStream($conn);
        d("<<<<<<<<<<<<<<<<", $request);
        $response = new HTTPResponse();
        if ($request->isWebSocketHandshake()) {
            $response->setStatusCode(101);
            $response->setHeader('upgrade', 'websocket');
            $response->setHeader('connection', 'upgrade');

            $key = $request->getHeader('sec-websocket-key');
            $acc = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
            $acc = base64_encode(sha1($acc, true));
            $response->setHeader('sec-websocket-accept', $acc);

            fwrite($conn, $response);
            sleep(1);
            /* $conn2 = stream_socket_accept($conn); */
            /* dd(fread($conn2, 200)); */
            /* fwrite($conn, "sssssssssssss"); */
            d(">>>>>>>>>>>>>>>>", $response . '');
        } else {
            /* fwrite($conn, $res); */
        }

        /* dd($request); */
        /* dd($s, $conn, "<conn"); */
    }
    sleep(1);
    d("waiting");

}

fwrite($conn, $res);
d(__LINE__);

dd($req);
