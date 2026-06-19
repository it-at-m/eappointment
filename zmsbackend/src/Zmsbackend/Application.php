<?php

/**
 * @package Zmsbackend
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend;

use BO\Slim\LoggerService;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_BACKEND_TWIG_CACHE')) {
    $value = getenv('ZMS_BACKEND_TWIG_CACHE');
    if ($value === false || $value === '') {
        $value = getenv('ZMS_API_TWIG_CACHE');
    }
    define('ZMS_BACKEND_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));
}

define(
    'ZMSBACKEND_SESSION_DURATION',
    getenv('ZMSBACKEND_SESSION_DURATION') ? getenv('ZMSBACKEND_SESSION_DURATION') : (
        getenv('ZMSDB_SESSION_DURATION') ? getenv('ZMSDB_SESSION_DURATION') : 28800
    )
);

class Application extends \BO\Slim\Application
{
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsbackend';

    public static ?CacheInterface $cache = null;
    public static string $CACHE_DIR;
    public static int $SOURCE_CACHE_TTL;

    public static int $LOGGER_MAX_REQUESTS;
    public static int $LOGGER_MAX_ERROR_REQUESTS;
    public static int $LOGGER_RESPONSE_LENGTH;
    public static int $LOGGER_STACK_LINES;
    public static int $LOGGER_MESSAGE_SIZE;
    public static int $LOGGER_CACHE_TTL;
    public static int $LOGGER_MAX_RETRIES;
    public static int $LOGGER_BACKOFF_MIN;
    public static int $LOGGER_BACKOFF_MAX;
    public static int $LOGGER_LOCK_TIMEOUT;

    public static int $MAX_STRING_LENGTH;
    public static int $MAX_RECURSION_DEPTH;

    const DEBUG = false;
    const TWIG_CACHE = ZMS_BACKEND_TWIG_CACHE;
    const SESSION_DURATION = ZMSBACKEND_SESSION_DURATION;

    const DB_ENABLE_WSREPSYNCWAIT = false;
    const RIGHTSCHECK_ENABLED = true;

    const DB_DSN_READONLY = 'mysql:dbname=zmsbo;host=127.0.0.1';
    const DB_DSN_READWRITE = 'mysql:dbname=zmsbo;host=127.0.0.1';
    const DB_STARTINFO = 'startinfo';
    const DB_USERNAME = 'server';
    const DB_PASSWORD = 'internet';
    const DB_IS_GALERA = true;
    const SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    public static $locale = 'de';
    public static $supportedLanguages = [
        'de' => [
            'name' => 'Deutsch',
            'locale' => 'de_DE.utf-8',
            'default' => true,
        ],
    ];

    public static $data = '/data';
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

    private static function envInt(string $primaryKey, string $fallbackKey, int $default): int
    {
        foreach ([$primaryKey, $fallbackKey] as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') {
                return (int) $value;
            }
        }

        return $default;
    }

    private static function initializeLogger(): void
    {
        self::$LOGGER_MAX_REQUESTS = self::envInt('ZMS_BACKEND_LOGGER_MAX_REQUESTS', 'ZMS_API_LOGGER_MAX_REQUESTS', 1000);
        self::$LOGGER_MAX_ERROR_REQUESTS = self::envInt('ZMS_BACKEND_LOGGER_MAX_ERROR_REQUESTS', 'ZMS_API_LOGGER_MAX_ERROR_REQUESTS', 0);
        self::$LOGGER_RESPONSE_LENGTH = self::envInt('ZMS_BACKEND_LOGGER_RESPONSE_LENGTH', 'ZMS_API_LOGGER_RESPONSE_LENGTH', 1048576);
        self::$LOGGER_STACK_LINES = self::envInt('ZMS_BACKEND_LOGGER_STACK_LINES', 'ZMS_API_LOGGER_STACK_LINES', 20);
        self::$LOGGER_MESSAGE_SIZE = self::envInt('ZMS_BACKEND_LOGGER_MESSAGE_SIZE', 'ZMS_API_LOGGER_MESSAGE_SIZE', 8192);
        self::$LOGGER_CACHE_TTL = self::envInt('ZMS_BACKEND_LOGGER_CACHE_TTL', 'ZMS_API_LOGGER_CACHE_TTL', 60);
        self::$LOGGER_MAX_RETRIES = self::envInt('ZMS_BACKEND_LOGGER_MAX_RETRIES', 'ZMS_API_LOGGER_MAX_RETRIES', 3);
        self::$LOGGER_BACKOFF_MIN = self::envInt('ZMS_BACKEND_LOGGER_BACKOFF_MIN', 'ZMS_API_LOGGER_BACKOFF_MIN', 100);
        self::$LOGGER_BACKOFF_MAX = self::envInt('ZMS_BACKEND_LOGGER_BACKOFF_MAX', 'ZMS_API_LOGGER_BACKOFF_MAX', 1000);
        self::$LOGGER_LOCK_TIMEOUT = self::envInt('ZMS_BACKEND_LOGGER_LOCK_TIMEOUT', 'ZMS_API_LOGGER_LOCK_TIMEOUT', 5);
        LoggerService::configure(self::getLoggerConfig());
    }

    private static function initializeRequestLimits(): void
    {
        self::$MAX_STRING_LENGTH = (int) (getenv('MAX_STRING_LENGTH') ?: 32768);
        self::$MAX_RECURSION_DEPTH = (int) (getenv('MAX_RECURSION_DEPTH') ?: 10);
    }

    private static function validateCacheDirectory(): void
    {
        if (!is_dir(self::$CACHE_DIR)) {
            if (!@mkdir(self::$CACHE_DIR, 0750, true) && !is_dir(self::$CACHE_DIR)) {
                throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', self::$CACHE_DIR));
            }
        }

        if (!is_writable(self::$CACHE_DIR)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" is not writable', self::$CACHE_DIR));
        }
    }

    private static function setupCache(): void
    {
        $psr6 = new FilesystemAdapter(namespace: '', defaultLifetime: self::$SOURCE_CACHE_TTL, directory: self::$CACHE_DIR);
        self::$cache = new Psr16Cache($psr6);
        LoggerService::$cache = self::$cache;
    }

    public static function getLoggerConfig(): array
    {
        return [
            'maxRequests' => self::$LOGGER_MAX_REQUESTS,
            'maxErrorRequests' => self::$LOGGER_MAX_ERROR_REQUESTS,
            'responseLength' => self::$LOGGER_RESPONSE_LENGTH,
            'stackLines' => self::$LOGGER_STACK_LINES,
            'messageSize' => self::$LOGGER_MESSAGE_SIZE,
            'cacheTtl' => self::$LOGGER_CACHE_TTL,
            'maxRetries' => self::$LOGGER_MAX_RETRIES,
            'backoffMin' => self::$LOGGER_BACKOFF_MIN,
            'backoffMax' => self::$LOGGER_BACKOFF_MAX,
            'lockTimeout' => self::$LOGGER_LOCK_TIMEOUT,
        ];
    }

    public static function getRequestLimits(): array
    {
        return [
            'maxStringLength' => self::$MAX_STRING_LENGTH,
            'maxRecursionDepth' => self::$MAX_RECURSION_DEPTH,
        ];
    }

    public static function initialize(): void
    {
        self::initializeLogger();
        self::initializeCache();
        self::initializeRequestLimits();
    }
}

Application::initialize();
