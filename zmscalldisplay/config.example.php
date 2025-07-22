<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://example.com/api/');
define('ZMS_API_PROXY', getenv('ZMS_API_PROXY') ? getenv('ZMS_API_PROXY') : NULL);
define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'zms');
define('ZMS_MODULE_NAME', 'zmscalldisplay');
define('ZMS_CALLDISPLAY_TWIG_CACHE', getenv('ZMS_CALLDISPLAY_TWIG_CACHE') ?: '/cache/');

class App extends \BO\Zmscalldisplay\Application
{
    const IDENTIFIER = ZMS_IDENTIFIER;
    const APP_PATH = APP_PATH;
    const DEBUG = false;
    const TWIG_CACHE = ZMS_CALLDISPLAY_TWIG_CACHE;
    const HTTP_BASE_URL = ZMS_API_URL;

    /**
     * Name of the module
     */
    const MODULE_NAME = ZMS_MODULE_NAME;

    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 9,
        CURLOPT_PROXY => ZMS_API_PROXY,
        //CURLOPT_VERBOSE => true,
    ];
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}
