<?php


namespace Spider\IO;


interface IOInterface
{
    public function write(string $msg);
    public function read(?int $n);
}