<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

class Application
{
    /**
      * Root directory for the project
      */
    const APP_PATH = '.';

    /* -----------------------------------------------------------------------
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
    const TEMPLATE_PATH = '/templates/default/';

    /**
     * Define path for Twig template cache
     */
    const TWIG_CACHE = false;


    /* -----------------------------------------------------------------------
     * Logging PSR3 compatible
     */

    public static $log = null;

    const MONOLOG_LOGLEVEL = \Monolog\Logger::WARNING;
}
