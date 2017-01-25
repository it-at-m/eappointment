<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://service.berlin.de/terminvereinbarung/api/2');

class App extends \BO\Zmsticketprinter\Application
{
    const IDENTIFIER = 'Zmsticketprinter-ENV';
    const APP_PATH = APP_PATH;
    const DEBUG = true;
    const SLIM_DEBUG = true;
    //const TWIG_CACHE = '/cache/';
    const MONOLOG_LOGLEVEL = 'debug';
    const HTTP_BASE_URL = ZMS_API_URL;
    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 9,
        //CURLOPT_VERBOSE => true,
    ];
}

// uncomment for testing
// App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));
