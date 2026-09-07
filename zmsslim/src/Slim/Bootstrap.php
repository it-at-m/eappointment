<?php

namespace BO\Slim;

use App;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\HttpCache\CacheProvider;
use BO\Slim\Helper\PhpErrorHandler;
use BO\Slim\Factory\ResponseFactory;
use BO\Slim\Factory\ServerRequestFactory;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(Coupling)
 * Bootstrapping connects the classes, so coupling should be ignored
 *
 */

class Bootstrap
{
    protected static ?self $instance = null;

    public static function init(): void
    {
        Profiler::init();
        $bootstrap = self::getInstance();
        $bootstrap->configureAppStatics();
        $bootstrap->configureLogger(App::DEBUGLEVEL, App::IDENTIFIER);
        $bootstrap->configureSlim();
        $bootstrap->configureLocale();
        Profiler::add("Init");
    }

    /**
     * Logger + locale for CLI/cron without loading Slim (same JSON format as init()).
     */
    public static function initForCli(): void
    {
        $bootstrap = self::getInstance();
        $bootstrap->configureAppStatics();
        $level = defined('\\App::DEBUGLEVEL') ? \App::DEBUGLEVEL : self::getenvOrDefault('DEBUGLEVEL', 'INFO');
        $identifier = defined('\\App::IDENTIFIER') ? \App::IDENTIFIER : 'zms';
        $bootstrap->configureLogger($level, $identifier);
        $charset = defined('\\App::CHARSET') ? \App::CHARSET : 'UTF-8';
        $timezone = defined('\\App::TIMEZONE') ? \App::TIMEZONE : 'Europe/Berlin';
        $bootstrap->configureLocale($charset, $timezone);
    }

    /**
     * Guarantee App::$log for CLI/cron entrypoints (idempotent).
     * Replaces legacy config.php loggers (stdout + LineFormatter) with JSON on stdout (CLI) or stderr (web).
     */
    public static function ensureLogger(): void
    {
        if (!class_exists('\App', false)) {
            return;
        }
        if (\App::$log instanceof LoggerInterface && !(\App::$log instanceof Logger)) {
            return;
        }
        if (\App::$log instanceof Logger && self::loggerUsesJsonFormatter(\App::$log)) {
            return;
        }
        \App::$log = null;
        self::initForCli();
    }

    protected static function loggerUsesJsonFormatter(Logger $log): bool
    {
        foreach ($log->getHandlers() as $handler) {
            if (
                $handler instanceof FormattableHandlerInterface
                && $handler->getFormatter() instanceof JsonFormatter
            ) {
                return true;
            }
        }

        return false;
    }

    public static function getInstance(): self
    {
        self::$instance = (self::$instance instanceof Bootstrap) ? self::$instance : new self();
        return self::$instance;
    }

    protected function configureAppStatics(): void
    {
        if (getenv('ZMS_URL_SIGNATURE_KEY') !== false) {
            App::$urlSignatureSecret = getenv('ZMS_URL_SIGNATURE_KEY');
        }
    }

    /**
     * @return void
     */
    protected function configureLocale(
        string $charset = App::CHARSET,
        string $timezone = App::TIMEZONE
    ): void {
        ini_set('default_charset', $charset);
        date_default_timezone_set($timezone !== '' ? $timezone : 'Europe/Berlin');
        mb_internal_encoding($charset);
        App::$now = ($now = App::$now) instanceof \DateTimeInterface ? $now : new \DateTimeImmutable();
    }

    protected static array $debuglevels = array(
        'DEBUG'     => Logger::DEBUG,
        'INFO'      => Logger::INFO,
        'NOTICE'    => Logger::NOTICE,
        'WARNING'   => Logger::WARNING,
        'ERROR'     => Logger::ERROR,
        'CRITICAL'  => Logger::CRITICAL,
        'ALERT'     => Logger::ALERT,
        'EMERGENCY' => Logger::EMERGENCY,
    );

    protected function parseDebugLevel(string $level): int
    {
        return isset(static::$debuglevels[$level]) ? static::$debuglevels[$level] : static::$debuglevels['DEBUG'];
    }

    /**
     * PSR-3 / Monolog method name (lowercase) for App::$log->{$level}().
     */
    public static function normalizeLogLevelName(string $level): string
    {
        $upper = strtoupper($level);
        if ($upper === 'WARN') {
            $upper = 'WARNING';
        }
        if (!isset(static::$debuglevels[$upper])) {
            return 'info';
        }

        return strtolower($upper);
    }

    /**
     * True when ZMS_CRON_LOG is set by cronjob.* shell entrypoints (searchable JSON field "cron").
     */
    public static function isCronLogging(): bool
    {
        $value = getenv('ZMS_CRON_LOG');
        if ($value === false || $value === '') {
            return false;
        }

        return !in_array(strtolower($value), ['0', 'false', 'off', 'no'], true);
    }

    /**
     * Cron job id from ZMS_CRON_NAME (e.g. zmsapi_hourly).
     */
    public static function getCronLogName(): string
    {
        if (!static::isCronLogging()) {
            return '';
        }
        $name = getenv('ZMS_CRON_NAME');
        if ($name === false || $name === '') {
            return '';
        }

        return $name;
    }

    protected function configureLogger(string $level, string $identifier): void
    {
        App::$log = new Logger($identifier);
        $level = $this->parseDebugLevel($level);
        // Cron/CLI: stdout so Kubernetes/CAP collectors parse JSON; web: stderr
        $stream = PHP_SAPI === 'cli' ? 'php://stdout' : 'php://stderr';
        $handler = new StreamHandler($stream, $level);

        $formatter = new JsonFormatter();

        // Add processor to format time_local first
        App::$log->pushProcessor(function (array $record) {
            return array(
                'time_local' => (new \DateTime())->format('Y-m-d\TH:i:sP'),
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'remote_addr' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '',
                'remote_user' => '',
                'application' => defined('\\App::IDENTIFIER') ? App::IDENTIFIER : 'zms',
                'module' => defined('\\App::MODULE_NAME') ? App::MODULE_NAME : 'zmsslim',
                'cron' => static::isCronLogging(),
                'cron_name' => static::getCronLogName(),
                'message' => $record['message'],
                'level' => $record['level_name'],
                'context' => $record['context'],
                'extra' => $record['extra']
            );
        });

        $handler->setFormatter($formatter);
        App::$log->pushHandler($handler);

        App::$log = App::$log;

        PhpErrorHandler::register();
    }

    protected function configureSlim(): void
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
        $templatePaths = [App::APP_PATH . App::TEMPLATE_PATH];

        $envCustomTemplatesPath = getenv('ZMS_CUSTOM_TEMPLATES_PATH');
        if (
            is_string($envCustomTemplatesPath)
            && $envCustomTemplatesPath !== ''
            && $envCustomTemplatesPath !== '0'
        ) {
            $customTemplatesPath = $envCustomTemplatesPath;
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

    /**
     * @return false|string
     */
    public static function readCacheDir(): string|false
    {
        $path = false;
        $cacheDir = App::TWIG_CACHE;
        /** @psalm-suppress TypeDoesNotContainType Module App subclasses may set TWIG_CACHE to a path string. */
        if (is_string($cacheDir) && $cacheDir !== '') {
            $path = App::APP_PATH . $cacheDir;
            $userinfo = posix_getpwuid(posix_getuid());
            $user = (is_array($userinfo) && isset($userinfo['name'])) ? $userinfo['name'] : 'user';
            $githead = Git::readCurrentHash();
            $path .= (is_string($githead) && $githead !== '') ? '/' . $user . $githead . '/' : '/' . $user . '/';
            if (!is_dir($path)) {
                mkdir($path);
                chmod($path, 0777);
            }
        }
        return $path;
    }

    public static function addTwigExtension(\Twig\Extension\ExtensionInterface $extension): void
    {
        $container = App::$slim->getContainer();
        if (!$container instanceof Container) {
            throw new \RuntimeException('Slim container is not initialized');
        }
        /** @var Twig $twig */
        $twig = $container->get('view');
        $twig->addExtension($extension);
    }

    public static function addTwigFilter(\Twig\TwigFilter $filter): void
    {
        $container = App::$slim->getContainer();
        if (!$container instanceof Container) {
            throw new \RuntimeException('Slim container is not initialized');
        }
        $twig = $container->get('view');
        $twig->getEnvironment()->addFilter($filter);
    }

    public static function addTwigTemplateDirectory(string $namespace, string $path): void
    {
        $container = App::$slim->getContainer();
        if (!$container instanceof Container) {
            throw new \RuntimeException('Slim container is not initialized');
        }
        $twig = $container->get('view');
        $loader = $twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath($path, $namespace);
        }
    }

    /**
     * @return void
     */
    public static function loadRouting(string $filename): void
    {
        $container = App::$slim->getContainer();
        if (!$container instanceof Container) {
            throw new \RuntimeException('Slim container is not initialized');
        }
        $cacheFile = static::readCacheDir();
        if (is_string($cacheFile) && $cacheFile !== '' && $cacheFile !== '0') {
            $cacheFile = $cacheFile . '/routing.cache';
            try {
                $router = $container->get('router');
                if (is_object($router) && method_exists($router, 'setCacheFile')) {
                    $router->setCacheFile($cacheFile);
                }
            } catch (\Exception $exception) {
                App::$log->warning('Could not write router cache file', [
                    'cacheFile' => $cacheFile,
                    'exception' => $exception->getMessage(),
                ]);
                throw $exception;
            }
        }
        /** @psalm-suppress UnresolvableInclude Routing files are supplied by consuming apps. */
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

    private static function getenvOrDefault(string $name, string $default): string
    {
        $value = getenv($name);
        if ($value === false || $value === '' || $value === '0') {
            return $default;
        }

        return $value;
    }
}
