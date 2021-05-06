<?php


namespace Spider\IO;


interface IOInterface
{
    public function __construct(string $host, $port);
    public function read();
    public function write(string $msg): void ;
}