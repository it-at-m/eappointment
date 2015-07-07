<?php
/**
 * @package Slimproject
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

class Bootstrap
{
    public static function init()
    {
        self::configureLocale();
        self::configureLogger();
        self::configureSlim();
    }

    public static function configureLocale(
        $locale = \App::LOCALE,
        $charset = \App::CHARSET,
        $timezone = \App::TIMEZONE
    ) {
        ini_set('default_charset', $charset);
        setlocale(LC_ALL, $locale);
        date_default_timezone_set($timezone);
        mb_internal_encoding($charset);
    }

    public static function configureLogger(
        $level = \App::MONOLOG_LOGLEVEL,
        $identifier = \App::IDENTIFIER
    ) {
        \App::$log = new \Monolog\Logger($identifier);
        \App::$log->pushHandler(new \Monolog\Handler\ErrorLogHandler(
            \Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM,
            $level
        ));
    }

    public static function configureSlim()
    {
        // configure slim
        \App::$slim = new \Slim\Slim(array(
            'debug' => \App::SLIM_DEBUG,
            'log.enabled' => \App::SLIM_DEBUG,
            'log.level' => \App::SLIM_LOGLEVEL,
            'view' => new \Slim\Views\Twig(),
            'templates.path' => \App::APP_PATH  . \App::TEMPLATE_PATH
        ));

        // configure slim views with twig
        \App::$slim->view()->parserOptions = array (
            'debug' => \App::SLIM_DEBUG,
            'cache' => \App::TWIG_CACHE ? \App::APP_PATH . \App::TWIG_CACHE : false,
        );
        self::addTwigExtension(new \Slim\Views\TwigExtension());
        self::addTwigExtension(new \BO\Slim\TwigExtension());
        self::addTwigExtension(new \Twig_Extension_Debug());

        //self::addTwigTemplateDirectory('default', \App::APP_PATH . \App::TEMPLATE_PATH);
    }

    public static function addTwigExtension($extension)
    {
        $twig = \App::$slim->view->getInstance();
        $twig->addExtension($extension);
    }
    
    public static function addTwigFilter($filter)
    {
    	$twig = \App::$slim->view->getInstance();
    	$twig->addFilter($filter);
    }

    public static function addTwigTemplateDirectory($namespace, $path)
    {
        $twig = \App::$slim->view->getInstance();
        $loader = $twig->getLoader();
        $loader->addPath($path, $namespace);
    }
}
