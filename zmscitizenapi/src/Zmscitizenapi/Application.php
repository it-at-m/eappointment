<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi;

use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;


class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'Zmscitizenapi';

    /**
     * Name of the source which should be used for the API
     */
    public static string $source_name = "dldb";

    /**
     * -----------------------------------------------------------------------
     * ZMS API access
     * @var Http $http
     */
    public static $http = null;

    public static $http_curl_config = [];

    /**
     * Maintenance mode flag, initialized dynamically
     */
    public static bool $MAINTENANCE_MODE_ENABLED;

    public static ?CacheInterface $cache = null;

    /**
     * CAPTCHA-related settings, initialized dynamically
     */
    public static bool $CAPTCHA_ENABLED;
    public static string $FRIENDLY_CAPTCHA_SECRET_KEY;
    public static string $FRIENDLY_CAPTCHA_SITE_KEY;
    public static string $FRIENDLY_CAPTCHA_ENDPOINT;
    public static string $FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE;

    public static string $ALTCHA_CAPTCHA_SECRET_KEY;
    public static string $ALTCHA_CAPTCHA_SITE_KEY;
    public static string $ALTCHA_CAPTCHA_ENDPOINT;
    public static string $ALTCHA_CAPTCHA_ENDPOINT_PUZZLE;

    /**
     * Static initializer to set dynamic settings
     */
    public static function initialize()
    {
        self::$MAINTENANCE_MODE_ENABLED = filter_var(getenv('MAINTENANCE_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        self::$CAPTCHA_ENABLED = filter_var(getenv('CAPTCHA_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        
        self::$FRIENDLY_CAPTCHA_SECRET_KEY = getenv('FRIENDLY_CAPTCHA_SECRET_KEY') ?: "";
        self::$FRIENDLY_CAPTCHA_SITE_KEY = getenv('FRIENDLY_CAPTCHA_SITE_KEY') ?: "";
        self::$FRIENDLY_CAPTCHA_ENDPOINT = getenv('FRIENDLY_CAPTCHA_ENDPOINT') ?: "https://eu-api.friendlycaptcha.eu/api/v1/siteverify";
        self::$FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE = getenv('FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE') ?: "https://eu-api.friendlycaptcha.eu/api/v1/puzzle";

        self::$ALTCHA_CAPTCHA_SECRET_KEY = getenv('ALTCHA_CAPTCHA_SECRET_KEY') ?: "";
        self::$ALTCHA_CAPTCHA_SITE_KEY = getenv('ALTCHA_CAPTCHA_SITE_KEY') ?: "";
        self::$ALTCHA_CAPTCHA_ENDPOINT = getenv('ALTCHA_CAPTCHA_ENDPOINT') ?: "https://eu.altcha.org/form/";
        self::$ALTCHA_CAPTCHA_ENDPOINT_PUZZLE = getenv(name: 'ALTCHA_CAPTCHA_ENDPOINT_PUZZLE') ?: "https://eu.altcha.org/";

        self::initializeCache();
    }
    
    public static function initializeCache(): void
    {
        // Create a PSR-6 FilesystemAdapter. Data is stored in __DIR__ . '/cache'.
        // Adjust path and default lifetime as necessary.
        $psr6 = new FilesystemAdapter(
            namespace: '',            // optional sub-directory name
            defaultLifetime: 3600,    // default TTL in seconds
            directory: __DIR__ . '/cache'
        );

        // Wrap the PSR-6 adapter into a PSR-16 cache
        $psr16 = new Psr16Cache($psr6);

        // Assign it to our static $cache property
        self::$cache = $psr16;
    }
}

Application::initialize();