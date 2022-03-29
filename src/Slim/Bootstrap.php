<?php
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
        self::$instance = (self::$instance instanceof Bootstrap) ? self::$instance : new self();
        return self::$instance;
    }

    protected function configureLocale(
        $charset = \App::CHARSET,
        $timezone = \App::TIMEZONE
    ) {
        ini_set('default_charset', $charset);
        date_default_timezone_set($timezone);
        mb_internal_encoding($charset);
        \App::$now = (! \App::$now) ? new \DateTimeImmutable() : \App::$now;
    }

    protected static $debuglevels = array(
        'DEBUG'     => \Monolog\Logger::DEBUG,
        'INFO'      => \Monolog\Logger::INFO,
        'NOTICE'    => \Monolog\Logger::NOTICE,
        'WARNING'   => \Monolog\Logger::WARNING,
        'ERROR'     => \Monolog\Logger::ERROR,
        'CRITICAL'  => \Monolog\Logger::CRITICAL,
        'ALERT'     => \Monolog\Logger::ALERT,
        'EMERGENCY' => \Monolog\Logger::EMERGENCY,
    );

    protected function parseDebugLevel($level)
    {
        return isset(static::$debuglevels[$level]) ? static::$debuglevels[$level] : static::$debuglevels['DEBUG'];
    }

    protected function configureLogger(
        $level = \App::DEBUGLEVEL,
        $identifier = \App::IDENTIFIER
    ) {
        \App::$log = new \Monolog\Logger($identifier);
        $level = $this->parseDebugLevel($level);
        $handler = new \Monolog\Handler\ErrorLogHandler(\Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM, $level);
        $handler->setFormatter(new \Monolog\Formatter\JsonFormatter());
        \App::$log->pushHandler($handler);
    }

    protected function configureSlim()
    {
        // configure slim
        \App::$slim = new SlimApp(array(
            'debug' => \App::DEBUG,
            'cache' => function () {
                return new \Slim\HttpCache\CacheProvider();
            },
            'settings' => [
                'determineRouteBeforeAppMiddleware' => true,
                'displayErrorDetails' => true,
                'logger' => [
                    'name' => 'slim-app',
                    'level' => $this->parseDebugLevel(\App::DEBUGLEVEL),
                ],
            ],
        ));
        $container = \App::$slim->getContainer();
        // Configure caching
        \App::$slim->add(new \Slim\HttpCache\Cache('public', 300));
        \App::$slim->add(new Middleware\IpAddress(true, true));
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
        self::addTwigExtension(new \Twig\Extension\DebugExtension());

        \App::$slim->get('__noroute', function () {
            throw new Exception('Route missing');
        })->setName('noroute');

    }

    public static function getTwigView()
    {
        $template_path = (is_array(\App::TEMPLATE_PATH)) ? \App::TEMPLATE_PATH : \App::APP_PATH  . \App::TEMPLATE_PATH;
        $view = new \Slim\Views\Twig(
            $template_path,
            [
                'cache' => self::readCacheDir(),
                'debug' => \App::DEBUG,
            ]
        );
        return $view;
    }

    public static function readCacheDir()
    {
        $path = false;
        if (\App::TWIG_CACHE) {
            $path = \App::APP_PATH . \App::TWIG_CACHE;
            $userinfo = posix_getpwuid(posix_getuid());
            $user = $userinfo['name'];
            $githead = Git::readCurrentHash();
            $path .= ($githead) ? '/' . $user . $githead . '/' : '/' . $user . '/';
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
        require($filename);
    }
}
