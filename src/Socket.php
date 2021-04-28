<?php

namespace Spider;


use RuntimeException;

class Socket
{
    private $ip;
    private $port;
    private $socket;
    /**
     * @var BuffIO
     */
    private $writer;
    /**
     * @var BuffIO
     */
    private $reader;

    public const SUB_TYPE = 'SUB ';
    public const PUB_TYPE = 'PUB ';
    private $type;


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
        $this->writer = new BuffIO($socket);
        $this->reader = new BuffIO($socket);
        $this->socket = $socket;
    }


    /**
     * @param $uri
     * @param $method
     * @param null $header
     * @param string $body
     * @param int $timeout
     * @return string
     * @throws SpiderException
     */
    public function publish($uri, $method, $header = null, $body = '', $timeout = 5) :string
    {
        if (! $this->socket) {
            $this->connect();
        }
        if (! $this->type) {
            $this->sendType(self::PUB_TYPE);
        }

        $json = [
            'uri' => $uri,
            'method' => $method,
            'header' => $header,
            'body' => $body,
            'timeout' => $timeout
        ];
        $command = json_encode($json);
        $command = pack('Na*',  strlen($command), $command);
        $this->writer->write($command);
        $this->writer->flush();

        if (! $data = $this->reader->read(4) ) {
            throw new SpiderException('Connection is closed');
        }
        [, $length] = unpack('N', $data);
        if (! $data = $this->reader->read($length)) {
            throw new SpiderException('Connection is closed');
        }
        return $data;
    }


    /**
     * @param string $type
     */
    public function sendType($type): void
    {
        $this->type = $type;
        $command = pack('a4', $type);
        $this->writer->write($command);
        $this->writer->flush();
    }

    /**
     * subscribe
     *
     * @param callable $callback
     * @throws SpiderException
     */
    public function subscribe(callable $callback): void
    {
        if (! $this->socket) {
            $this->connect();
        }
        if (! $this->type) {
            $this->sendType(self::SUB_TYPE);
        }

        for(;;) {
            $length = $this->readFulWithBinary($this->socket, 4);
            $len = unpack("N", $length);
            $payload = $this->readFulWithBinary($this->socket, $len[1]);
            if ($payload === '' || $payload === false) {
                socket_close($this->socket);
                break;
            }
            // deal the data
            $callback($payload);
        }
    }

}
