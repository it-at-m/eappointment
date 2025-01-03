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
    
    public static bool $MAINTENANCE_MODE_ENABLED;
    public static string $CACHE_DIR;
    public static int $CACHE_LIFETIME;
    
    public static bool $CAPTCHA_ENABLED;
    
    public static string $FRIENDLY_CAPTCHA_SECRET_KEY;
    public static string $FRIENDLY_CAPTCHA_SITE_KEY;
    public static string $FRIENDLY_CAPTCHA_ENDPOINT;
    public static string $FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE;
    
    public static string $ALTCHA_CAPTCHA_SECRET_KEY;
    public static string $ALTCHA_CAPTCHA_SITE_KEY;
    public static string $ALTCHA_CAPTCHA_ENDPOINT;
    public static string $ALTCHA_CAPTCHA_ENDPOINT_PUZZLE;

    public static function initialize(): void
    {
        self::initializeMaintenanceMode();
        self::initializeCaptcha();
        self::initializeCache();
    }

    private static function initializeMaintenanceMode(): void 
    {
        self::$MAINTENANCE_MODE_ENABLED = filter_var(
            getenv('MAINTENANCE_ENABLED'), 
            FILTER_VALIDATE_BOOLEAN
        );
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
        self::$CACHE_LIFETIME = (int)(getenv('CACHE_LIFETIME') ?: 3600);

        self::validateCacheDirectory();
        self::setupCache();
    }

    private static function validateCacheDirectory(): void
    {
        if (!is_dir(self::$CACHE_DIR) && !mkdir(self::$CACHE_DIR, 0755, true)) {
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
            defaultLifetime: self::$CACHE_LIFETIME,
            directory: self::$CACHE_DIR
        );

        self::$cache = new Psr16Cache($psr6);
    }
}

Application::initialize();