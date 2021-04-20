<?php

use Spider\Socket;

require __DIR__.'/../vendor/autoload.php';

$spider = new Socket("127.0.0.1", "8080");
$spider->subscribe(static function ($json){
    echo 'yes';
});
