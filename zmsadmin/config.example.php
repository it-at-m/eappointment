<?php
// @codingStandardsIgnoreFile
 
define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');
define('ZMS_CURL_TIMEOUT', getenv('ZMS_CURL_TIMEOUT') ? intval(getenv('ZMS_CURL_TIMEOUT')) : 25);
define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'zms');
define('ZMS_MODULE_NAME', 'zmsadmin');
$value = getenv('ZMS_ADMIN_TWIG_CACHE');
define('ZMS_ADMIN_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));

class App extends \BO\Zmsadmin\Application
{
    const IDENTIFIER = ZMS_IDENTIFIER;
    const DEBUG = false;
    const TWIG_CACHE = ZMS_ADMIN_TWIG_CACHE;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = ZMS_API_URL;

    /**
     * Name of the module
     */
    const MODULE_NAME = ZMS_MODULE_NAME;

    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => ZMS_CURL_TIMEOUT,
        //CURLOPT_VERBOSE => true,
    ];
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}
