<?php


namespace Spider\IO;



use Spider\Connection\ConnectionInterface;

class BuffIO implements IOInterface
{

    /**
     * @var string
     */
    private $left = '';
    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function write(string $msg): void
    {
        $this->connection->write($msg);
    }

    public function read(?int $n)
    {
        for (;;) {
            if (strlen($this->left) >= $n) {
                $data = substr($this->left, 0, $n);
                $this->left = substr($this->left, $n);
                return $data;
            }
            if (!$msg = $this->connection->read()) {
                return '';
            }
            $this->left .= $msg;
        }
    }

}