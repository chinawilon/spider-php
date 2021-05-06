<?php


namespace Spider;


use Spider\IO\IOInterface;

class BuffIO
{

    /**
     * @var string
     */
    private $left = '';
    /**
     * @var IOInterface
     */
    private $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    public function write(string $msg): void
    {
        $this->left .= $msg;
    }

    public function flush(): void
    {
        $msg = $this->left;
        $this->left = '';
        $this->io->write($msg);

    }

    public function read(int $n)
    {
        for (;;) {
            if (strlen($this->left) >= $n) {
                $data = substr($this->left, 0, $n);
                $this->left = substr($this->left, $n);
                return $data;
            }
            if (!$msg = $this->io->read()) {
                return '';
            }
            $this->left .= $msg;
        }
    }

}