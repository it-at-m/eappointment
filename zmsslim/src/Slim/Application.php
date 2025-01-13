<?php

namespace BO\Slim;

define(
    'ZMS_SESSION_DURATION',
    getenv('ZMS_SESSION_DURATION') ? getenv('ZMS_SESSION_DURATION') : 28800
);

class Application
{
    /**
     * Root directory for the project
     */
    const APP_PATH = '.';

    /**
     * Name of the application
     */
    const IDENTIFIER = 'unnamed slim project';

    /**
     * Flag to enable debugging mode for application,
     * if debug is enabled, an exception is shown with a backtrace
     */
    const DEBUG = false;
    const DEBUGLEVEL = 'WARNING';

    const SESSION_DURATION = ZMS_SESSION_DURATION;

    const LOG_ERRORS = true;
    const LOG_DETAILS = true;

    /**
     * Settings for region
     */
    const CHARSET = 'UTF-8';

    const TIMEZONE = 'Europe/Berlin';

    public static $includeUrl = null;

    /*
     * -----------------------------------------------------------------------
     * current time
     */

    public static $now;

    /*
     * -----------------------------------------------------------------------
     * Slim
     */

    /**
     * Slim singleton instance
     *
     * @var \BO\Slim\SlimApp $slim
     */
    public static $slim;


    /**
     * Define the path for the templates relative to APP_PATH
     */
    const TEMPLATE_PATH = '/templates/';

    /**
     * Define path for Twig template cache
     */
    const TWIG_CACHE = false;

    /**
     * Set this option, if ESI should be used
     */
    const ESI_ENABLED = true;

    /**
     * translator class
     */
    const TRANSLATOR_CLASS = '\\Symfony\\Component\\Translation\\Translator';

    /**
     * Default parameters for templates
     *
     */
    public static $templatedefaults = array();

    /**
     * Default parameters for middleware HttpBasicAuth
     *
     */
    public static $httpBasicAuth = array();


    /*
     * -----------------------------------------------------------------------
     * Logging PSR3 compatible
     */
    public static $log = null;

    /**
     * image preferences
     */

    public static $isImageAllowed = true;

    /**
     * @var \BO\Slim\Language $language
     *
     */
    const MULTILANGUAGE = true;

    public static $languagesource = 'json';

    public static $language = null;

    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name'    => 'Deutsch',
            'locale'  => 'de_DE.utf-8',
            'default' => true,
        ),
        'en' => array(
            'name'    => 'English',
            'locale'  => 'en_GB.utf-8',
            'default' => false,
        )
    );

    // default overwritten with Bootstrap::init()
    public static $urlSignatureSecret = 'e8dd240a854185c740384d90d771d85c';
}
