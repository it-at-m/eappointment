<?php
// @codingStandardsIgnoreFile

/**
 * Example App singleton for zmsslim.
 * Consuming modules define their own App in config.php at runtime.
 * Included in psalm.xml so Psalm can resolve \App references in zmsslim.
 */
class App extends \BO\Slim\Application
{
    public const IDENTIFIER = 'zmsslim';
    public const APP_PATH = '.';
    public const DEBUG = false;
    public const MODULE_NAME = 'zmsslim';

    // Provided by consuming modules; declared here for Psalm analysis of zmsslim.
    public const DB_HOST = '';
    public const DB_NAME = '';
    public const DB_USER = '';
    public const DB_PASSWORD = '';
    public const DB_PORT = '';
    public const CONFIG_SECURE_TOKEN = '';
    public const httpBasicAuth = '';

    /** @var mixed */
    public static $http = null;

    /** @var string|null */
    public static $esiBaseUrl = null;

    public static function getBasePath(): string
    {
        return '';
    }
}
