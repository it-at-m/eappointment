<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://service.berlin.de/terminvereinbarung/api/2');

class App extends \BO\Zmsticketprinter\Application
{
    const IDENTIFIER = 'Zmsticketprinter-ENV';
    const APP_PATH = APP_PATH;
    const DEBUG = false;
    //const TWIG_CACHE = '/cache/';
    const HTTP_BASE_URL = ZMS_API_URL;
    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 25,
        //CURLOPT_VERBOSE => true,
    ];
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}
