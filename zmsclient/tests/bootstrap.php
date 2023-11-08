<?php
// define the application path as single global constant

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__));
}

if (!defined('ZMS_API_URL')) {
    define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'http://mockup:8083');
}

if (file_exists(APP_PATH . '/../vendor/autoload.php')) {
    define('VENDOR_PATH', APP_PATH . '/../vendor');
} else {
    define('VENDOR_PATH', APP_PATH . '/../../../');
}
require_once(VENDOR_PATH . '/autoload.php');
require(APP_PATH . '/config.php');

\BO\Zmsclient\Psr7\Client::$curlopt = [
    CURLOPT_SSLVERSION        => 0,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 15,
    //CURLOPT_VERBOSE => true,
];
\BO\Zmsclient\Tests\Base::$http_baseurl = ZMS_API_URL;
