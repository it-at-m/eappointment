<?php
// @codingStandardsIgnoreFile

define(
    'ZMS_API_URL',
    (($value = getenv('ZMS_API_URL')) !== false && $value !== '')
        ? $value
        : 'https://localhost/terminvereinbarung/api/2'
);
define(
    'ZMS_CURL_TIMEOUT',
    (($value = getenv('ZMS_CURL_TIMEOUT')) !== false && $value !== '')
        ? intval($value)
        : 25
);
define(
    'ZMS_IDENTIFIER',
    (($value = getenv('ZMS_IDENTIFIER')) !== false && $value !== '')
        ? $value
        : 'zms'
);
define('ZMS_MODULE_NAME', 'zmsstatistic');
$value = getenv('ZMS_STATISTIC_TWIG_CACHE');
define(
    'ZMS_STATISTIC_TWIG_CACHE',
    ($value === 'false')
        ? false
        : (($value !== false && $value !== '') ? $value : '/cache/')
);

class App extends \BO\Zmsstatistic\Application
{
    const string IDENTIFIER = ZMS_IDENTIFIER;
    const bool DEBUG = false;
    const mixed TWIG_CACHE = ZMS_STATISTIC_TWIG_CACHE;

    /**
     * HTTP url for api
     */
    const string HTTP_BASE_URL = ZMS_API_URL;

    /**
     * Name of the module
     */
    const string MODULE_NAME = ZMS_MODULE_NAME;

    public static array $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => ZMS_CURL_TIMEOUT,
        //CURLOPT_VERBOSE => true,
    ];
}

if (($timeAdjust = getenv('ZMS_TIMEADJUST')) !== false && $timeAdjust !== '') {
    App::$now = new DateTimeImmutable(date($timeAdjust), new DateTimeZone('Europe/Berlin'));
}
