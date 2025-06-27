<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsticketprinter;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsticketprinter';

    public const DEBUG = false;

    /**
     * language preferences
     */
    public static $locale = 'de';

    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name' => 'Deutsch',
            'locale' => 'de_DE.utf-8',
            'default' => true,
        ),
        'en' => array(
            'name' => 'English',
            'locale' => 'en_GB.utf-8',
            'default' => false,
        )
    );

    public static $now = '';

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
     */

    public static $http = null;

    public static $http_curl_config = array();

    public const JSON_COMPRESS_LEVEL = 1;

    /**
     * HTTP url for api
     */
    public const HTTP_BASE_URL = 'http://user:pass@host.tdl';
    public const SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    public const CLIENTKEY = '';

    /**
     * signature key for url signature to save query paramter with hash
     */
    public static $urlSignatureSecret = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    /**
     * Cache instance
     *
     * @var \Psr\SimpleCache\CacheInterface|null $cache
     */
    public static ?CacheInterface $cache = null;

    /**
     * Cache directory for PSR-16 cache
     *
     * @var string $PSR16_CACHE_DIR_ZMSTICKETPRINTER
     */
    public static string $PSR16_CACHE_DIR_ZMSTICKETPRINTER;

    /**
     * Cache TTL for PSR-16 cache
     *
     * @var int $PSR16_CACHE_TTL_ZMSTICKETPRINTER
     */
    public static int $PSR16_CACHE_TTL_ZMSTICKETPRINTER;

    /**
     * Initialize the application
     *
     * @return void
     */
    public static function initialize(): void
    {
        self::initializeCache();
    }

    /**
     * Initialize the cache
     *
     * @return void
     */
    public static function initializeCache(): void
    {
        self::$PSR16_CACHE_DIR_ZMSTICKETPRINTER = getenv('PSR16_CACHE_DIR_ZMSTICKETPRINTER') ?: dirname(dirname(dirname(__DIR__))) . '/cache_psr16';
        self::$PSR16_CACHE_TTL_ZMSTICKETPRINTER = (int) (getenv('PSR16_CACHE_TTL_ZMSTICKETPRINTER') ?: 3600);

        self::validateCacheDirectory();
        self::setupCache();
    }

    /**
     * Validate the cache directory
     *
     * @return void
     * @throws \Exception
     */
    private static function validateCacheDirectory(): void
    {
        if (!is_dir(self::$PSR16_CACHE_DIR_ZMSTICKETPRINTER)) {
            if (!mkdir(self::$PSR16_CACHE_DIR_ZMSTICKETPRINTER, 0750, true)) {
                throw new \Exception('Could not create cache directory: ' . self::$PSR16_CACHE_DIR_ZMSTICKETPRINTER);
            }
        }

        if (!is_writable(self::$PSR16_CACHE_DIR_ZMSTICKETPRINTER)) {
            throw new \Exception('Cache directory is not writable: ' . self::$PSR16_CACHE_DIR_ZMSTICKETPRINTER);
        }
    }

    /**
     * Setup the cache
     *
     * @return void
     */
    private static function setupCache(): void
    {
        $psr16 = new FilesystemAdapter(namespace: '', defaultLifetime: self::$PSR16_CACHE_TTL_ZMSTICKETPRINTER, directory: self::$PSR16_CACHE_DIR_ZMSTICKETPRINTER);
        self::$cache = new Psr16Cache($psr16);
    }
}
