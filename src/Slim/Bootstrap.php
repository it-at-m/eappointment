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
        self::configureSlim();
        self::configureLocale();
        self::configureLogger();
    }

    public static function configureLocale(
        $locale = \App::LOCALE,
        $charset = \App::CHARSET,
        $timezone = \App::TIMEZONE
    ) {
        ini_set('default_charset', $charset);
        date_default_timezone_set($timezone);
        mb_internal_encoding($charset);

        $language = self::getLanguage();
        setlocale(LC_ALL, \App::$lcTimes[$language]);

        // Specify the location of the translation tables
        bindtextdomain('dldb-'.$language, \App::APP_PATH. '/locale');
        bind_textdomain_codeset('dldb-'.$language, $charset);

        // Choose domain
        textdomain('dldb-'.$language);
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
        \App::$slim = new I18nSlim(array(
            //'debug' => \App::SLIM_DEBUG,
            //'settings' => [
            //    'displayErrorDetails' => true,
            //    'logger' => [
            //        'name' => 'slim-app',
            //        'level' => \App::MONOLOG_LOGLEVEL,
            //    ],
            //],
            //'view' => new TwigView(
            //    \App::APP_PATH  . \App::TEMPLATE_PATH,
            //    array (
            //        'debug' => \App::SLIM_DEBUG,
            //        'cache' => \App::TWIG_CACHE ? \App::APP_PATH . \App::TWIG_CACHE : false,
            //    )
            //),
        ));
        // configure slim views with twig
        self::addTwigExtension(new \Slim\Views\TwigExtension());
        self::addTwigExtension(new \BO\Slim\TwigExtension());
        self::addTwigExtension(new \Twig_Extension_Debug());

        //self::addTwigTemplateDirectory('default', \App::APP_PATH . \App::TEMPLATE_PATH);
        \App::$slim->get('', function () {
            throw new Exception('Route missing');
        })->name('noroute');
    }

    public static function getLanguage()
    {
        $lang = '';
        // TODO: interpreting uri on bootstrap does not work well with unit testing
        // and may have unexpected results with routing like /energie -> "en"
        // (difficult to debug, because this function here is well hidden)
        //$lang = substr(\App::$slim->request()->getResourceUri(), 1, 2);
        $lang = ($lang != '' && in_array($lang, array_keys(\App::$supportedLanguages))) ? $lang : \App::DEFAULT_LANG;
        \App::$slim->config('lang', $lang);
        return $lang;
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