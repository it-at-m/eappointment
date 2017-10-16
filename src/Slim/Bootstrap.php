<?php
/**
 * @package Slimproject
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

/**
 * @SuppressWarnings(Coupling)
 * Bootstrapping connects the classes, so coupling should be ignored
 *
 */
class Bootstrap
{
    protected static $instance = null;

    public static function init()
    {
        Profiler::init();
        $bootstrap = self::getInstance();
        $bootstrap->configureSlim();
        $bootstrap->configureLocale();
        $bootstrap->configureLogger();
        Profiler::add("Init");
    }

    public static function getInstance()
    {
        if (self::$instance instanceof Bootstrap) {
            return self::$instance;
        }
        $bootstrap = new self();
        self::$instance = $bootstrap;
        return $bootstrap;
    }

    protected function configureLocale(
        $charset = \App::CHARSET,
        $timezone = \App::TIMEZONE
    ) {
        ini_set('default_charset', $charset);
        date_default_timezone_set($timezone);
        mb_internal_encoding($charset);
    }

    protected function configureLogger(
        $level = \App::MONOLOG_LOGLEVEL,
        $identifier = \App::IDENTIFIER
    ) {
        \App::$log = new \Monolog\Logger($identifier);
        \App::$log->pushHandler(new \Monolog\Handler\ErrorLogHandler(
            \Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM,
            $level
        ));
    }

    protected function configureSlim()
    {
        // configure slim
        \App::$slim = new SlimApp(array(
            'debug' => \App::SLIM_DEBUG,
            'cache' => function () {
                return new \Slim\HttpCache\CacheProvider();
            },
            'settings' => [
                'determineRouteBeforeAppMiddleware' => true,
                'displayErrorDetails' => true,
                'logger' => [
                    'name' => 'slim-app',
                    'level' => \App::MONOLOG_LOGLEVEL,
                ],
            ],
        ));
        $container = \App::$slim->getContainer();
        // Configure caching
        \App::$slim->add(new \Slim\HttpCache\Cache('public', 300));
        \App::$slim->add(new Middleware\IpAddress());
        \App::$slim->add(new Middleware\Validator());
        \App::$slim->add(new Middleware\Profiler());
        \App::$slim->add('BO\Slim\Middleware\Route:getInfo');
        // configure slim views with twig
        $container['view'] = function () {
            return self::getTwigView();
        };
        self::addTwigExtension(new \Slim\Views\TwigExtension(
            $container['router'],
            $container['request']->getUri()
        ));
        self::addTwigExtension(new \BO\Slim\TwigExtensionsAndFilter(
            $container
        ));
        self::addTwigExtension(new \Twig_Extension_Debug());

        //self::addTwigTemplateDirectory('default', \App::APP_PATH . \App::TEMPLATE_PATH);
        \App::$slim->get('__noroute', function () {
            throw new Exception('Route missing');
        })->setName('noroute');
    }

    public static function getTwigView()
    {
        $view = new \Slim\Views\Twig(
            \App::APP_PATH  . \App::TEMPLATE_PATH,
            [
                'cache' => self::readCacheDir(),
                'debug' => \App::SLIM_DEBUG,
            ]
        );
        return $view;
    }

    public static function readCacheDir()
    {
        $path = false;
        if (\App::TWIG_CACHE) {
            $path = \App::APP_PATH . \App::TWIG_CACHE;
            $githead = Git::readCurrentHash();
            if ($githead) {
                $path .= '/' . $githead . '/';
            } else {
                $path .= '/static/';
            }
            if (!is_dir($path)) {
                mkdir($path);
                chmod($path, 0777);
            }
        }
        return $path;
    }

    public static function addTwigExtension($extension)
    {
        $twig = \App::$slim->getContainer()->view;
        $twig->addExtension($extension);
    }

    public static function addTwigFilter($filter)
    {
        $twig = \App::$slim->getContainer()->view;
        $twig->getEnvironment()->addFilter($filter);
    }

    public static function addTwigTemplateDirectory($namespace, $path)
    {
        $twig = \App::$slim->getContainer()->view;
        $loader = $twig->getLoader();
        $loader->addPath($path, $namespace);
    }

    public static function loadRouting($filename)
    {
        $bootstrap = self::getInstance();
        $container = \App::$slim->getContainer();
        $cacheFile = static::readCacheDir();
        if ($cacheFile) {
            $cacheFile = $cacheFile . '/routing.cache';
            try {
                $container['router']->setCacheFile($cacheFile);
            } catch (\Exception $exception) {
                error_log("Could not write Router-Cache-File: $cacheFile");
                throw $exception;
            }
        }
        $bootstrap->addRoutingToSlim($filename);
    }

    /**
     * This is a workaround for PHP prior to version 7
     * Slim3 bind $this to a container in a callback, to enable this we fake a $this on routing
     */
    public function addRoutingToSlim($filename)
    {
        require($filename);
    }
}
