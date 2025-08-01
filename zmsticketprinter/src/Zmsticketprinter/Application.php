<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsticketprinter;

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_TICKETPRINTER_TWIG_CACHE')) {
    $value = getenv('ZMS_TICKETPRINTER_TWIG_CACHE');
    define('ZMS_TICKETPRINTER_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));
}

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsticketprinter';

    public const DEBUG = false;
    const TWIG_CACHE = ZMS_TICKETPRINTER_TWIG_CACHE;

    /**
     * language preferences
     */
    public static $locale = 'de';

    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name' => 'Deutsch',
            'locale' => 'de_DE.utf-8',
            'default' => true,
        ),
        'en' => array(
            'name' => 'English',
            'locale' => 'en_GB.utf-8',
            'default' => false,
        )
    );

    public static $now = '';

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
     */

    public static $http = null;

    public static $http_curl_config = array();

    public const JSON_COMPRESS_LEVEL = 1;

    /**
     * HTTP url for api
     */
    public const HTTP_BASE_URL = 'http://user:pass@host.tdl';
    public const SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    public const CLIENTKEY = '';
}
