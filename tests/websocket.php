<?php

use function Co\run;

include __DIR__.'/../vendor/autoload.php';

run(static function () {
    $client = new Swoole\Coroutine\Http\Client('172.17.0.3', 8080);
    $client->upgrade('/ws');
    $i = 1;
    for (;;) {
        $msg = random_int(100000, 999999);
        $client->push(pack('Na*', strlen($msg), $msg));
        var_dump($client->recv());
    }

});