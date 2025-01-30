<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class Application extends \BO\Slim\Application
{
    public const IDENTIFIER = 'Zmscitizenapi';
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
    public static string $FRIENDLY_CAPTCHA_SECRET_KEY;
    public static string $FRIENDLY_CAPTCHA_SITE_KEY;
    public static string $FRIENDLY_CAPTCHA_ENDPOINT;
    public static string $FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE;
    public static string $ALTCHA_CAPTCHA_SECRET_KEY;
    public static string $ALTCHA_CAPTCHA_SITE_KEY;
    public static string $ALTCHA_CAPTCHA_ENDPOINT;
    public static string $ALTCHA_CAPTCHA_ENDPOINT_PUZZLE;

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

    // CSRF config
    public static int $CSRF_TOKEN_LENGTH;
    public static string $CSRF_SESSION_KEY;

    // CORS config
    public static string $CORS_ALLOWED_ORIGINS;

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
        self::$MAINTENANCE_MODE_ENABLED = filter_var(
            getenv('MAINTENANCE_ENABLED'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private static function initializeLogger(): void
    {
        self::$LOGGER_MAX_REQUESTS = (int) (getenv('LOGGER_MAX_REQUESTS') ?: 1000);
        self::$LOGGER_RESPONSE_LENGTH = (int) (getenv('LOGGER_RESPONSE_LENGTH') ?: 1048576); // 1MB
        self::$LOGGER_STACK_LINES = (int) (getenv('LOGGER_STACK_LINES') ?: 20);
        self::$LOGGER_MESSAGE_SIZE = (int) (getenv('LOGGER_MESSAGE_SIZE') ?: 8192); // 8KB
        self::$LOGGER_CACHE_TTL = (int) (getenv('LOGGER_CACHE_TTL') ?: 60);
        self::$LOGGER_MAX_RETRIES = (int) (getenv('LOGGER_MAX_RETRIES') ?: 3);
        self::$LOGGER_BACKOFF_MIN = (int) (getenv('LOGGER_BACKOFF_MIN') ?: 100);
        self::$LOGGER_BACKOFF_MAX = (int) (getenv('LOGGER_BACKOFF_MAX') ?: 1000);
        self::$LOGGER_LOCK_TIMEOUT = (int) (getenv('LOGGER_LOCK_TIMEOUT') ?: 5);
    }

    private static function initializeCaptcha(): void
    {
        self::$CAPTCHA_ENABLED = filter_var(
            getenv('CAPTCHA_ENABLED'),
            FILTER_VALIDATE_BOOLEAN
        );

        self::$FRIENDLY_CAPTCHA_SECRET_KEY = getenv('FRIENDLY_CAPTCHA_SECRET_KEY') ?: '';
        self::$FRIENDLY_CAPTCHA_SITE_KEY = getenv('FRIENDLY_CAPTCHA_SITE_KEY') ?: '';
        self::$FRIENDLY_CAPTCHA_ENDPOINT = getenv('FRIENDLY_CAPTCHA_ENDPOINT')
            ?: 'https://eu-api.friendlycaptcha.eu/api/v1/siteverify';
        self::$FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE = getenv('FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE')
            ?: 'https://eu-api.friendlycaptcha.eu/api/v1/puzzle';

        self::$ALTCHA_CAPTCHA_SECRET_KEY = getenv('ALTCHA_CAPTCHA_SECRET_KEY') ?: '';
        self::$ALTCHA_CAPTCHA_SITE_KEY = getenv('ALTCHA_CAPTCHA_SITE_KEY') ?: '';
        self::$ALTCHA_CAPTCHA_ENDPOINT = getenv('ALTCHA_CAPTCHA_ENDPOINT')
            ?: 'https://eu.altcha.org/form/';
        self::$ALTCHA_CAPTCHA_ENDPOINT_PUZZLE = getenv('ALTCHA_CAPTCHA_ENDPOINT_PUZZLE')
            ?: 'https://eu.altcha.org/';
    }

    private static function initializeCache(): void
    {
        self::$CACHE_DIR = getenv('CACHE_DIR') ?: __DIR__ . '/cache';
        self::$SOURCE_CACHE_TTL = (int) (getenv('SOURCE_CACHE_TTL') ?: 3600);

        self::validateCacheDirectory();
        self::setupCache();
    }

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
        self::$MAX_REQUEST_SIZE = (int) (getenv('MAX_REQUEST_SIZE') ?: 10485760); // 10MB
        self::$MAX_STRING_LENGTH = (int) (getenv('MAX_STRING_LENGTH') ?: 32768); // 32KB
        self::$MAX_RECURSION_DEPTH = (int) (getenv('MAX_RECURSION_DEPTH') ?: 10);

        // CSRF
        self::$CSRF_TOKEN_LENGTH = (int) (getenv('CSRF_TOKEN_LENGTH') ?: 32);
        self::$CSRF_SESSION_KEY = getenv('CSRF_SESSION_KEY') ?: 'csrf_token';

        // CORS
        self::$CORS_ALLOWED_ORIGINS = getenv('CORS') ?: '';

        // IP Filter
        self::$IP_BLACKLIST = getenv('IP_BLACKLIST') ?: '';

        self::$ACCESS_UNPUBLISHED_ON_DOMAIN = getenv('ACCESS_UNPUBLISHED_ON_DOMAIN') ?: null;
    }

    public static function reinitializeMiddlewareConfig(): void
    {
        self::initializeMiddleware();
    }

    private static function validateCacheDirectory(): void
    {
        if (!is_dir(self::$CACHE_DIR) && !mkdir(self::$CACHE_DIR, 0750, true)) {
            throw new \RuntimeException(
                sprintf('Cache directory "%s" could not be created', self::$CACHE_DIR)
            );
        }

        if (!is_writable(self::$CACHE_DIR)) {
            throw new \RuntimeException(
                sprintf('Cache directory "%s" is not writable', self::$CACHE_DIR)
            );
        }
    }

    private static function setupCache(): void
    {
        $psr6 = new FilesystemAdapter(
            namespace: '',
            defaultLifetime: self::$SOURCE_CACHE_TTL,
            directory: self::$CACHE_DIR
        );

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

    public static function getCsrfConfig(): array
    {
        return [
            'tokenLength' => self::$CSRF_TOKEN_LENGTH,
            'sessionKey' => self::$CSRF_SESSION_KEY
        ];
    }

    public static function getCorsAllowedOrigins(): array
    {
        return array_filter(explode(',', self::$CORS_ALLOWED_ORIGINS));
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