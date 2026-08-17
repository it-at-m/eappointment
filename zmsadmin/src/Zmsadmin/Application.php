<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Helper\ModuleLoggerInitializer;
use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;

define(
    'ZMS_ADMIN_TEMPLATE_FOLDER',
    getenv('ZMS_ADMIN_TEMPLATE_FOLDER') ? getenv('ZMS_ADMIN_TEMPLATE_FOLDER') : '/templates/'
);

define(
    'ZMS_ADMIN_SESSION_DURATION',
    (($value = getenv('ZMS_ADMIN_SESSION_DURATION')) !== false && $value !== '') ? $value : 36000
);

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_ADMIN_TWIG_CACHE')) {
    $value = getenv('ZMS_ADMIN_TWIG_CACHE');
    define('ZMS_ADMIN_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));
}

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     *
     */
    const string IDENTIFIER = 'zms';

    const string MODULE_NAME = 'zmsadmin';

    public static ?CacheInterface $cache = null;

    const bool DEBUG = false;

    const TWIG_CACHE = ZMS_ADMIN_TWIG_CACHE;

    const string TEMPLATE_PATH = ZMS_ADMIN_TEMPLATE_FOLDER;

    const SESSION_DURATION = ZMS_ADMIN_SESSION_DURATION;

    public static $includeUrl = '/terminvereinbarung/admin';

    /**
     * allow cluster wide process calls
     */

    public static $allowClusterWideCall = true;

    /**
     * image preferences
     */

    public static $isImageAllowed = false;

    /**
     * language preferences
     */
    const bool MULTILANGUAGE = true;

    public static $locale = 'de';
    public static $supportedLanguages = array(
         // Default language
         'de' => array(
             'name'    => 'Deutsch',
             'locale'  => 'de_DE',
             'default' => true,
         ),
         // Other languages
         'en' => array(
             'name'    => 'English',
             'locale'  => 'en_GB',
         )
     );

    /**
    * config preferences
    */
    const string CONFIG_SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    /**
     * signature key for url signature to save query paramter with hash
     */
    public static $urlSignatureSecret = ZMS_CONFIG_SECURE_TOKEN;

    /**
     * -----------------------------------------------------------------------
     * ZMS API access
     * @var Http $http
     */
    public static $http = null;

    public static $http_curl_config = array();

    const string CLIENTKEY = '';

    const int JSON_COMPRESS_LEVEL = 1;

    /**
     * HTTP url for api
     */
    const string HTTP_BASE_URL = 'http://user:pass@host.tdl';

    public static function initialize(): void
    {
        ModuleLoggerInitializer::configure('ZMS_ADMIN');
        self::$cache = ModuleLoggerInitializer::tryInitializeCache();
    }
}

Application::initialize();
