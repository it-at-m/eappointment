<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_API_TWIG_CACHE')) {
    $value = getenv('ZMS_API_TWIG_CACHE');
    define('ZMS_API_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));
}

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsapi';

    public static ?CacheInterface $cache = null;
    // Cache config
    public static string $CACHE_DIR;
    public static int $SOURCE_CACHE_TTL;

    /**
     * @var Bool DEBUG
     */
    const DEBUG = false;
    const TWIG_CACHE = ZMS_API_TWIG_CACHE;

    /**
     * @var Bool DB_ENABLE_WSREPSYNCWAIT
     */
    const DB_ENABLE_WSREPSYNCWAIT = false;

    /**
     * @var Bool RIGHTSCHECK_ENABLED
     */
    const RIGHTSCHECK_ENABLED = true;

    /**
     * @var String DB_DSN_READONLY
     */
    const DB_DSN_READONLY = 'mysql:dbname=zmsbo;host=127.0.0.1';

    /**
     * @var String DB_DSN_READWRITE
     */
    const DB_DSN_READWRITE = 'mysql:dbname=zmsbo;host=127.0.0.1';

    /**
     * temporary db name for using dldb data
     * @var String DB_STARTINFO
     */
    const DB_STARTINFO = 'startinfo';

    /**
     * @var String DB_USERNAME
     */
    const DB_USERNAME = 'server';

    /**
     * @var String DB_PASSWORD
     */
    const DB_PASSWORD = 'internet';

    /**
     * @var String DB_IS_GALERA
     */
    const DB_IS_GALERA = true;

    /**
     * @var String Security Token for Api Access -> get config for example
     */
    const SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    /**
     * language preferences
     */

    public static $locale = 'de';

    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name'    => 'Deutsch',
            'locale'  => 'de_DE.utf-8',
            'default' => true,
        )
    );

    /**
     * dldb data path
     */
    public static $data = '/data';

    /**
     * @var \DateTimeInterface $now time to use for today (testing)
     */
    public static $now = null;

    public static function getNow()
    {
        if (self::$now instanceof \DateTimeInterface) {
            return self::$now;
        }
        return new \DateTimeImmutable();
    }

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