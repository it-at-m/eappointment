<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

define(
    'ZMS_ADMIN_TEMPLATE_FOLDER',
    getenv('ZMS_ADMIN_TEMPLATE_FOLDER') ? getenv('ZMS_ADMIN_TEMPLATE_FOLDER') : '/templates/'
);

define(
    'ZMS_ADMIN_SESSION_DURATION',
    getenv('ZMS_ADMIN_SESSION_DURATION') ? getenv('ZMS_ADMIN_SESSION_DURATION') : 28800
);

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     *
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsadmin';

    const DEBUG = false;

    const TWIG_CACHE = '/cache/';

    const TEMPLATE_PATH = ZMS_ADMIN_TEMPLATE_FOLDER;

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
    const MULTILANGUAGE = true;

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
    const CONFIG_SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    /**
     * signature key for url signature to save query paramter with hash
     */
    public static $urlSignatureSecret = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    /**
     * -----------------------------------------------------------------------
     * ZMS API access
     * @var Http $http
     */
    public static $http = null;

    public static $http_curl_config = array();

    const CLIENTKEY = '';

    const JSON_COMPRESS_LEVEL = 1;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = 'http://user:pass@host.tdl';

    /**
     * Cache configuration
     */
    public static ?CacheInterface $cache = null;
    public static string $PSR16_CACHE_DIR_ZMSADMIN;
    public static int $PSR16_CACHE_TTL_ZMSADMIN;

    public static function initialize(): void
    {
        self::initializeCache();
    }

    private static function initializeCache(): void
    {
        self::$PSR16_CACHE_DIR_ZMSADMIN = getenv('PSR16_CACHE_DIR_ZMSADMIN') ?: dirname(dirname(dirname(__DIR__))) . '/cache_psr16';
        self::$PSR16_CACHE_TTL_ZMSADMIN = (int) (getenv('PSR16_CACHE_TTL_ZMSADMIN') ?: 3600);
        self::validateCacheDirectory();
        self::setupCache();
    }

    private static function validateCacheDirectory(): void
    {
        if (!is_dir(self::$PSR16_CACHE_DIR_ZMSADMIN) && !mkdir(self::$PSR16_CACHE_DIR_ZMSADMIN, 0750, true)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', self::$PSR16_CACHE_DIR_ZMSADMIN));
        }

        if (!is_writable(self::$PSR16_CACHE_DIR_ZMSADMIN)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" is not writable', self::$PSR16_CACHE_DIR_ZMSADMIN));
        }
    }

    private static function setupCache(): void
    {
        $psr16 = new FilesystemAdapter(namespace: '', defaultLifetime: self::$PSR16_CACHE_TTL_ZMSADMIN, directory: self::$PSR16_CACHE_DIR_ZMSADMIN);
        self::$cache = new Psr16Cache($psr16);
    }
}
