<?php

namespace BO\Slim;

define(
    'ZMS_SESSION_DURATION',
    (($value = getenv('ZMS_SESSION_DURATION')) !== false && $value !== '') ? $value : 36000
);
if (!defined('ZMS_SLIM_TWIG_CACHE')) {
    $value = getenv('ZMS_SLIM_TWIG_CACHE');
    define('ZMS_SLIM_TWIG_CACHE', ($value === 'false') ? false : ($value ?: false));
}
define('ZMS_DEBUGLEVEL', getenv('DEBUGLEVEL') ? getenv('DEBUGLEVEL') : 'INFO');

class Application
{
    /**
     * Root directory for the project
     */
    public const string APP_PATH = '.';

    /**
     * Name of the application
     */
    public const string IDENTIFIER = 'unnamed slim project';

    public const string MODULE_NAME = 'unnamed slim module';

    /**
     * Flag to enable debugging mode for application,
     * if debug is enabled, an exception is shown with a backtrace
     */
    public const bool DEBUG = false;
    const string DEBUGLEVEL = ZMS_DEBUGLEVEL;
    const SESSION_DURATION = ZMS_SESSION_DURATION;
    const string SECURE_TOKEN = '';
    const string CONFIG_SECURE_TOKEN = '';
    const bool RIGHTSCHECK_ENABLED = true;
    const int JSON_COMPRESS_LEVEL = 1;
    const string HTTP_BASE_URL = '';
    const string CLIENTKEY = '';
    const bool MAINTENANCE_MODE_ENABLED = false;
    const string ZMS_CITIZENLOGIN_EXTERNALUSERID_CLAIM_NAME = '';
    const bool LOG_ERRORS = true;
    const bool LOG_DETAILS = true;
/**
     * Settings for region
     */
    const string CHARSET = 'UTF-8';
    const string TIMEZONE = 'Europe/Berlin';
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
    const string TEMPLATE_PATH = '/templates/';
/**
     * Define path for Twig template cache
     */
    const TWIG_CACHE = false;
/**
     * Set this option, if ESI should be used
     */
    const bool ESI_ENABLED = true;
/**
     * translator class
     */
    const string TRANSLATOR_CLASS = '\\Symfony\\Component\\Translation\\Translator';
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
    const bool MULTILANGUAGE = true;
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
