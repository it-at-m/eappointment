<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @TODO: Refactor this class into smaller focused classes (LoggerInitializer, MiddlewareInitializer) to reduce complexity
 */
class Application extends \BO\Slim\Application
{
    public const IDENTIFIER = 'zms';
    public const MODULE_NAME = 'zmscitizenapi';
    public static string $source_name = 'dldb';
    public static $http = null;
    public static array $http_curl_config = [];
    public static ?CacheInterface $cache = null;
    // Cache config
    public static string $CACHE_DIR;
    public static int $SOURCE_CACHE_TTL;
    public static bool $MAINTENANCE_MODE_ENABLED;
    // Logger config

    public static int $LOGGER_MAX_REQUESTS;
    public static int $LOGGER_RESPONSE_LENGTH;
    public static int $LOGGER_STACK_LINES;
    public static int $LOGGER_MESSAGE_SIZE;
    public static int $LOGGER_CACHE_TTL;
    public static int $LOGGER_MAX_RETRIES;
    public static int $LOGGER_BACKOFF_MIN;
    public static int $LOGGER_BACKOFF_MAX;
    public static int $LOGGER_LOCK_TIMEOUT;
    // Captcha config
    public static bool $CAPTCHA_ENABLED;
    public static string $CAPTCHA_TOKEN_SECRET;
    public static int $CAPTCHA_TOKEN_TTL;
    public static string $ALTCHA_CAPTCHA_SITE_KEY;
    public static string $ALTCHA_CAPTCHA_SITE_SECRET;
    public static string $ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE;
    public static string $ALTCHA_CAPTCHA_ENDPOINT_VERIFY;
    // Rate limiting config
    public static int $RATE_LIMIT_MAX_REQUESTS;
    public static int $RATE_LIMIT_CACHE_TTL;
    public static int $RATE_LIMIT_MAX_RETRIES;
    public static int $RATE_LIMIT_BACKOFF_MIN;
    public static int $RATE_LIMIT_BACKOFF_MAX;
    public static int $RATE_LIMIT_LOCK_TIMEOUT;
    // Request limits config
    public static int $MAX_REQUEST_SIZE;
    public static int $MAX_STRING_LENGTH;
    public static int $MAX_RECURSION_DEPTH;
    // IP Filter config
    public static string $IP_BLACKLIST;

    public static string $ACCESS_UNPUBLISHED_ON_DOMAIN;

    public static function initialize(): void
    {
        self::initializeMaintenanceMode();
        self::initializeLogger();
        self::initializeCaptcha();
        self::initializeCache();
        self::initializeMiddleware();
    }

    private static function initializeMaintenanceMode(): void
    {
        self::$MAINTENANCE_MODE_ENABLED = filter_var(getenv('MAINTENANCE_ENABLED'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @TODO: Extract logger initialization logic into a dedicated LoggerInitializer class
     */
    private static function initializeLogger(): void
    {
        self::$LOGGER_MAX_REQUESTS = (int) (getenv('LOGGER_MAX_REQUESTS') ?: 1000);
        self::$LOGGER_RESPONSE_LENGTH = (int) (getenv('LOGGER_RESPONSE_LENGTH') ?: 1048576);
        // 1MB
        self::$LOGGER_STACK_LINES = (int) (getenv('LOGGER_STACK_LINES') ?: 20);
        self::$LOGGER_MESSAGE_SIZE = (int) (getenv('LOGGER_MESSAGE_SIZE') ?: 8192);
        // 8KB
        self::$LOGGER_CACHE_TTL = (int) (getenv('LOGGER_CACHE_TTL') ?: 60);
        self::$LOGGER_MAX_RETRIES = (int) (getenv('LOGGER_MAX_RETRIES') ?: 3);
        self::$LOGGER_BACKOFF_MIN = (int) (getenv('LOGGER_BACKOFF_MIN') ?: 100);
        self::$LOGGER_BACKOFF_MAX = (int) (getenv('LOGGER_BACKOFF_MAX') ?: 1000);
        self::$LOGGER_LOCK_TIMEOUT = (int) (getenv('LOGGER_LOCK_TIMEOUT') ?: 5);
    }

    private static function initializeCaptcha(): void
    {
        self::$CAPTCHA_ENABLED = filter_var(getenv('CAPTCHA_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        self::$CAPTCHA_TOKEN_SECRET = getenv('CAPTCHA_TOKEN_SECRET') ?: '';
        self::$CAPTCHA_TOKEN_TTL = (int) getenv('CAPTCHA_TOKEN_TTL') ?: 300;
        self::$ALTCHA_CAPTCHA_SITE_KEY = getenv('ALTCHA_CAPTCHA_SITE_KEY') ?: '';
        self::$ALTCHA_CAPTCHA_SITE_SECRET = getenv('ALTCHA_CAPTCHA_SITE_SECRET') ?: '';
        self::$ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE = getenv('ALTCHA_CAPTCHA_ENDPOINT_CHALLENGE')
            ?: 'https://captcha.muenchen.de/api/v1/captcha/challenge';
        self::$ALTCHA_CAPTCHA_ENDPOINT_VERIFY = getenv('ALTCHA_CAPTCHA_ENDPOINT_VERIFY')
            ?: 'https://captcha.muenchen.de/api/v1/captcha/verify';
    }

    private static function initializeCache(): void
    {
        self::$CACHE_DIR = getenv('CACHE_DIR') ?: __DIR__ . '/cache';
        self::$SOURCE_CACHE_TTL = (int) (getenv('SOURCE_CACHE_TTL') ?: 3600);
        self::validateCacheDirectory();
        self::setupCache();
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @TODO: Extract middleware initialization logic into a dedicated MiddlewareInitializer class
     */
    private static function initializeMiddleware(): void
    {
        // Rate limiting
        self::$RATE_LIMIT_MAX_REQUESTS = (int) (getenv('RATE_LIMIT_MAX_REQUESTS') ?: 60);
        self::$RATE_LIMIT_CACHE_TTL = (int) (getenv('RATE_LIMIT_CACHE_TTL') ?: 60);
        self::$RATE_LIMIT_MAX_RETRIES = (int) (getenv('RATE_LIMIT_MAX_RETRIES') ?: 3);
        self::$RATE_LIMIT_BACKOFF_MIN = (int) (getenv('RATE_LIMIT_BACKOFF_MIN') ?: 10);
        self::$RATE_LIMIT_BACKOFF_MAX = (int) (getenv('RATE_LIMIT_BACKOFF_MAX') ?: 50);
        self::$RATE_LIMIT_LOCK_TIMEOUT = (int) (getenv('RATE_LIMIT_LOCK_TIMEOUT') ?: 1);
        // Request limits
        self::$MAX_REQUEST_SIZE = (int) (getenv('MAX_REQUEST_SIZE') ?: 10485760);
        // 10MB
        self::$MAX_STRING_LENGTH = (int) (getenv('MAX_STRING_LENGTH') ?: 32768);
        // 32KB
        self::$MAX_RECURSION_DEPTH = (int) (getenv('MAX_RECURSION_DEPTH') ?: 10);
        // IP Filter
        self::$IP_BLACKLIST = getenv('IP_BLACKLIST') ?: '';

        self::$ACCESS_UNPUBLISHED_ON_DOMAIN = getenv('ACCESS_UNPUBLISHED_ON_DOMAIN') ?: '';
    }

    public static function reinitializeMiddlewareConfig(): void
    {
        self::initializeMiddleware();
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

    public static function getLoggerConfig(): array
    {
        return [
            'maxRequests' => self::$LOGGER_MAX_REQUESTS,
            'responseLength' => self::$LOGGER_RESPONSE_LENGTH,
            'stackLines' => self::$LOGGER_STACK_LINES,
            'messageSize' => self::$LOGGER_MESSAGE_SIZE,
            'cacheTtl' => self::$LOGGER_CACHE_TTL,
            'maxRetries' => self::$LOGGER_MAX_RETRIES,
            'backoffMin' => self::$LOGGER_BACKOFF_MIN,
            'backoffMax' => self::$LOGGER_BACKOFF_MAX,
            'lockTimeout' => self::$LOGGER_LOCK_TIMEOUT
        ];
    }

    public static function getRateLimit(): array
    {
        return [
            'maxRequests' => self::$RATE_LIMIT_MAX_REQUESTS,
            'cacheExpiry' => self::$RATE_LIMIT_CACHE_TTL,
            'maxRetries' => self::$RATE_LIMIT_MAX_RETRIES,
            'backoffMin' => self::$RATE_LIMIT_BACKOFF_MIN,
            'backoffMax' => self::$RATE_LIMIT_BACKOFF_MAX,
            'lockTimeout' => self::$RATE_LIMIT_LOCK_TIMEOUT
        ];
    }

    public static function getRequestLimits(): array
    {
        return [
            'maxSize' => self::$MAX_REQUEST_SIZE,
            'maxStringLength' => self::$MAX_STRING_LENGTH,
            'maxRecursionDepth' => self::$MAX_RECURSION_DEPTH
        ];
    }

    public static function getIpBlacklist(): string
    {
        return self::$IP_BLACKLIST ?: '';
    }

    public static function getAccessUnpublishedOnDomain(): ?string
    {
        return self::$ACCESS_UNPUBLISHED_ON_DOMAIN ?: null;
    }
}

Application::initialize();
