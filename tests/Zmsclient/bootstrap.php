<?php
// define the application path as single global constant
define("APP_PATH", realpath(__DIR__));

chdir(__DIR__);
// use autoloading offered by composer, see composer.json for path settings
if (file_exists(APP_PATH . '/../../vendor/autoload.php')) {
    define('AUTOLOAD_PATH', APP_PATH . '/../../vendor');
} else {
    define('AUTOLOAD_PATH', APP_PATH . '/../../../..');
}
require_once(AUTOLOAD_PATH . '/autoload.php');

\BO\Zmsclient\Psr7\Client::$curlopt = [
    CURLOPT_SSLVERSION        => 0,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 15,
    //CURLOPT_VERBOSE => true,
];
\BO\Zmsclient\Tests\Base::$http_baseurl = 'https://eappointment.example.com/api/2';
