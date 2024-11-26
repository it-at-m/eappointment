<?php

namespace BO\Zmscitizenapi;

use BO\Zmsclient\Http;

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

    /**
     * CAPTCHA-related settings, initialized dynamically
     */
    public static bool $CAPTCHA_ENABLED;
    public static string $CAPTCHA_SECRET;
    public static string $CAPTCHA_SITEKEY;
    public static string $CAPTCHA_ENDPOINT;
    public static string $CAPTCHA_ENDPOINT_PUZZLE;

    /**
     * Static initializer to set dynamic settings
     */
    public static function initialize()
    {
        self::$MAINTENANCE_MODE_ENABLED = filter_var(getenv('MAINTENANCE_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        self::$CAPTCHA_ENABLED = filter_var(getenv('CAPTCHA_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        self::$CAPTCHA_SECRET = getenv('CAPTCHA_SECRET') ?: "";
        self::$CAPTCHA_SITEKEY = getenv('CAPTCHA_SITEKEY') ?: "";
        self::$CAPTCHA_ENDPOINT = getenv('CAPTCHA_ENDPOINT') ?: "https://eu-api.friendlycaptcha.eu/api/v1/siteverify";
        self::$CAPTCHA_ENDPOINT_PUZZLE = getenv('CAPTCHA_ENDPOINT_PUZZLE') ?: "https://eu-api.friendlycaptcha.eu/api/v1/puzzle";
    }    
}

Application::initialize();