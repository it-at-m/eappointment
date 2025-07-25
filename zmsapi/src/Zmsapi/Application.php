<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

if (!getenv('ZMS_CONFIG_SECURE_TOKEN')) {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_API_TWIG_CACHE')) {
    $value = getenv('ZMS_API_TWIG_CACHE');
    define('ZMS_API_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));
}

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsapi';

    /**
     * @var Bool DEBUG
     */
    const DEBUG = false;
    const TWIG_CACHE = ZMS_API_TWIG_CACHE;

    /**
     * @var Bool DB_ENABLE_WSREPSYNCWAIT
     */
    const DB_ENABLE_WSREPSYNCWAIT = false;

    /**
     * @var Bool RIGHTSCHECK_ENABLED
     */
    const RIGHTSCHECK_ENABLED = true;

    /**
     * @var String DB_DSN_READONLY
     */
    const DB_DSN_READONLY = 'mysql:dbname=zmsbo;host=127.0.0.1';

    /**
     * @var String DB_DSN_READWRITE
     */
    const DB_DSN_READWRITE = 'mysql:dbname=zmsbo;host=127.0.0.1';

    /**
     * temporary db name for using dldb data
     * @var String DB_STARTINFO
     */
    const DB_STARTINFO = 'startinfo';

    /**
     * @var String DB_USERNAME
     */
    const DB_USERNAME = 'server';

    /**
     * @var String DB_PASSWORD
     */
    const DB_PASSWORD = 'internet';

    /**
     * @var String DB_IS_GALERA
     */
    const DB_IS_GALERA = true;

    /**
     * @var String Security Token for Api Access -> get config for example
     */
    const SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    /**
     * language preferences
     */

    public static $locale = 'de';

    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name'    => 'Deutsch',
            'locale'  => 'de_DE.utf-8',
            'default' => true,
        )
    );

    /**
     * dldb data path
     */
    public static $data = '/data';

    /**
     * @var \DateTimeInterface $now time to use for today (testing)
     */
    public static $now = null;

    public static function getNow()
    {
        if (self::$now instanceof \DateTimeInterface) {
            return self::$now;
        }
        return new \DateTimeImmutable();
    }
}
