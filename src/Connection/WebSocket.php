<?php


namespace Spider\Connection;


use RuntimeException;
use Swoole\Coroutine\Http\Client;
use Swoole\WebSocket\CloseFrame;
use Swoole\WebSocket\Frame;

class WebSocket implements ConnectionInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(string $host, $port)
    {
        $this->client = new Client($host, $port, false);
    }

    public function upgrade(string $path): void
    {
        if (! $this->client->upgrade($path) ) {
            throw new RuntimeException("upgrade protocol error");
        }
    }

    public function write(string $msg): void
    {
        $this->client->push($msg);
    }

    public function read(): string
    {
        /**@var $frame Frame **/
        if ( ($frame = $this->client->recv()) && $frame->data !== 'close' && (!$frame instanceof CloseFrame)) {
            return $frame->data;
        }
        return '';
    }
}