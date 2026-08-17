<?php
// @codingStandardsIgnoreFile

/**
 * Example App singleton for zmsslim.
 * Consuming modules define their own App in config.php at runtime.
 * Included in psalm.xml so Psalm can resolve \App references in zmsslim.
 */
class App extends \BO\Slim\Application
{
    public const string IDENTIFIER = 'zmsslim';
    public const string APP_PATH = '.';
    public const bool DEBUG = false;
    public const string MODULE_NAME = 'zmsslim';

    // Provided by consuming modules; declared here for Psalm analysis of zmsslim.
    public const string DB_HOST = '';
    public const string DB_NAME = '';
    public const string DB_USER = '';
    public const string DB_PASSWORD = '';
    public const string DB_PORT = '';
    public const string CONFIG_SECURE_TOKEN = '';
    public const string httpBasicAuth = '';

    /** @var mixed */
    public static $http = null;

    /** @var string|null */
    public static $esiBaseUrl = null;

    public static function getBasePath(): string
    {
        return '';
    }
}
