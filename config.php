<?php
// @codingStandardsIgnoreFile
 
define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');
define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'Zmsstatistic-ENV');
define('ZMS_CURL_TIMEOUT', getenv('ZMS_CURL_TIMEOUT') ? intval(getenv('ZMS_CURL_TIMEOUT')) : 25);

class App extends \BO\Zmsstatistic\Application
{
    const IDENTIFIER = ZMS_IDENTIFIER;
    const DEBUG = false;
    // Per default uses dir ./cache
    //const TWIG_CACHE = false;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = ZMS_API_URL;

    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => ZMS_CURL_TIMEOUT,
        //CURLOPT_VERBOSE => true,
    ];
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}
