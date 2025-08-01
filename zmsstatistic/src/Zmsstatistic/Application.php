<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsclient\Http;

define(
    'ZMS_STATISTIC_SESSION_DURATION',
    getenv('ZMS_STATISTIC_SESSION_DURATION') ? getenv('ZMS_STATISTIC_SESSION_DURATION') : 28800
);

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_STATISTIC_TWIG_CACHE')) {
    $value = getenv('ZMS_STATISTIC_TWIG_CACHE');
    define('ZMS_STATISTIC_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));
}

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     *
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsstatistic';

    const DEBUG = false;

    const TWIG_CACHE = ZMS_STATISTIC_TWIG_CACHE;

    const SESSION_DURATION = ZMS_STATISTIC_SESSION_DURATION;

    public static $includeUrl = '/terminvereinbarung/statistic';
    /**
     * language preferences
     */
    public static $locale = 'de';
    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name'    => 'Deutsch',
            'locale'  => 'de_DE',
            'default' => true,
        ),
        // Other languages
        'en' => array(
            'name'    => 'English',
            'locale'  => 'en_GB',
        )
    );

    /**
     * image preferences
     */

    public static $isImageAllowed = false;

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
     */
    public static $http = null;

    public static $http_curl_config = array();

    const JSON_COMPRESS_LEVEL = 1;

    /**
    * config preferences
    */
    const CONFIG_SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = 'http://user:pass@host.tdl';
}
