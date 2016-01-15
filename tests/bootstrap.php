<?php
require(__DIR__ . '/../vendor/autoload.php');
\BO\Zmsclient\Psr7\Client::$curlopt = [
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 3,
    //CURLOPT_VERBOSE => true,
];
\BO\Zmsclient\Tests\Base::$http_baseurl = 'https://localhost/terminvereinbarung/api/2';
