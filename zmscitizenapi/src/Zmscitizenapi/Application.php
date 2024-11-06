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
    public static string $FRIENDLYCAPTCHA_SECRET;
    public static string $FRIENDLYCAPTCHA_SITEKEY;
    public static string $FRIENDLYCAPTCHA_ENDPOINT;
    public static string $FRIENDLYCAPTCHA_ENDPOINT_PUZZLE;

    /**
     * Static initializer to set dynamic settings
     */
    public static function initialize()
    {
        self::$MAINTENANCE_MODE_ENABLED = getenv('MAINTENANCE_ENABLED') === "1";
        self::$CAPTCHA_ENABLED = getenv('CAPTCHA_ENABLED') === "1";
        self::$FRIENDLYCAPTCHA_SECRET = getenv('FRIENDLYCAPTCHA_SECRET') ?: "";
        self::$FRIENDLYCAPTCHA_SITEKEY = getenv('FRIENDLYCAPTCHA_SITEKEY') ?: "";
        self::$FRIENDLYCAPTCHA_ENDPOINT = getenv('FRIENDLYCAPTCHA_ENDPOINT') ?: "https://api.friendlycaptcha.com/api/v1/siteverify";
        self::$FRIENDLYCAPTCHA_ENDPOINT_PUZZLE = getenv('FRIENDLYCAPTCHA_ENDPOINT_PUZZLE') ?: "https://api.friendlycaptcha.com/api/v1/puzzle";
    }
}

Application::initialize();