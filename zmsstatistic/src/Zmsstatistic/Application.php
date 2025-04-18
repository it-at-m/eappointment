<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

define(
    'ZMS_STATISTIC_SESSION_DURATION',
    getenv('ZMS_STATISTIC_SESSION_DURATION') ? getenv('ZMS_STATISTIC_SESSION_DURATION') : 28800
);

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     *
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsstatistic';

    const DEBUG = false;

    const TWIG_CACHE = '/cache/';

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
    const CONFIG_SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = 'http://user:pass@host.tdl';

    /**
     * Cache configuration
     */
    public static ?CacheInterface $cache = null;
    public static string $PSR16_CACHE_DIR_ZMSSTATISTIC;
    public static int $PSR16_CACHE_TTL_ZMSSTATISTIC;

    public static function initialize(): void
    {
        self::initializeCache();
    }

    private static function initializeCache(): void
    {
        self::$PSR16_CACHE_DIR_ZMSSTATISTIC = getenv('PSR16_CACHE_DIR_ZMSSTATISTIC') ?: dirname(dirname(dirname(__DIR__))) . '/cache_psr16';
        self::$PSR16_CACHE_TTL_ZMSSTATISTIC = (int) (getenv('PSR16_CACHE_TTL_ZMSSTATISTIC') ?: 3600);
        self::validateCacheDirectory();
        self::setupCache();
    }

    private static function validateCacheDirectory(): void
    {
        if (!is_dir(self::$PSR16_CACHE_DIR_ZMSSTATISTIC) && !mkdir(self::$PSR16_CACHE_DIR_ZMSSTATISTIC, 0750, true)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', self::$PSR16_CACHE_DIR_ZMSSTATISTIC));
        }

        if (!is_writable(self::$PSR16_CACHE_DIR_ZMSSTATISTIC)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" is not writable', self::$PSR16_CACHE_DIR_ZMSSTATISTIC));
        }
    }

    private static function setupCache(): void
    {
        $psr16 = new FilesystemAdapter(namespace: '', defaultLifetime: self::$PSR16_CACHE_TTL_ZMSSTATISTIC, directory: self::$PSR16_CACHE_DIR_ZMSSTATISTIC);
        self::$cache = new Psr16Cache($psr16);
    }
}
