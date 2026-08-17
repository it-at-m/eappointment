<?php

/**
 * @package Zmsbackend
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend;

use BO\Slim\LoggerService;
use BO\Slim\Traits\CacheInitializationTrait;
use Psr\SimpleCache\CacheInterface;

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_BACKEND_TWIG_CACHE')) {
    $value = getenv('ZMS_BACKEND_TWIG_CACHE');
    if ($value === false || $value === '' || $value === 'false') {
        define('ZMS_BACKEND_TWIG_CACHE', false);
    } else {
        define('ZMS_BACKEND_TWIG_CACHE', $value);
    }
}

define(
    'ZMSBACKEND_SESSION_DURATION',
    (($value = getenv('ZMSBACKEND_SESSION_DURATION')) !== false && $value !== '') ? $value : (
        (($value = getenv('ZMSDB_SESSION_DURATION')) !== false && $value !== '') ? $value : 36000
    )
);

class Application extends \BO\Slim\Application
{
    use CacheInitializationTrait;

    const string IDENTIFIER = 'zms';

    const string MODULE_NAME = 'zmsbackend';

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

    const bool DEBUG = false;
    const TWIG_CACHE = ZMS_BACKEND_TWIG_CACHE;
    const SESSION_DURATION = ZMSBACKEND_SESSION_DURATION;

    const bool DB_ENABLE_WSREPSYNCWAIT = false;
    const bool RIGHTSCHECK_ENABLED = true;

    const string DB_DSN_READONLY = 'mysql:dbname=zmsbo;host=127.0.0.1';
    const string DB_DSN_READWRITE = 'mysql:dbname=zmsbo;host=127.0.0.1';
    const string DB_STARTINFO = 'startinfo';
    const string DB_USERNAME = 'server';
    const string DB_PASSWORD = 'internet';
    const bool DB_IS_GALERA = true;
    const string SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

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

    /**
     * @psalm-api
     */
    public static function getNow(): \DateTimeInterface
    {
        if (self::$now instanceof \DateTimeInterface) {
            return self::$now;
        }
        return new \DateTimeImmutable();
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private static function initializeLogger(): void
    {
        self::$LOGGER_MAX_REQUESTS = (int) (getenv('ZMS_BACKEND_LOGGER_MAX_REQUESTS') ?: 1000);
        self::$LOGGER_MAX_ERROR_REQUESTS = (int) (getenv('ZMS_BACKEND_LOGGER_MAX_ERROR_REQUESTS') ?: 0);
        self::$LOGGER_RESPONSE_LENGTH = (int) (getenv('ZMS_BACKEND_LOGGER_RESPONSE_LENGTH') ?: 1048576);
        self::$LOGGER_STACK_LINES = (int) (getenv('ZMS_BACKEND_LOGGER_STACK_LINES') ?: 20);
        self::$LOGGER_MESSAGE_SIZE = (int) (getenv('ZMS_BACKEND_LOGGER_MESSAGE_SIZE') ?: 8192);
        self::$LOGGER_CACHE_TTL = (int) (getenv('ZMS_BACKEND_LOGGER_CACHE_TTL') ?: 60);
        self::$LOGGER_MAX_RETRIES = (int) (getenv('ZMS_BACKEND_LOGGER_MAX_RETRIES') ?: 3);
        self::$LOGGER_BACKOFF_MIN = (int) (getenv('ZMS_BACKEND_LOGGER_BACKOFF_MIN') ?: 100);
        self::$LOGGER_BACKOFF_MAX = (int) (getenv('ZMS_BACKEND_LOGGER_BACKOFF_MAX') ?: 1000);
        self::$LOGGER_LOCK_TIMEOUT = (int) (getenv('ZMS_BACKEND_LOGGER_LOCK_TIMEOUT') ?: 5);
        LoggerService::configure(self::getLoggerConfig());
    }

    private static function initializeRequestLimits(): void
    {
        self::$MAX_STRING_LENGTH = (int) (getenv('MAX_STRING_LENGTH') ?: 32768);
        self::$MAX_RECURSION_DEPTH = (int) (getenv('MAX_RECURSION_DEPTH') ?: 10);
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

    /** @psalm-api */
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
        self::initializeCache(__DIR__ . '/cache');
        self::initializeRequestLimits();
    }
}

Application::initialize();
