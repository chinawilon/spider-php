<?php


namespace Spider\IO;


use Spider\Connection\ConnectionInterface;

class DataIO implements IOInterface
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * DataIO constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function read(?int $n): string
    {
        return $this->connection->read();
    }

    public function write(string $msg): void
    {
        $this->connection->write($msg);
    }
}