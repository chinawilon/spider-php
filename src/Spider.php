<?php


namespace Spider;




use Spider\IO\IOInterface;

class Spider
{
    /**
     * @var string
     */
    protected $type;

    public const SUB_TYPE = 'SUB ';
    public const PUB_TYPE = 'PUB ';

    /**
     * @var IOInterface
     */
    protected $writer;
    /**
     * @var IOInterface
     */
    protected $reader;
    /**
     * @var bool
     */
    private $needPack;

    /**
     * Spider constructor.
     *
     * @param IOInterface $io
     * @param bool $needPack
     */
    public function __construct(IOInterface $io, $needPack = true)
    {
        $this->writer = $io;
        $this->reader = clone $io;
        $this->needPack = $needPack;
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
    public function publish($uri, $method, $header = null, $body = '', $timeout = 5): string
    {
        if (! $this->type) {
            $this->sendType(self::PUB_TYPE);
        }

        $this->sendRequest($uri, $method, $header, $body, $timeout);

        if ( $this->needPack ) {
            if (!$data = $this->reader->read(4)) {
                throw new SpiderException('Connection is closed');
            }
            [, $length] = unpack('N', $data);
            if (!$payload = $this->reader->read($length)) {
                throw new SpiderException('Connection is closed');
            }
        } else {
            $payload = $this->reader->read(null);
        }
        return $payload;
    }


    /**
     * send request
     *
     * @param $uri
     * @param $method
     * @param $header
     * @param $body
     * @param $timeout
     */
    public function sendRequest($uri, $method, $header, $body, $timeout): void
    {
        $json = [
            'uri' => $uri,
            'method' => $method,
            'header' => $header,
            'body' => $body,
            'timeout' => $timeout
        ];
        $command = json_encode($json);
        if ( $this->needPack ) {
            $command = pack('Na*', strlen($command), $command);
        }
        $this->writer->write($command);
    }


    /**
     * @param string $type
     */
    public function sendType($type): void
    {
        $this->type = $type;
        $command = pack('a4', $type);
        $this->writer->write($command);
    }

    /**
     * subscribe
     *
     * @param callable $callback
     * @throws SpiderException
     */
    public function subscribe(callable $callback): void
    {
        if (! $this->type) {
            $this->sendType(self::SUB_TYPE);
        }

        for(;;) {
            if (! $data = $this->reader->read(4) ) {
                throw new SpiderException('Connection is closed');
            }
            [, $length] = unpack('N', $data);
            if (! $payload = $this->reader->read($length)) {
                throw new SpiderException('Connection is closed');
            }
            // deal the data
            $callback($payload);
        }
    }

}