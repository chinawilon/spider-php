<?php


namespace Spider\Connection;


use Spider\SpiderException;
use Swoole\Client;

class SwooleSocket implements ConnectionInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * SwooleSocket constructor.
     *
     * @param string $host
     * @param $port
     * @throws SpiderException
     */
    public function __construct(string $host, $port)
    {
        $this->client = new Client(SWOOLE_SOCK_TCP);
        if (! $this->client->connect($host, $port) ) {
            throw new SpiderException("Swoole socket connect error");
        }
    }

    public function read(): string
    {
        return $this->client->recv() ?? '';
    }

    public function write(string $msg): void
    {
        $this->client->send($msg);
    }
}