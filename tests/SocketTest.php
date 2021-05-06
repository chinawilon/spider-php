<?php

use Spider\IO\PHPSocket;
use PHPUnit\Framework\TestCase;
use Spider\IO\SwooleSocket;
use Spider\SpiderException;

class SocketTest extends TestCase
{

    /**
     * @var string
     */
    protected $host = '172.17.0.2';
    /**
     * @var int
     */
    protected $port = 8080;

    /**
     * @throws SpiderException
     */
    public function testPHPPublish(): void
    {
        $spider = new Spider\Spider(new PHPSocket($this->host, $this->port));
        for($i=0; $i< 1000; $i++) {
            echo $spider->publish('http://ip.taobao.com/service/getIpInfo.php?ip='.$this->ip(), 'GET') . PHP_EOL;
        }
    }

    /**
     * @throws SpiderException
     */
    public function testSwoolePublish(): void
    {
        $spider = new Spider\Spider(new SwooleSocket($this->host, $this->port));
        for ($i = 0; $i < 1000; $i++) {
            echo $spider->publish('http://ip.taobao.com/service/getIpInfo.php?ip=' . $this->ip(), 'GET') . PHP_EOL;
        }
    }

    /**
     * @throws SpiderException
     */
    public function testPHPSubscribe(): void
    {
        $spider = new Spider\Spider(new PHPSocket($this->host, $this->port));
        $spider->subscribe(static function ($json){
            file_put_contents("test.txt", $json.PHP_EOL, FILE_APPEND);
        });
    }

    /**
     * @throws SpiderException
     */
    public function testSwooleSubscribe(): void
    {
        $spider = new Spider\Spider(new SwooleSocket($this->host, $this->port));
        $spider->subscribe(static function ($json){
            file_put_contents("test.txt", $json.PHP_EOL, FILE_APPEND);
        });
    }


    // random ip
    public function ip(): string
    {
        $ip_long = array(
            array('607649792', '608174079'), // 36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), // 61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), // 222.16.0.0-222.95.255.255
        );
        try {
            $rand_key = random_int(0, 9);
            return long2ip(random_int($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
        } catch (Exception $e) {
            return "8.8.8.8";
        }

    }
}