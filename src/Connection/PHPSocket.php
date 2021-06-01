<?php


namespace Spider\Connection;


use RuntimeException;

class PHPSocket implements ConnectionInterface
{
    private $socket;

    /**
     * PHPSocket constructor.
     *
     * @param string $host
     * @param $port
     */
    public function __construct(string $host, $port)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! $socket) {
            throw new RuntimeException("PHP socket create exception");
        }
        if (! socket_connect($socket, $host, $port)) {
            throw New RuntimeException("PHP socket connect error");
        }
        $this->socket = $socket;
    }

    public function read(): string
    {
        if (!$msg = socket_read($this->socket, 1024)) {
            socket_close($this->socket);
            return '';
        }
        return $msg;
    }

    public function write(string $msg): void
    {
        socket_write($this->socket, $msg, strlen($msg));
    }

}