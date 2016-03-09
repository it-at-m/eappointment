<?php
/**
 *
 * @package Slimproject
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Slim;

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
     * Settings for region
     */
    const DEFAULT_LANG = 'de';

    const CHARSET = 'UTF-8';

    const TIMEZONE = 'Europe/Berlin';

    public static $supportedLanguages = array();

    public static $lcTimes = array(
        'de' => 'de_DE',
        'en' => 'en_GB'
    );

    /*
     * -----------------------------------------------------------------------
     * Slim
     */

    /**
     * Slim singleton instance
     *
     * @var \Slim\Slim $slim
     */
    public static $slim;

    /**
     * Log level for Slim
     */
    const SLIM_LOGLEVEL = \Slim\Log::ERROR;

    /**
     * if debug is enabled, an exception is shown with a backtrace
     */
    const SLIM_DEBUG = false;

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
    const ESI_ENABLED = false;

    /*
     * -----------------------------------------------------------------------
     * Logging PSR3 compatible
     */
    public static $log = null;

    const MONOLOG_LOGLEVEL = \Monolog\Logger::WARNING;
}