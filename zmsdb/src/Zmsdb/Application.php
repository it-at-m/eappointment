<?php

/**
 * @package Zmsdb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdb;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

define(
    'ZMSDB_SESSION_DURATION',
    getenv('ZMSDB_SESSION_DURATION') ? getenv('ZMSDB_SESSION_DURATION') : 28800
);

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsdb';

    const DEBUG = false;

    const SESSION_DURATION = ZMSDB_SESSION_DURATION;

    public static ?CacheInterface $cache = null;
    // Cache config
    public static string $CACHE_DIR;
    public static int $SOURCE_CACHE_TTL;

    /**
     * Default parameters for templates
     */
    public static $templatedefaults = array();

    /**
     * Default parameters for middleware HttpBasicAuth
     */
    public static $httpBasicAuth = array();

    /**
     * image preferences
     */
    public static $isImageAllowed = false;

    /**
     * language preferences
     */
    const MULTILANGUAGE = true;
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

    private static function initializeCache(): void
    {
        self::$CACHE_DIR = getenv('CACHE_DIR') ?: __DIR__ . '/cache';
        self::$SOURCE_CACHE_TTL = (int) (getenv('SOURCE_CACHE_TTL') ?: 3600);
        self::validateCacheDirectory();
        self::setupCache();
    }

    private static function validateCacheDirectory(): void
    {
        if (!is_dir(self::$CACHE_DIR) && !mkdir(self::$CACHE_DIR, 0750, true)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', self::$CACHE_DIR));
        }

        if (!is_writable(self::$CACHE_DIR)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" is not writable', self::$CACHE_DIR));
        }
    }

    private static function setupCache(): void
    {
        $psr6 = new FilesystemAdapter(namespace: '', defaultLifetime: self::$SOURCE_CACHE_TTL, directory: self::$CACHE_DIR);
        self::$cache = new Psr16Cache($psr6);
    }

    public static function initialize(): void
    {
        self::initializeCache();
    }
}

Application::initialize();
