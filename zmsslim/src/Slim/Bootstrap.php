<?php
namespace BO\Slim;

use App;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\HttpCache\CacheProvider;
use BO\Slim\Factory\ResponseFactory;
use BO\Slim\Factory\ServerRequestFactory;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

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
        $bootstrap->configureAppStatics();
        $bootstrap->configureLogger(App::DEBUGLEVEL, App::IDENTIFIER);
        $bootstrap->configureSlim();
        $bootstrap->configureLocale();
        Profiler::add("Init");
    }

    public static function getInstance()
    {
        self::$instance = (self::$instance instanceof Bootstrap) ? self::$instance : new self();
        return self::$instance;
    }

    protected function configureAppStatics()
    {
        if (getenv('ZMS_URL_SIGNATURE_KEY') !== false) {
            App::$urlSignatureSecret = getenv('ZMS_URL_SIGNATURE_KEY');
        }
    }

    protected function configureLocale(
        $charset = App::CHARSET,
        $timezone = App::TIMEZONE
    ) {
        ini_set('default_charset', $charset);
        date_default_timezone_set($timezone);
        mb_internal_encoding($charset);
        App::$now = (! App::$now) ? new \DateTimeImmutable() : App::$now;
    }

    protected static $debuglevels = array(
        'DEBUG'     => Logger::DEBUG,
        'INFO'      => Logger::INFO,
        'NOTICE'    => Logger::NOTICE,
        'WARNING'   => Logger::WARNING,
        'ERROR'     => Logger::ERROR,
        'CRITICAL'  => Logger::CRITICAL,
        'ALERT'     => Logger::ALERT,
        'EMERGENCY' => Logger::EMERGENCY,
    );

    protected function parseDebugLevel($level)
    {
        return isset(static::$debuglevels[$level]) ? static::$debuglevels[$level] : static::$debuglevels['DEBUG'];
    }

    protected function configureLogger(string $level, string $identifier): void
    {
        App::$log = new Logger($identifier);
        $level = $this->parseDebugLevel($level);
        $handler = new StreamHandler('php://stderr', $level);
        
        $formatter = new JsonFormatter();
        $formatter->setDateFormat('Y-m-d\TH:i:sP');
        
        // Add processor to format time_local first
        App::$log->pushProcessor(function ($record) {
            return array(
                'time_local' => $record['datetime']->format('Y-m-d\TH:i:sP'),
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'remote_addr' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '',
                'remote_user' => '',
                'application' => 'zmsslim',
                'message' => $record['message'],
                'level' => $record['level_name'],
                'context' => $record['context'],
                'extra' => $record['extra']
            );
        });
        
        $handler->setFormatter($formatter);
        App::$log->pushHandler($handler);
    }

    protected function configureSlim()
    {
        $container = $this->buildContainer();

        // instantiate slim
        App::$slim = new SlimApp(
            new ResponseFactory(),
            $container
        );
        App::$slim->determineBasePath();

        $container->set('router', App::$slim->getRouteCollector());

        // Configure caching
        App::$slim->add(new \Slim\HttpCache\Cache('public', 300));
        App::$slim->add(new Middleware\Validator());
        App::$slim->add('BO\Slim\Middleware\Route:getInfo');
        App::$slim->addRoutingMiddleware();
        App::$slim->add(new Middleware\Profiler());
        App::$slim->add(new Middleware\IpAddress(true, true));
        App::$slim->add(new Middleware\ZmsSlimRequest());
        App::$slim->add(new Middleware\TrailingSlash());

        $errorMiddleware = App::$slim->addErrorMiddleware(App::DEBUG, App::LOG_ERRORS, App::LOG_DETAILS, App::$log);
        $container->set('errorMiddleware', $errorMiddleware);

        self::addTwigExtension(new TwigExtensionsAndFilter(
            $container
        ));
        self::addTwigExtension(new DebugExtension());

        App::$slim->get('__noroute', function () {
            throw new \Exception('Route missing');
        })->setName('noroute');
    }

    public static function getTwigView(): Twig
    {
        $customTemplatesPath = 'custom_templates/';
        $templatePaths = (is_array(App::TEMPLATE_PATH)) ? App::TEMPLATE_PATH : [App::APP_PATH  . App::TEMPLATE_PATH];


        if (getenv("ZMS_CUSTOM_TEMPLATES_PATH")) {
            $customTemplatesPath = getenv("ZMS_CUSTOM_TEMPLATES_PATH");
        }

        if (is_dir($customTemplatesPath)) {
            array_unshift($templatePaths, $customTemplatesPath);
        }

        return new Twig(
            new FilesystemLoader($templatePaths),
            [
                'cache' => self::readCacheDir(),
                'debug' => App::DEBUG,
            ]
        );
    }

    public static function readCacheDir()
    {
        $path = false;
        if (App::TWIG_CACHE) {
            $path = App::APP_PATH . App::TWIG_CACHE;
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
        /** @var Twig $twig */
        $twig = App::$slim->getContainer()->get('view');
        $twig->addExtension($extension);
    }

    public static function addTwigFilter($filter)
    {
        $twig = App::$slim->getContainer()->get('view');
        $twig->getEnvironment()->addFilter($filter);
    }

    public static function addTwigTemplateDirectory($namespace, $path)
    {
        $twig = App::$slim->getContainer()->get('view');
        $loader = $twig->getLoader();
        $loader->addPath($path, $namespace);
    }

    public static function loadRouting($filename)
    {
        $container = App::$slim->getContainer();
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

    /**
     * @return Container
     */
    protected function buildContainer(): Container
    {
        $container = new Container();
        $container->set('debug', App::DEBUG);
        $container->set('cache', new CacheProvider());
        $container->set('settings', []);

        // configure slim views with twig
        $container->set('view', self::getTwigView());

        $container->set('request', ServerRequestFactory::createFromGlobals());

        return $container;
    }
}
