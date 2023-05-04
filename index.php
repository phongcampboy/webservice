<?php

header("Access-Control-Allow-Origin: *");


require_once('module/mysql/MysqliDb.php');
require_once('dbconfig.php');

$f3 = require('lib/base.php');
//$f3->set('domain', 'http://10.100.100.243');

$f3->set('domain', 'http://tmnserver1.dyndns.tv');
$f3->config('config.ini');
$f3->config('router.ini');
$f3->set(
    'ONERROR',
    function ($f3) {
        echo $f3->get('ERROR.text');
        // if ($f3->get('ERROR.code') == '404') {
        //     $f3->reroute('/');
        // }
    }
);

$f3->route(
    'GET /',
    function () {
        echo "404 page not found!";
    }
);

$f3->route(
    'GET /info',
    function () {
        echo phpinfo();
    }
);

$f3->run();

