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
        $bootstrap = self::getInstance();
        $bootstrap->configureSlim();
        $bootstrap->configureLocale();
        $bootstrap->configureLogger();
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
        $container = \App::$slim->getContainer();
        ini_set('default_charset', $charset);
        date_default_timezone_set($timezone);
        mb_internal_encoding($charset);
        \App::$language = new \BO\Slim\Language($container['request'], \App::$supportedLanguages);
        return \App::$language;
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
        \App::$slim->add(new \Slim\HttpCache\Cache('public', 86400));
        \App::$slim->add(new Middleware\IpAddress());
        \App::$slim->add(new Middleware\Validator());
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
                'cache' => self::readTwigCacheDir(),
                'debug' => \App::SLIM_DEBUG,
            ]
        );
        return $view;
    }

    public static function readTwigCacheDir()
    {
        $path = false;
        if (\App::TWIG_CACHE) {
            $path = \App::APP_PATH . \App::TWIG_CACHE;
            $githead = self::readGitHead();
            if ($githead) {
                $path .= '/' . $githead . '/';
            } else {
                $path .= '/static';
            }
            if (!is_dir($path)) {
                mkdir($path);
            }
        }
        return $path;
    }

    public static function readGitHead()
    {
        $githash = false;
        $githead = \App::APP_PATH . '/.git/HEAD';
        if (is_readable($githead)) {
            $gitbranch = trim(fgets(fopen($githead, 'r')));
            $gitbranch = preg_replace('#^.* ([^\s]+)$#', '$1', $gitbranch);
            $githashFile = \App::APP_PATH . '/.git/' . $gitbranch;
            if (is_readable($githashFile)) {
                $githash = trim(fgets(fopen($githashFile, 'r')));
            }
        }
        return $githash;
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
