<?php


namespace Spider\Connection;


interface ConnectionInterface
{
    public function __construct(string $host, $port);
    public function read(): string ;
    public function write(string $msg): void ;
}