<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Helper\ModuleLoggerInitializer;
use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;

define(
    'ZMS_STATISTIC_SESSION_DURATION',
    (int) ((($value = getenv('ZMS_STATISTIC_SESSION_DURATION')) !== false && $value !== '') ? $value : 36000)
);

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_STATISTIC_TWIG_CACHE')) {
    $value = getenv('ZMS_STATISTIC_TWIG_CACHE');
    if ($value === 'false') {
        define('ZMS_STATISTIC_TWIG_CACHE', false);
    } elseif ($value === false || $value === '') {
        define('ZMS_STATISTIC_TWIG_CACHE', '/cache/');
    } else {
        define('ZMS_STATISTIC_TWIG_CACHE', $value);
    }
}

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     *
     */
    const string IDENTIFIER = 'zms';

    const string MODULE_NAME = 'zmsstatistic';

    public static ?CacheInterface $cache = null;

    const bool DEBUG = false;

    const string|false TWIG_CACHE = ZMS_STATISTIC_TWIG_CACHE;

    const int SESSION_DURATION = ZMS_STATISTIC_SESSION_DURATION;

    public static string $includeUrl = '/terminvereinbarung/statistic';
    /**
     * language preferences
     */
    public static string $locale = 'de';
    public static array $supportedLanguages = array(
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

    public static bool $isImageAllowed = false;

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
     */
    public static ?Http $http = null;

    public static array $http_curl_config = array();

    const int JSON_COMPRESS_LEVEL = 1;

    /**
    * config preferences
    */
    const string CONFIG_SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    /**
     * HTTP url for api
     */
    const string HTTP_BASE_URL = 'http://user:pass@host.tdl';

    public static function initialize(): void
    {
        ModuleLoggerInitializer::configure('ZMS_STATISTIC');
        self::$cache = ModuleLoggerInitializer::tryInitializeCache();
    }
}

Application::initialize();
