<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://example.com/api/');

class App extends \BO\Zmscalldisplay\Application
{
    const IDENTIFIER = 'Zmscalldisplay-ENV';
    const APP_PATH = APP_PATH;
    const DEBUG = false;
    const SLIM_DEBUG = false;
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
