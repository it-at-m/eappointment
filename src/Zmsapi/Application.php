<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'Zmsapi';

    /**
     * @var String VERSION_MAJOR
     */
    const VERSION_MAJOR = '0';

    /**
     * @var String VERSION_MINOR
     */
    const VERSION_MINOR = '1';

    /**
     * @var String VERSION_PATCH
     */
    const VERSION_PATCH = '0';

    /**
     * @var Bool DEBUG
     */
    const DEBUG = false;

    /**
     * @var String DB_DSN_READONLY
     */
    const DB_DSN_READONLY = 'mysql:dbname=zmsbo;host=127.0.0.1';

    /**
     * @var String DB_DSN_READWRITE
     */
    const DB_DSN_READWRITE = 'mysql:dbname=zmsbo;host=127.0.0.1';

    /**
     * @var String DB_USERNAME
     */
    const DB_USERNAME = 'server';

    /**
     * @var String DB_PASSWORD
     */
    const DB_PASSWORD = 'internet';

    /**
     * language preferences
     */

    public static $locale = 'de';

    public static $supportedLanguages = array(
        'de' => 'Deutsch',
        'en' => 'English'
    );

    public static $lcTimes = array(
        'de' => 'de_DE.UTF-8',
        'en' => 'en_GB.UTF-8'
    );

    /**
     * dldb access to stored data without exposing type of data storage
     */
    public static $dldbdata = null;
    public static $data = '/data';
}
