<?php

use PHPUnit\Framework\TestCase;
use Spider\Socket;
use Spider\SpiderException;

class SocketTest extends TestCase
{
    /**
     * @throws SpiderException
     */
    public function testPublish(): void
    {
        $spider = new Socket("127.0.0.1", "9501");
        for($i=0; $i< 100; $i++) {
            echo $spider->publish('http://ip.taobao.com/service/getIpInfo.php?ip='.$this->ip(), 'GET') . PHP_EOL;
        }
    }

    /**
     * @throws SpiderException
     */
    public function testSubscribe(): void
    {
        $spider = new Socket("127.0.0.1", "9501");
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