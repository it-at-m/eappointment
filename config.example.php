<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');

define('ZMS_API_PROXY', getenv('ZMS_API_PROXY') ? getenv('ZMS_API_PROXY') : NULL);

class App extends \BO\Zmsstatistic\Application
{
    const IDENTIFIER = 'Zmsstatistic-ENV';
    const DEBUG = false;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = ZMS_API_URL;

    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_PROXY => ZMS_API_PROXY,
        //CURLOPT_VERBOSE => true,
    ];
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}
