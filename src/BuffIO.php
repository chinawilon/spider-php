<?php


namespace Spider;


class BuffIO
{
    private $socket;

    private $left = '';

    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    public function write(string $msg): void
    {
        $this->left .= $msg;
    }

    public function flush(): void
    {
        $msg = $this->left;
        $this->left = '';
        socket_write($this->socket, $msg, strlen($msg));
    }

    public function read(int $n)
    {
        for (;;) {
            if (strlen($this->left) >= $n) {
                $data = substr($this->left, 0, $n);
                $this->left = substr($this->left, $n);
                return $data;
            }
            if (!$msg = socket_read($this->socket, 1024)) {
                socket_close($this->socket);
                return '';
            }
            $this->left .= $msg;
        }
    }

}