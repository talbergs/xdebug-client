<?php

use PHPUnit\Framework\TestCase;

class appTest extends TestCase
{
    protected $process;
    protected bool $process_ready;

    public function whileSpawn(callable $callback)
    {
        $cmd = [
            './src/app.php',
            '--xdebug-socket',
            '/tmp/xdebug.sock',
            '--http-port',
            '8080',
        ];

        $descriptorspec = [
           1 => ["pipe", "w"],
           2 => ["pipe", "w"],
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes);
        while (!$this->process_ready);

        $terminate = function ($signal = SIGTERM) use (&$process, &$pipes) {
            proc_terminate($process, $signal);
            $stdout = explode(PHP_EOL, stream_get_contents($pipes[1]));
            $stderr = explode(PHP_EOL, stream_get_contents($pipes[2]));

            return [$stdout, $stderr];
        };

        $this->process = $process;

        $callback($terminate);

        fclose($pipes[1]);
        fclose($pipes[2]);
    }

    public function spawn(array $cmd, ?array &$stdout, ?array &$stderr)
    {
        $descriptorspec = [
           1 => ["pipe", "w"],
           2 => ["pipe", "w"],
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes);
        $pid = proc_get_status($process)['pid'];
        pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED);

        $stdout = explode(PHP_EOL, stream_get_contents($pipes[1]));
        $stderr = explode(PHP_EOL, stream_get_contents($pipes[2]));

        proc_terminate($process);

        fclose($pipes[1]);
        fclose($pipes[2]);
    }

    /**
     * @test
     */
    public function printsHelpIfNoArgumentsPassed()
    {
        $this->spawn(['./src/app.php'], $stdout, $stderr);

        $this->assertSame([''], $stderr);
        $this->assertSame([
            'Usage',
            '--http-port 8080',
            '--xdebug-socket /tmp/xdebug.sock',
            '',
        ], $stdout);
    }

    /**
     * @test
     */
    public function ifAllArgumentsArePassedCorrectAppSaysItListens()
    {
        $this->whileSpawn(function (callable $terminate) {
            list($stdout, $stderr) = $terminate(SIGTERM);
            $this->assertSame([''], $stderr);
            $this->assertSame([
                'Listening for web connections on: 0.0.0.0:8080',
                'Listening for xdebug connections on /tmp/xdebug.sock',
                '',
            ], $stdout);
        });
    }

    /**
     * @test
     */
    public function socketIsCreated()
    {
        $this->whileSpawn(function () {
            $this->assertTrue(file_exists('/tmp/xdebug.sock'), 'No socket created');
        });
    }

    /**
     * @test
     */
    public function socketIsRemovedAtExit()
    {
        $this->whileSpawn(function (callable $terminate) {
            $terminate(SIGTERM);
            $this->assertFalse(file_exists('/tmp/xdebug.sock'), 'Not removed');
        });

        $this->whileSpawn(function (callable $terminate) {
            $terminate(SIGHUP);
            $this->assertFalse(file_exists('/tmp/xdebug.sock'), 'Not removed');
        });

        $this->whileSpawn(function (callable $terminate) {
            $terminate(SIGQUIT);
            $this->assertFalse(file_exists('/tmp/xdebug.sock'), 'Not removed');
        });
    }

    /**
     * @test
     */
    public function socketIsRemovedAfterCrash()
    {
        $this->whileSpawn(function (callable $terminate) {
            $terminate(SIGILL);
            $pid = proc_get_status($this->process)['pid'];
            pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED);
            $this->assertFalse(file_exists('/tmp/xdebug.sock'), 'Not removed');
        });
    }

    /**
     * @test
     */
    public function canRespondToAGetRequest()
    {
        $this->whileSpawn(function () {
            $this->assertEquals(
                '<script src="js.js"></script>',
                @file_get_contents('http://0.0.0.0:8080'),
                'First request',
            );
        });
    }

    /**
     * @test
     */
    public function canRespondToSecondGetRequest()
    {
        $this->whileSpawn(function () {
            $this->assertEquals(
                '<script src="js.js"></script>',
                @file_get_contents('http://0.0.0.0:8080'),
                'First request',
            );

            $this->assertEquals(
                '<script src="js.js"></script>',
                @file_get_contents('http://0.0.0.0:8080'),
                'Subsequent request',
            );
        });
    }

    /**
     * @test
     */
    public function givesJsFileOnJsRequest()
    {
        $this->whileSpawn(function () {
            $this->assertEquals(
                file_get_contents('./src/js.js'),
                @file_get_contents('http://0.0.0.0:8080/js.js'),
                'First request',
            );
        });
    }

    /**
     * @test
     */
    public function websocketHandshakeWorked()
    {
        $this->whileSpawn(function () {
            $cmd = [
                'curl',
                '--silent',
                '--dump-header',
                '-',
                '--max-time',
                '0.001',
                '--header',
                'Connection: Upgrade',
                '--header',
                'Upgrade: websocket',
                '--header',
                'Sec-WebSocket-Key: SGVsbG8sIHdvcmxkIQ==',
                '--header',
                'Sec-WebSocket-Version: 13',
                'http://0.0.0.0:8080',
            ];

            $descriptorspec = [
               1 => ["pipe", "w"],
            ];

            $process = proc_open($cmd, $descriptorspec, $pipes);
            $pid = proc_get_status($process)['pid'];
            pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED);

            $stdout = explode(PHP_EOL, stream_get_contents($pipes[1]));

            fclose($pipes[1]);

            $expect = [
                "HTTP/1.1 101 Switching Protocols\r",
                "upgrade: websocket\r",
                "connection: upgrade\r",
                "sec-websocket-accept: qGEgH3En71di5rrssAZTmtRTyFk=\r",
                "\r",
                "",
            ];

            $this->assertSame($expect, $stdout);
        });
    }

    /**
     * @test
     */
    public function acceptsXdebugConnection()
    {
        $this->whileSpawn(function () {
            exec('socat -u OPEN:/dev/null UNIX-CONNECT:/tmp/xdebug.sock', $o, $exit_code);
            $this->assertSame(0, $exit_code);
        });
    }

    /**
     * @test
     */
    public function logsXdebugConnected()
    {
        $this->whileSpawn(function (callable $terminate) {
            exec('socat -u OPEN:/dev/null UNIX-CONNECT:/tmp/xdebug.sock');
            list($stdout) = $terminate();
            $this->assertSame([
                'Listening for web connections on: 0.0.0.0:8080',
                'Listening for xdebug connections on /tmp/xdebug.sock',
                'Xdebug just connected: /tmp/xdebug.sock',
                '',
            ], $stdout);
        });
    }

    protected function setUp(): void
    {
        if (file_exists('/tmp/xdebug.sock')) {
            unlink('/tmp/xdebug.sock');
        }

        $this->process_ready = false;
        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            $this->process_ready = true;
        });
    }

    protected function tearDown(): void
    {
        if (is_resource($this->process)) {
            proc_terminate($this->process, SIGKILL);
        }
    }
}
