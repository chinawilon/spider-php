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
     * @var BuffIO
     */
    protected $writer;
    /**
     * @var BuffIO
     */
    protected $reader;

    /**
     * Spider constructor.
     *
     * @param IOInterface $io
     */
    public function __construct(IOInterface $io)
    {
        $this->writer = new BuffIO($io);
        $this->reader = new BuffIO($io);
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
        if (! $payload = $this->reader->read($length)) {
            throw new SpiderException('Connection is closed');
        }
        return $payload;
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