<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');

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
        CURLOPT_TIMEOUT => 9,
        //CURLOPT_VERBOSE => true,
    ];
}
