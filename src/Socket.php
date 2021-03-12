<?php

namespace Spider;


use RuntimeException;

class Socket
{
    private $ip;
    private $port;
    private $socket;
    private $preFlag = false;

    /**
     * Socket constructor.
     *
     * @param $ip
     * @param $port
     */
    public function __construct($ip, $port)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * Connect the socket
     */
    public function connect(): void
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! $socket) {
            throw new RuntimeException("socket create exception");
        }
        if (! socket_connect($socket, $this->ip, $this->port)) {
            throw New RuntimeException("socket connect error");
        }
        $this->socket = $socket;
    }

    /**
     * @param $uri
     * @param $method
     * @param array $header
     * @param string $body
     * @param int $timeout
     * @return string
     * @throws SpiderException
     */
    public function publish($uri, $method, $header = null, $body = '', $timeout = 10) :string
    {
        $this->preSend('PUB');

        $json = [
            'uri' => $uri,
            'method' => $method,
            'header' => $header,
            'body' => $body,
            'timeout' => $timeout
        ];
        $command = json_encode($json);
        $command = pack('na*',  strlen($command), $command);
        socket_write($this->socket, $command, strlen($command));
        $ret = socket_read($this->socket, 1024, PHP_BINARY_READ);
        if (false === $ret) {
            socket_close($this->socket);
            throw new SpiderException("socket read error");
        }
        if ( "" === $ret) {
            throw new SpiderException("socket closed");
        }
        return $ret;
    }

    /**
     * subscribe
     *
     * @param callable $callback
     * @throws SpiderException
     */
    public function subscribe(callable $callback): void
    {
        $this->preSend('SUB');
        for(;;) {
            $length = socket_read($this->socket, 2, PHP_BINARY_READ);
            if ( "" === $length) {
                throw new SpiderException("socket closed");
            }
            $len = unpack("n", $length);
            if (! $len) {
                throw new SpiderException("socket occur error");
            }
            $payload = socket_read($this->socket, $len[1], PHP_BINARY_READ);
            if ( "" === $payload) {
                throw new SpiderException("socket closed");
            }
            if (false === $payload) {
                socket_close($this->socket);
                throw new SpiderException("socket read error");
            }
            // deal the data
            $callback($payload);
        }
    }


    /**
     * @param $method
     * @return string|void
     */
    private function preSend($method)
    {
        if ($this->preFlag) {
            return;
        }
        $this->preFlag = true;
        $command = pack('a3', $method);
        if (! $this->socket) {
            $this->connect();
        }
        socket_write($this->socket, $command, strlen($command));
    }

}
