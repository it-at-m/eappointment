<?php

/**
 * @package Zmsdb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdb;

define(
    'ZMSDB_SESSION_DURATION',
    getenv('ZMSDB_SESSION_DURATION') ? getenv('ZMSDB_SESSION_DURATION') : 10
);

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';

    const MODULE_NAME = 'zmsdb';

    const DEBUG = false;

    const SESSION_DURATION = ZMSDB_SESSION_DURATION;

    /**
     * Default parameters for templates
     */
    public static $templatedefaults = array();

    /**
     * Default parameters for middleware HttpBasicAuth
     */
    public static $httpBasicAuth = array();

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
}
